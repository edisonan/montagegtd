<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Feed;
use App\Models\FeedSub;
use App\Models\User;
use App\Models\ArticleSub;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleSubRepository;
use App\Repositories\FeedRepository;
use App\Repositories\FeedSubRepository;
use ArandiLopez\Feed\Factories\FeedFactory;
use Celd\Opml\Importer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\FeedFetch\FeedFetchFactory;

/**
 * FeedService订阅相关Service
 *
 * @author edison.an
 *        
 */
class FeedService {
	
	/**
	 * FeedSubRepository 实例 .
	 *
	 * @var FeedSubRepository
	 */
	protected $feedSubRepository;
	
	/**
	 * FeedRepository 实例 .
	 *
	 * @var FeedRepository
	 */
	protected $feedRepository;
	
	/**
	 * ArticleRepository 实例 .
	 *
	 * @var ArticleRepository
	 */
	protected $articleRepository;
	
	/**
	 * ArticleSubRepository 实例 .
	 *
	 * @var ArticleSubRepository
	 */
	protected $articleSubRepository;
	
	/**
	 * CategoryService 实例 .
	 *
	 * @var CategoryService
	 */
	protected $categoryService;
	
	/**
	 * ArticleService 实例 .
	 *
	 * @var ArticleService
	 */
	protected $articleService;
	
	/**
	 * 创建Service
	 *
	 * @param FeedSubRepository $feedSubs        	
	 * @param FeedRepository $feeds        	
	 */
	public function __construct(FeedSubRepository $feedSubRepository, FeedRepository $feedRepository, ArticleSubRepository $articleSubRepository, ArticleRepository $articleRepository, CategoryService $categoryService, ArticleService $articleService) {
		$this->feedSubRepository = $feedSubRepository;
		$this->feedRepository = $feedRepository;
		$this->articleRepository = $articleRepository;
		$this->articleSubRepository = $articleSubRepository;
		$this->categoryService = $categoryService;
		$this->articleService = $articleService;
	}
	
	/**
	 * 首页信息-已订阅源列表+分类列表
	 *
	 * @param string $url        	
	 * @return string[]|unknown[]|\App\Models\Category[][]
	 */
	public function getIndexInfo($url = '') {
		// 如果上送地址参数，将会自动读取地址信息
		$title = ! empty ( $url ) ? CommonUtil::pageTitle ( $url ) : '';
		
		// 获取已订阅信息
		$feedSubs = $this->feedSubRepository->getUserFeedSubList ( Auth::id () );
		
		// 获取订阅分类信息
		$categorys = $this->categoryService->getList ( true );
		
		return array (
				'feedSubs' => $feedSubs,
				'categorys' => $categorys,
				'url' => $url,
				'title' => $title 
		);
	}
	
	/**
	 * 发现页信息-获取推荐信息和订阅分类信息
	 *
	 * @return unknown[]|string[][]|\App\Models\Category[][]
	 */
	public function getExplorerInfo() {
		// 获取推荐的订阅
		$recommendFeeds = $this->feedRepository->getRecommendedList ();
		
		// 获取订阅分类信息
		$categorys = $this->categoryService->getList ( true );
		
		return array (
				'feeds' => $recommendFeeds,
				'categorys' => $categorys,
				'recommend_categorys' => Feed::$recommend_categorys 
		);
	}
	
	/**
	 * 根据推荐分类id或者名称来搜索订阅源
	 *
	 * @param int $recommendCategoryId        	
	 * @param string $name        	
	 * @return unknown
	 */
	public function getSearchFeeds($recommendCategoryId, $name) {
		if (! empty ( $recommendCategoryId )) {
			$feeds = $this->feedRepository->getListByRecommendCategoryId ( $recommendCategoryId );
		} else {
			$feeds = $this->feedRepository->getListByFeedName ( $name );
		}
		return $feeds;
	}
	
