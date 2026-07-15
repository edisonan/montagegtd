<?php

namespace App\Services;

use App\Http\Utils\Aip\AipSpeech;
use App\Models\User;
use App\Models\Article;
use App\Models\ArticleMark;
use App\Models\ArticleSub;
use App\Models\FeedSub;
use App\Models\Feed;
use App\Repositories\CategoryRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;
use App\Repositories\UserDigestProfileRepository;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Requests\Request;

/**
 * 文章管理相关业务逻辑
 *
 * @author edison.an
 *        
 */
class ArticleService {
	
	/**
	 * CategoryRepository 实例.
	 *
	 * @var CategoryRepository
	 */
	protected $categoryRepository;
	
	/**
	 * ArticleRepository 实例.
	 *
	 * @var ArticleRepository
	 */
	protected $articleRepository;
	
	/**
	 * FeedSubRepository 实例 .
	 *
	 * @var FeedSubRepository
	 */
	protected $feedSubRepository;
	
	/**
	 * ArticleSubRepository 实例 .
	 *
	 * @var ArticleSubRepository
	 */
	protected $articleSubRepository;

    /**
     * UserDigestProfileRepository 实例 .
     *
     * @var UserDigestProfileRepository
     */
    protected $userDigestProfileRepository;
	
	/**
	 * 创建Service
	 *
	 * @param CategoryRepository $categoryRepository        	
	 * @param ArticleRepository $articleRepository        	
	 * @param FeedSubRepository $feedSubRepository        	
	 * @param ArticleSubRepository $articleSubRepository        	
	 */
	public function __construct(CategoryRepository $categoryRepository, ArticleRepository $articleRepository, FeedSubRepository $feedSubRepository, ArticleSubRepository $articleSubRepository, UserDigestProfileRepository $userDigestProfileRepository) {
		$this->categoryRepository = $categoryRepository;
		$this->articleRepository = $articleRepository;
		$this->feedSubRepository = $feedSubRepository;
		$this->articleSubRepository = $articleSubRepository;
        $this->userDigestProfileRepository = $userDigestProfileRepository;
	}
	
	/**
	 * 根据不同条件 获取相关文章信息
	 *
	 * @param string $status        	
	 * @param int $pageCount        	
	 * @param string $feedId        	
	 * @param string $categoryId        	
	 * @return unknown
	 */
	public function getArticleSubList(string $status, int $pageCount = 20, $feedId = '', $categoryId = '', array $filters = array()) {
		// 组装订阅源ids
		$feedIds = array ();
		if (! empty ( $feedId )) {
			$feedIds [] = $feedId;
		} else if (! empty ( $categoryId )) {
			$feedIds [] = $this->feedSubRepository->getFeedIdsByCategoryId ( $categoryId );
		}
		
		// 获取文章列表
		$articleSubs = $this->articleSubRepository->getArticleSubList ( Auth::id (), $status, $feedIds, $pageCount, $this->buildPersonalizedFilters(Auth::id(), $filters) );
		return $articleSubs;
	}
	
	/**
	 * 获取文章分类导航
	 *
	 * @param string $status        	
	 */
	public function getNavInfo(string $status) {
		$userId = \Auth::id ();
		
		// 获取所有的订阅信息及其分类信息.
		$categoryFeedInfos = $this->feedSubRepository->getCategoryFeedInfos ( $userId );
		
		// 每个订阅源该状态下数量
		$feedCountInfos = $this->getFeedCountInfos ( $userId, $status );
		
		// 导航信息，结构如下:
		// [category_id_value] [category_info] => {category_name/category_id}
		// [category_id_value] [list] => {feed_id1/feed_name1/feed_count1}, {feed_id2/feed_name2/feed_count2}...
		$navInfos = array ();
		foreach ( $categoryFeedInfos as $item ) {
			$navInfos [$item->category_id] ['category_info'] = array (
					'category_name' => $item->category_name,
					'category_id' => $item->category_id 
			);
			
			$navInfos [$item->category_id] ['list'] [] = array (
					'feed_id' => $item->feed_id,
					'feed_name' => $item->feed_name,
					'feed_count' => isset ( $feedCountInfos [$item->feed_id] ) ? $feedCountInfos [$item->feed_id] : 0 
			);
		}
		
		foreach ( $navInfos as $key => $val ) {
			$navInfos [$key] ['list'] = $this->sortFeed ( $val ['list'] );
		}
		return $navInfos;
	}
	