	/**
	 * 获取订阅源分类导航信息
	 */
	public function getNavInfo() {
		$userId = \Auth::id ();
		
		$categoryFeedInfos = $this->feedSubRepository->getUserFeedSubListWithCategory ( $userId );
		
		$navInfos = array ();
		foreach ( $categoryFeedInfos as $item ) {
			$navInfos [$item->category_id] ['category_info'] = array (
					'category_name' => $item->category_name,
					'category_id' => $item->category_id 
			);
			
			$feed = array (
					'feed_id' => $item->feed_id,
					'feed_name' => $item->feed_name,
					'feed_sub_id' => $item->feed_sub_id 
			);
			
			$navInfos [$item->category_id] ['list'] [] = $feed;
		}
		
		if (count ( $navInfos ) == 0) {
			$category = $this->categoryService->quickCreateCategory ( '未分类' );
			$navInfos [] = array (
					'category_info' => array (
							'category_name' => $category->name,
							'category_id' => $category->id 
					),
					'list' => array () 
			);
		}
		return $navInfos;
	}
	
	/**
	 * 存取新的订阅
	 *
	 * @param string $feedName        	
	 * @param string $url        	
	 * @param int $categoryId        	
	 * @return boolean
	 */
	public function store($feedName, $url, $categoryId) {
		$userId = \Auth::id ();
		
		// 检测分类合规性
		$category = $this->categoryService->getByCategoryId ( $categoryId );
		if (empty ( $category )) {
			throw new CustomException ( "该分类不存在" );
		}
		
		// 依据此订阅是否存在做不同处理
		$feed = $this->feedRepository->getFeedByUrl ( $url );
		if (empty ( $feed )) {
			$feed = new Feed ();
			$feed->user_id = $userId;
			$feed->feed_name = $feedName;
			$feed->url = $url;
			$feed->category_id = $categoryId;
			$feed->sub_count = 1;
			$feed->type = 1;
			$feed->save ();
		} else {
			$feedSub = $this->feedSubRepository->getUserFeedByFeedId ( $userId, $feed->id );
			
			if (! empty ( $feedSub )) {
				throw new CustomException ( "已经订阅过啦" );
			}
			
			// 如果未锁定，那么更改Feed的名称
			if (empty ( $feed->recommend_name ) && $feed->feed_name != $feedName) {
				$feed->feed_name = $feedName;
			}
			$feed->sub_count = $feed->sub_count + 1;
			$feed->save ();
		}
		
		// 进行订阅
		$this->storeFeedSub ( $feed, $feedName, $categoryId );
	}
	
	/**
	 * 根据已有订阅源快速订阅
	 *
	 * @param int $feedId        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function quickStore($feedId) {
		// 判断是否已经订阅
		$feedSub = $this->feedSubRepository->getUserFeedByFeedId ( Auth::id (), $feedId );
		if (! empty ( $feedSub )) {
			throw new CustomException ( "已经关注" );
		}
		
		// 获取订阅源信息
		$feed = $this->feedRepository->getFeedById ( $feedId );
		if (empty ( $feed )) {
			throw new CustomException ( "该订阅源不存在" );
		}
		
		// 更新订阅源
		$feed->sub_count = $feed->sub_count + 1;
		$feed->save ();
		
		// 默认订阅到未分类下
		$category = $this->categoryService->getByCategoryName ( '未分类', true );
		
		$this->storeFeedSub ( $feed, $feed->feed_name, $category->id );
	}
	
	/**
	 * 保存订阅信息，并更新订阅最新文章
	 *
	 * @param unknown $feedId        	
	 * @param unknown $feedName        	
	 * @param unknown $categoryId        	
	 */
	private function storeFeedSub($feed, $feedName, $categoryId) {
		$userId = Auth::id ();
		
		// 进行订阅
		$feedSub = new FeedSub ();
		$feedSub->user_id = $userId;
		$feedSub->status = 1;
		$feedSub->feed_id = $feed->id;
		$feedSub->feed_name = $feedName;
		$feedSub->category_id = $categoryId;
		$feedSub->save ();
		
		// 尝试为该订阅用户获取已有文章数据
		$articleSubs = $this->articleService->processNewFeedArticle ( $feed->id, $userId );
		if (count ( $articleSubs ) == 0) {
			try {
				$this->checkFeed ( $feed );
			} catch ( \Throwable $e ) {
				Log::error ( 'check feed exception:' . $feed->id . '|' . $feed->feed_name . '|' . $e->getMessage () );
			}
		}
	}
	
	/**
	 *
	 * @param User $user        	
	 * @param array $feedSubIdsArr        	
	 * @param int $changeFeedSubId        	
	 * @param int $changeFeedSubCategoryId        	
	 * @return boolean
	 */
	public function sort($feedSubIdsArr, $changeFeedSubId, $changeFeedSubCategoryId, $categoryFeedSubIds = array()) {
		$userId = (int)Auth::id();
		$feedSubIds = array_values(array_unique(array_filter(array_map('intval', (array)$feedSubIdsArr))));
		$changeFeedSubId = (int)$changeFeedSubId;
		$changeFeedSubCategoryId = (int)$changeFeedSubCategoryId;
		if ($userId <= 0 || empty($feedSubIds)) {
			throw new CustomException('订阅排序数据为空');
		}

		$categoryFeedSubIds = is_array($categoryFeedSubIds) ? $categoryFeedSubIds : array();

		return DB::transaction(function () use ($userId, $feedSubIds, $changeFeedSubId, $changeFeedSubCategoryId, $categoryFeedSubIds) {
			if (!empty($categoryFeedSubIds)) {
				foreach ($categoryFeedSubIds as $categoryId => $ids) {
					$categoryId = (int)$categoryId;
					$category = $this->categoryService->getByCategoryId($categoryId);
					if (empty($category)) {
						throw new CustomException('分类信息上送错误');
					}
					foreach (array_values(array_unique(array_filter(array_map('intval', (array)$ids)))) as $sort => $feedSubId) {
						$feedSub = $this->feedSubRepository->getUserFeedById($userId, $feedSubId);
						if (empty($feedSub)) {
							throw new CustomException('订阅信息上送错误');
						}
						$feedSub->category_id = $categoryId;
						$feedSub->feed_order = $sort;
						$feedSub->save();
					}
				}
				return true;
			}

			if ($changeFeedSubId > 0 && $changeFeedSubCategoryId > 0) {
				$category = $this->categoryService->getByCategoryId($changeFeedSubCategoryId);
				if (empty($category)) {
					throw new CustomException('分类信息上送错误');
				}

				$feedSub = $this->feedSubRepository->getUserFeedById($userId, $changeFeedSubId);
				if (empty($feedSub)) {
					throw new CustomException('订阅信息上送错误');
				}

				$feedSub->category_id = $changeFeedSubCategoryId;
				$feedSub->save();
			}

			$sort = 0;
			foreach ($feedSubIds as $feedSubId) {
				$feedSub = $this->feedSubRepository->getUserFeedById($userId, $feedSubId);
				if (empty($feedSub)) {
					throw new CustomException('订阅信息上送错误');
				}
				$feedSub->feed_order = $sort++;
				$feedSub->save();
			}

			return true;
		});
	}