	/**
	 * 获取当前状态下，每个订阅的数量
	 *
	 * @param User $user        	
	 * @param int $status        	
	 * @return array like feed_id=>count
	 */
	public function getFeedCountInfos($userId, $status) {
		$originalCountInfos = $this->articleSubRepository->getFeedCountInfos ( $userId, $status );
		$countsInfo = array ();
		foreach ( $originalCountInfos as $originalCountInfo ) {
			$countsInfo [$originalCountInfo->feed_id] = $originalCountInfo->count;
		}
		return $countsInfo;
	}
	
	/**
	 * 获取文章列表
	 *
	 * @param string $feedId
	 *        	订阅源
	 * @param int $pageCount
	 *        	每页数量
	 * @return array
	 */
	public function getArticleListByFeedId($feedId, $pageCount, array $filters = array()) {
		// 查看订阅源
		$feed = Feed::where ( 'id', $feedId )->first ();
		if (empty ( $feed )) {
			throw new CustomException ( "该订阅不存在" );
		}
		
		// 获取文章列表
		$articles = $this->articleSubRepository->getArticleListByFeedId ( Auth::id (), $feedId, $pageCount, $this->buildPersonalizedFilters(Auth::id(), $filters) );
		
		return [ 
				'articles' => $articles,
				'feed' => $feed 
		];
	}

    public function buildPersonalizedFilters($userId, array $filters = array())
    {
        $viewMode = isset($filters['view_mode']) ? (string)$filters['view_mode'] : 'all';
        if ($viewMode === '') {
            $viewMode = 'all';
        }

        $payload = array(
            'time_range' => isset($filters['time_range']) ? (string)$filters['time_range'] : 'all',
            'read_duration' => isset($filters['read_duration']) ? (string)$filters['read_duration'] : 'all',
            'min_read_minutes' => isset($filters['min_read_minutes']) ? (int)$filters['min_read_minutes'] : 0,
            'max_read_minutes' => isset($filters['max_read_minutes']) ? (int)$filters['max_read_minutes'] : 0,
            'keyword' => isset($filters['keyword']) ? trim((string)$filters['keyword']) : '',
            'start_date' => isset($filters['start_date']) ? trim((string)$filters['start_date']) : '',
            'end_date' => isset($filters['end_date']) ? trim((string)$filters['end_date']) : '',
            'view_mode' => $viewMode,
            'primary_category' => isset($filters['primary_category']) ? trim((string)$filters['primary_category']) : '',
            'min_quality_score' => isset($filters['min_quality_score']) ? (int)$filters['min_quality_score'] : 0,
        );

        if ($viewMode !== 'personalized') {
            return $payload;
        }

        $profile = $this->userDigestProfileRepository->findEnabledByUserId($userId);
        if (!$profile) {
            $payload['preferred_categories'] = array('AI', '后端', '前端', '产品');
            $payload['topics'] = array();
            $payload['include_keywords'] = array();
            $payload['exclude_keywords'] = array();
            return $payload;
        }

        $payload['preferred_categories'] = (array)$profile->preferred_categories_json;
        $payload['topics'] = (array)$profile->topics_json;
        $payload['include_keywords'] = (array)$profile->include_keywords_json;
        $payload['exclude_keywords'] = (array)$profile->exclude_keywords_json;

        if (empty($payload['preferred_categories']) && empty($payload['topics']) && empty($payload['include_keywords'])) {
            $payload['preferred_categories'] = array('AI', '后端', '前端', '产品');
        }

        return $payload;
    }
	