	/**
	 * 手动刷新当前用户的全部启用订阅源，每个用户一分钟最多执行一次。
	 */
	public function refreshUserFeeds($userId)
	{
		$userId = (int)$userId;
		if ($userId <= 0) {
			throw new CustomException('用户未认证');
		}

		$lockKey = 'manual_feed_refresh_user_' . $userId;
		if (!Cache::add($lockKey, 1, 60)) {
			throw new CustomException('订阅刷新过于频繁，请 1 分钟后再试');
		}

		$feedSubs = FeedSub::where('user_id', $userId)
			->where('status', 1)
			->with('feed')
			->get();
		$feedIds = array();
		$successCount = 0;
		$failedCount = 0;
		foreach ($feedSubs as $feedSub) {
			$feed = $feedSub->feed;
			if (!$feed || in_array((int)$feed->id, $feedIds, true)) {
				continue;
			}
			$feedIds[] = (int)$feed->id;
			try {
				$this->checkFeed($feed);
				$successCount++;
			} catch (\Throwable $e) {
				$failedCount++;
				Log::warning('manual feed refresh failed', array('feed_id' => $feed->id, 'user_id' => $userId));
			}
		}

		return array(
			'feed_count' => count($feedIds),
			'success_count' => $successCount,
			'failed_count' => $failedCount,
		);
	}

	/**
	 * 手动刷新单个订阅源，每个用户针对同一个订阅源一分钟最多执行一次。
	 */
	public function refreshFeedForUser($userId, $feedSubId)
	{
		$userId = (int)$userId;
		$feedSubId = (int)$feedSubId;
		if ($userId <= 0) {
			throw new CustomException('用户未认证');
		}

		$feedSub = $this->feedSubRepository->getUserFeedById($userId, $feedSubId);
		if (!$feedSub || (int)$feedSub->status !== 1 || !$feedSub->feed) {
			throw new CustomException('订阅源不存在或已停用');
		}

		$lockKey = 'manual_feed_refresh_user_' . $userId . '_feed_' . $feedSubId;
		if (!Cache::add($lockKey, 1, 60)) {
			throw new CustomException('该订阅刷新过于频繁，请 1 分钟后再试');
		}

		$beforeCount = (int)ArticleSub::where('user_id', $userId)->where('feed_id', $feedSub->feed_id)->count();
		$this->checkFeed($feedSub->feed);
		$afterCount = (int)ArticleSub::where('user_id', $userId)->where('feed_id', $feedSub->feed_id)->count();

		return array(
			'feed_sub_id' => $feedSubId,
			'feed_id' => (int)$feedSub->feed_id,
			'new_article_count' => max(0, $afterCount - $beforeCount),
		);
	}
	
	/**
	 * 核验订阅地址是否正常
	 *
	 * @param string $feedUrl        	
	 * @throws CustomException
	 */
	public function validateFeedUrl($feedUrl) {
		$feedFactory = new FeedFactory ( [ 
				'cache.enabled' => false 
		] );
		
		$feeder = $feedFactory->make ( $feedUrl );
		$simplePieInstance = $feeder->getRawFeederObject ();
		
		if (empty ( $simplePieInstance )) {
			throw new CustomException ( "检测订阅源为空" );
		}
	}
	
	/**
	 *
	 * @param User $user        	
	 * @param string $path        	
	 */
	public function importOpml($path) {
		$importer = new Importer ( file_get_contents ( $path ) );
		$feedList = $importer->getFeedList ();
		
		$categorys = $this->categoryService->getList ( true );
		
		$category_arr = array ();
		foreach ( $categorys as $category ) {
			$category_arr [$category->name] = $category->id;
		}
		
		$categoryId = $category_arr ['未分类'];
		foreach ( $feedList->getItems () as $item ) {
			if ($item->getType () == 'category') {
				
				if (! isset ( $category_arr [$item->getTitle ()] )) {
					$category = $this->categoryService->quickCreateCategory ( $item->getTitle () );
					$category_arr [$item->getTitle ()] = $category->id;
				}
				
				$categoryId = $category_arr [$item->getTitle ()];
				
				foreach ( $item->getFeeds () as $feed ) {
					$this->store ( $feed->getTitle (), $feed->getXmlUrl (), $categoryId );
				}
			} else {
				$this->store ( $item->getTitle (), $item->getXmlUrl (), $categoryId );
			}
		}
	}
	
	/**
	 *
	 * @param Feed $feed        	
	 */
	public function checkFeed(Feed $feed) {
		$feedId = $feed->id;
		$feedUrl = $feed->url;
		Log::info ( "Check Feed:" . $feedId . '|' . $feedUrl );
		
		$feedFetchFactory = new FeedFetchFactory ();
		$feedFetch = $feedFetchFactory->getFeedFetch ( $feed );
		$infos = $feedFetch->getInfos ();
		if (empty ( $infos ) || count ( $infos ['list'] ) == 0) {
			return;
		}
		
		$feedLastPublished = '';
		$feedSubs = $this->feedSubRepository->getFeedSubAllListByFeedIdAndStatus ( $feedId, 1 );
		
		foreach ( $infos ['list'] as $info ) {
			if ($this->articleRepository->existArticleByFeedIdAndUrl ( $feedId, $info ['url'] )) {
				continue;
			}
			
			if (! empty ( $info ['subject'] ) && $this->articleRepository->existArticleByFeedIdAndSubject ( $feedId, $info ['subject'] )) {
				continue;
			}
			
			$article = new Article ();
			$article->feed_id = $feedId;
			$article->status = 'unread';
			$article->url = $info ['url'];
			$article->subject = $info ['subject'];
			$article->content = $info ['content'];
			$article->image_url = $info ['image_url'];
			$article->published = $info ['published'];
			
			$article->user_id = $feed->user_id;
			$article->save ();
			
			Log::info ( "Save Article:" . $article->url );
			
			foreach ( $feedSubs as $feedSub ) {
				$articleSub = ArticleSub::where ( 'user_id', $feedSub->user_id )->where ( 'article_id', $article->id )->first ();
				if (empty ( $articleSub )) {
					$articleSub = new ArticleSub ();
					$articleSub->feed_id = $feedSub->feed_id;
					$articleSub->user_id = $feedSub->user_id;
					$articleSub->article_id = $article->id;
					$articleSub->status = 'unread';
					$articleSub->published = $article->published;
					$articleSub->save ();
					
					Log::info ( "Save ArticleSub:" . $articleSub->user_id . '|' . $articleSub->article_id );
				}
			}
			
			if (empty ( $feedLastPublished ) || strtotime ( $feedLastPublished ) < strtotime ( $article->published )) {
				$feedLastPublished = $article->published;
			}
		}
		
		// update feed updated_at record
		$updateParams = [ 
				'updated_at' => date ( 'Y-m-j H:i:s' ),
				'feed_desc' => $infos ['basic'] ['feed_desc'],
				'favicon' => $infos ['basic'] ['favicon'] 
		];
		
		if (! empty ( $feedLastPublished )) {
			$updateParams ['last_published'] = $feedLastPublished;
		}
		
		Feed::where ( 'id', $feedId )->update ( $updateParams );
	}
	
	/**
	 * 根据活跃等级获取最新文章
	 * 
	 * @param int $activeLevel        	
	 */
	public function checkFeedByActiveLevel($activeLevel) {
		$feeds = $this->feedRepository->getAllListByActiveLevel ( $activeLevel );
		foreach ( $feeds as $feed ) {
			try {
				$this->checkFeed ( $feed );
			} catch ( Exception $e ) {
				Log::error ( 'check feed exception:' . $feed->id . '|' . $feed->feed_name . '|' . $e->getMessage () );
			}
		}
	}
	
	// public function checkFanfouFeed(Feed $feed) {
	// // set previous week
	// $previousweek = date ( 'Y-m-j H:i:s', strtotime ( '-7 days' ) );
	
	// Log::info ( "Check Feed:" . $feed->id . '|' . $feed->url );
	
	// $feedSubs = FeedSub::where ( 'feed_id', $feed->id )->where ( 'status', 1 )->get ();
	
	// $config = config ( 'services.fanfou' );
	