	/**
	 * 判断是否订阅此源
	 *
	 * @param int $feedId
	 *        	订阅源id
	 * @return boolean
	 */
	public function isFeed($feedId) {
		$feedSub = $this->feedSubRepository->getUserFeedSubByFeedIdStatus ( Auth::id (), $feedId );
		return empty ( $feedSub ) ? false : true;
	}
	
	/**
	 * 获取语音地址,如果不存在则生成语音
	 *
	 * @param Article $article
	 *        	文章实体
	 * @return string
	 */
	public function getActiveRecordUrl($article) {
		$path = config ( "app.storage_path" ) . 'article_records/' . $article->id . '.mp3';
		if (file_exists ( $path )) {
			return $path;
		}
		
		// 识别正确返回语音二进制 错误则返回json 参照下面错误码
		$aipSpeech = new AipSpeech ( env ( 'BD_APP_ID', '' ), env ( 'BD_API_KEY', '' ), env ( 'BD_SECRET_KEY', '' ) );
		$result = $aipSpeech->synthesis ( strip_tags ( $article->content ), 'zh', 1, array (
				'per' => 3 
		) );
		if (! is_array ( $result )) {
			file_put_contents ( $path, $result );
			return $path;
		} else {
			Log::info ( 'create article record error::' . json_encode ( $result ) );
			return '';
		}
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $ids
	 *        	文章订阅关系ids
	 * @param string $status
	 *        	状态
	 * @return boolean
	 */
	public function setArticleSubStatusByIds($ids, $status = 'read') {
		return $this->articleSubRepository->setArticleSubStatusByIds ( Auth::id (), $ids, $status );
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $feedId
	 *        	订阅源id
	 * @param string $status
	 *        	状态
	 * @return boolean
	 */
	public function setArticleSubStatusByFeedId($feedId, $status = 'read') {
		return $this->articleSubRepository->setArticleSubStatusByFeedId ( Auth::id (), $feedId, $status );
	}
	
	/**
	 * 设置文章阅读状态
	 *
	 * @param array $articleSub
	 *        	文章订阅关系实体
	 * @param string $status
	 *        	状态
	 * @return boolean
	 */
	public function setArticleSubStatus($articleSub, $status = 'read') {
		$articleSub->status = $status;
		$articleSub->updated_at = date ( 'Y-m-d H:i:s' );
		return $articleSub->update ();
	}
	
	/**
	 * 文章标记
	 *
	 * @param int $articleId
	 *        	文章id
	 * @param string $content
	 *        	标注内容
	 */
	public function mark($articleId, $content) {
		$articleMark = new ArticleMark ();
		$articleMark->user_id = Auth::id ();
		$articleMark->article_id = $articleId;
		$articleMark->content = $content;
		$articleMark->save ();
		return $articleMark;
	}
	
	/**
	 * 为新订阅用户添加该订阅源系统内最新已有文章关联关系
	 *
	 * @param int $feedId
	 *        	订阅源id
	 * @return \App\Models\ArticleSub[]
	 */
	public function processNewFeedArticle($feedId, $userId) {
		// 获取某订阅源最近发布的文章列表
		$articles = $this->articleRepository->getRecentPublishArticleListByFeedId ( $feedId, 30 );
		
		// 生成订阅关系
		$articleSubs = array ();
		foreach ( $articles as $article ) {
			try {
				$articleSub = new ArticleSub ();
				$articleSub->feed_id = $feedId;
				$articleSub->user_id = $userId;
				$articleSub->article_id = $article->id;
				$articleSub->status = 'unread';
				$articleSub->save ();
				$articleSubs [] = $articleSub;
			} catch ( Exception $e ) {
				Log::info ( "process new feed article exception : " . $e->getMessage () );
			}
		}
		return $articleSubs;
	}
	
	/**
	 * 按照数量针对订阅进行排序
	 *
	 * @param array $feeds
	 *        	订阅源s
	 * @return array
	 */
	private function sortFeed($feeds) {
		foreach ( $feeds as $key => $feed ) {
			if ($feed ['feed_count'] == 0) {
				$feeds [] = $feed;
				unset ( $feeds [$key] );
			}
		}
		return $feeds;
	}
}