	// $user = new User ();
	// $user->id = $feed->user_id;
	// $thirdRepository = new ThirdRepository ();
	// $third = $thirdRepository->forUserSource ( $user );
	// if (empty ( $third )) {
	// return;
	// }
	
	// $oauth_token = $third ['token_value'];
	// $oauth_token_secret = $third ['token_secret'];
	
	// $ff_user = new FFClient ( config ( "services.fanfou.client_id" ), config ( "services.fanfou.client_secret" ), $oauth_token, $oauth_token_secret );
	
	// $items = $ff_user->friends_timeline ( 1, 50 );
	// $items = json_decode ( $items, true );
	
	// if (empty ( $items ) || isset ( $items ['error'] )) {
	// return false;
	// }
	
	// foreach ( $items as $item ) {
	// if (isset ( $item ['repost_status'] )) {
	// $item = $item ['repost_status'];
	// }
	
	// $resultsUrl = Article::where ( [
	// 'feed_id' => $feed->id,
	// 'url' => 'http://fanfou.com/statuses/' . $item ['id']
	// ] )->count ();
	// $date = date ( 'Y-m-d H:i:s', strtotime ( $item ['created_at'] ) );
	
	// $feedLastPublished = '';
	// if ($resultsUrl == 0 && ! (strtotime ( $date ) < strtotime ( $previousweek ))) {
	// $article = new Article ();
	
	// $content = $item ['text'] . "&nbsp;&nbsp; ";
	
	// if (isset ( $item ['user'] )) {
	// $content = "<a href='http://fanfou.com/{$item['user']['unique_id']}'>@{$item['user']['name']}</a> &nbsp; $content";
	// }
	
	// if (isset ( $item ['photo'] )) {
	// $content = "$content<br><img width='' src='{$item['photo']['largeurl']}'/><a href='{$item['photo']['largeurl']}' target='_blank'>大图</a>";
	// }
	
	// // get article content
	// $article->feed_id = $feed->id;
	// $article->status = 'unread';
	// $article->url = 'http://fanfou.com/statuses/' . $item ['id'];
	// $article->subject = '';
	// $article->content = $content;
	// $article->published = date ( 'Y-m-d H:i:s', strtotime ( $item ['created_at'] ) );
	
	// $article->user_id = $feed->user_id;
	
	// $description = $item ['text'];
	// $article->save ();
	
	// Log::info ( "Save Article:" . $article->url );
	
	// foreach ( $feedSubs as $feedSub ) {
	// $articleSub = ArticleSub::where ( 'user_id', $feedSub->user_id )->where ( 'article_id', $article->id )->first ();
	// if (empty ( $articleSub )) {
	// $articleSub = new ArticleSub ();
	// $articleSub->feed_id = $feedSub->feed_id;
	// $articleSub->user_id = $feedSub->user_id;
	// $articleSub->article_id = $article->id;
	// $articleSub->status = 'unread';
	// $articleSub->published = $article->published;
	// $articleSub->save ();
	
	// Log::info ( "Save ArticleSub:" . $articleSub->user_id . '|' . $articleSub->article_id );
	// }
	// }
	
	// if (empty ( $feedLastPublished ) || strtotime ( $feedLastPublished ) < strtotime ( $article->published )) {
	// $feedLastPublished = $article->published;
	// }
	// }
	// }
	
	// // update feed updated_at record
	// if (count ( $items ) > 0) {
	// if (! empty ( $feedLastPublished )) {
	// Feed::where ( 'id', $feed->id )->update ( [
	// 'updated_at' => date ( 'Y-m-j H:i:s' ),
	// 'last_published' => $feedLastPublished
	// ] );
	// } else {
	// Feed::where ( 'id', $feed->id )->update ( [
	// 'updated_at' => date ( 'Y-m-j H:i:s' )
	// ] );
	// }
	// }
	// }
}
