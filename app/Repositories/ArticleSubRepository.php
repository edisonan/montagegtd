<?php

namespace App\Repositories;

use App\Models\ArticleSub;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 文章订阅关系Repository
 * 
 * @author edison.an
 *        
 */
class ArticleSubRepository {
	
	/**
	 * 根据状态用户id及订阅源等不同信息获取文章列表
	 *
	 * @param int $userId
	 *        	用户id
	 * @param int $status
	 *        	状态
	 * @param array $feedIds
	 *        	订阅源ids
	 * @param number $pageCount
	 *        	每页数量
	 * @return array
	 */
	public function getArticleSubList($userId, $status, $feedIds = array(), $pageCount = 20, array $filters = array()) {
		$articleSubs = ArticleSub::with ( 'article.feed', 'article.aiProfile' )
            ->join('articles', 'article_subs.article_id', '=', 'articles.id')
            ->select('article_subs.*')
            ->where ( 'article_subs.user_id', $userId );

        if ($status !== 'all') {
            $articleSubs->where('article_subs.status', $status);
        }
		if (! empty ( $feedIds )) {
			$articleSubs = $articleSubs->whereIn ( 'article_subs.feed_id', $feedIds );
		}
        $this->applyCommonFilters($articleSubs, $filters);
        $this->applyAiFilters($articleSubs, $filters);
		$articleSubs = $articleSubs->orderBy ( 'article_subs.updated_at', 'desc' )->simplePaginate ( $pageCount );
		return $articleSubs;
	}
	
	/**
	 * 获取某用户所有订阅源该状态下的数量
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param string $status
	 *        	状态
	 * @return unknown
	 */
	public function getFeedCountInfos($userId, $status) {
		$query = ArticleSub::select('feed_id', DB::raw('count(*) as count'))
			->where('user_id', $userId);
		if ($status !== 'all') {
			$query->where('status', $status);
		}
		return $query->groupBy('feed_id')->get();
	}
	
	/**
	 * 通过订阅源id获取某用户文章列表
	 *
	 * @param int $userId
	 *        	用户id
	 * @param int $feedId
	 *        	订阅源id
	 * @param int $pageCount
	 *        	每页数量
	 */
	public function getArticleListByFeedId($userId, $feedId, $pageCount = 20, array $filters = array()) {
        $articleSubs = ArticleSub::with ( 'article.feed', 'article.aiProfile' )
            ->join('articles', 'article_subs.article_id', '=', 'articles.id')
            ->select('article_subs.*')
            ->where ( 'article_subs.user_id', $userId )
            ->where ( 'article_subs.feed_id', $feedId );
        $this->applyCommonFilters($articleSubs, $filters);
        $this->applyAiFilters($articleSubs, $filters);
		return $articleSubs->orderBy ( 'article_subs.updated_at', 'desc' )->simplePaginate ( $pageCount );
    }

    protected function applyCommonFilters($query, array $filters)
    {
        $timeRange = isset($filters['time_range']) ? (string)$filters['time_range'] : 'all';
        $now = Carbon::now();
        $timeStart = null;
        if ($timeRange === '3h') {
            $timeStart = $now->copy()->subHours(3);
        } elseif ($timeRange === '6h') {
            $timeStart = $now->copy()->subHours(6);
        } elseif ($timeRange === '1d') {
            $timeStart = $now->copy()->subDay();
        } elseif ($timeRange === '3d') {
            $timeStart = $now->copy()->subDays(3);
        } elseif ($timeRange === '7d') {
            $timeStart = $now->copy()->subDays(7);
        }
        if ($timeStart) {
            $query->where('articles.published', '>=', $timeStart->toDateTimeString());
        }
        if ($timeRange === 'custom') {
            $startDate = trim((string)($filters['start_date'] ?? ''));
            $endDate = trim((string)($filters['end_date'] ?? ''));
            if ($startDate !== '') {
                $query->where('articles.published', '>=', $startDate . ' 00:00:00');
            }
            if ($endDate !== '') {
                $query->where('articles.published', '<=', $endDate . ' 23:59:59');
            }
        }

        $minReadMinutes = max(0, (int)($filters['min_read_minutes'] ?? 0));
        $maxReadMinutes = max(0, (int)($filters['max_read_minutes'] ?? 0));
        if ($minReadMinutes > 0) {
            $query->where('articles.estimated_read_minutes', '>=', $minReadMinutes);
        }
        if ($maxReadMinutes > 0) {
            $query->where('articles.estimated_read_minutes', '<=', $maxReadMinutes);
        }

        $keyword = trim((string)($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $query->leftJoin('feeds as filter_feeds', 'articles.feed_id', '=', 'filter_feeds.id')
                ->leftJoin('categories as filter_categories', 'filter_feeds.category_id', '=', 'filter_categories.id');
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('articles.subject', 'like', $like)
                    ->orWhere('articles.content', 'like', $like)
                    ->orWhere('filter_feeds.feed_name', 'like', $like)
                    ->orWhere('filter_categories.name', 'like', $like);
            });
        }
    }

    protected function applyAiFilters($query, array $filters)
    {
        $viewMode = isset($filters['view_mode']) ? (string)$filters['view_mode'] : 'all';
        $needsAiJoin = $viewMode !== 'all'
            || !empty($filters['primary_category'])
            || !empty($filters['min_quality_score']);

        if (!$needsAiJoin) {
            return;
        }

        $query->leftJoin('article_ai_profiles as aap', 'article_subs.article_id', '=', 'aap.article_id')
            ->leftJoin('articles as ai_articles', 'article_subs.article_id', '=', 'ai_articles.id')
            ->select('article_subs.*');

        if (!empty($filters['primary_category'])) {
            $query->where('aap.primary_category', (string)$filters['primary_category']);
        }

        if (!empty($filters['min_quality_score'])) {
            $query->where('aap.quality_score', '>=', (int)$filters['min_quality_score']);
        }

        if ($viewMode === 'tech') {
            $query->whereIn('aap.primary_category', array('AI', '后端', '前端'));
            return;
        }

        if ($viewMode === 'product') {
            $query->where('aap.primary_category', '产品');
            return;
        }

        if ($viewMode === 'read_later_suggest') {
            $query->where(function ($subQuery) {
                $subQuery->where('aap.quality_score', '>=', 70)
                    ->orWhereIn('aap.content_type', array('教程', '指南', '深度分析', '长文', '案例'));
            });
            return;
        }

        if ($viewMode === 'low_priority') {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('aap.id')
                    ->orWhere('aap.primary_category', '其他')
                    ->orWhere('aap.quality_score', '<', 50);
            });
            return;
        }

        if ($viewMode !== 'personalized') {
            return;
        }

        $scoreSqlParts = array('COALESCE(aap.quality_score, 0) * 0.3');
        $bindings = array();

        foreach ((array)($filters['preferred_categories'] ?? array()) as $category) {
            $category = trim((string)$category);
            if ($category === '') {
                continue;
            }
            $scoreSqlParts[] = 'CASE WHEN aap.primary_category = ? THEN 30 ELSE 0 END';
            $bindings[] = $category;
        }

        $keywords = array_merge(
            (array)($filters['topics'] ?? array()),
            (array)($filters['include_keywords'] ?? array())
        );
        foreach ($keywords as $keyword) {
            $keyword = trim((string)$keyword);
            if ($keyword === '') {
                continue;
            }
            $like = '%' . $keyword . '%';
            $scoreSqlParts[] = 'CASE WHEN ai_articles.subject LIKE ? OR aap.tags_json LIKE ? OR aap.keywords_json LIKE ? THEN 20 ELSE 0 END';
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        foreach ((array)($filters['exclude_keywords'] ?? array()) as $keyword) {
            $keyword = trim((string)$keyword);
            if ($keyword === '') {
                continue;
            }
            $like = '%' . $keyword . '%';
            $scoreSqlParts[] = 'CASE WHEN ai_articles.subject LIKE ? OR aap.tags_json LIKE ? OR aap.keywords_json LIKE ? THEN -100 ELSE 0 END';
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $query->selectRaw('(' . implode(' + ', $scoreSqlParts) . ') as personalized_score', $bindings)
            ->having('personalized_score', '>', 0)
            ->orderBy('personalized_score', 'desc');
    }

    public function findByUserIdAndArticleId($userId, $articleId)
    {
        return ArticleSub::with('article.feed')
            ->where('user_id', $userId)
            ->where('article_id', $articleId)
            ->orderBy('updated_at', 'desc')
            ->first();
    }
	
	/**
	 * 通过文章订阅ids调整文章阅读状态
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param array $ids
	 *        	文章订阅关系ids
	 * @param string $status
	 *        	状态
	 */
	public function setArticleSubStatusByIds($userId, $ids, $status) {
		return ArticleSub::whereIn ( 'id', $ids )->where ( 'user_id', $userId )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 通过订阅源id调整文章阅读状态
	 * 
	 * @param int $userId
	 *        	用户id
	 * @param int $feedId
	 *        	订阅源id
	 * @param string $status
	 *        	状态
	 */
	public function setArticleSubStatusByFeedId($userId, $feedId, $status) {
		return ArticleSub::where ( 'feed_id', $feedId )->where ( 'user_id', $userId )->where ( 'status', 'unread' )->update ( [ 
				'status' => $status,
				'updated_at' => date ( 'Y-m-d H:i:s' ) 
		] );
	}
	
	/**
	 * 获取时间段内统计情况
	 * 
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getStatisticCounts($startTime, $endTime) {
		return ArticleSub::select ( 'user_id', DB::raw ( 'count(*) as total' ) )->where ( 'status', 'read' )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->groupBy ( 'user_id' )->get ();
		;
	}
	
	/**
	 * 为总结获取记事列表
	 * 
	 * @param unknown $userId        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function getListForSummary($userId, $startTime, $endTime) {
		return ArticleSub::where ( 'user_id', $userId )->whereIn ( 'status', array (
				'star',
				'read_later' 
		) )->where ( 'updated_at', '>', $startTime )->where ( 'updated_at', '<=', $endTime )->orderBy ( 'id', 'desc' )->get ();
	}
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUser(User $user) {
	// return ArticleSub::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'asc' )->get ();
	// }
	
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByStatus(User $user, string $status, $needPage = false, $pageCount = 30) {
	// // $article = DB::table('article_subs')->join('articles', 'article_subs.article_id', '=', 'articles.id')
	// // ->join('feeds', 'articles.feed_id', '=', 'feeds.id')->where('article_subs.status',$status)->where('article_subs.user_id', $user->id)
	// // ->orderBy('article_subs.updated_at', 'desc')->limit($pageCount)->get();
	
	// // return $article;
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->orderBy ( 'id', 'desc' );
	
	// if ($needPage) {
	// return $article->simplePaginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// /**
	// * Get all of the tasks for a given user.
	// *
	// * @param User $user
	// * @return Collection
	// */
	// public function forUserByCategoryStatusFeedId(User $user, string $status, $category_id, $needPage = false, $pageCount = 30) {
	// $feedsubs = DB::table ( 'feed_subs' )->select ( 'feed_id' )->where ( 'category_id', $category_id )->where ( 'status', 1 )->get ();
	
	// $feedId_arr = array ();
	// foreach ( $feedsubs as $feedsub ) {
	// $feedId_arr [] = $feedsub->feed_id;
	// }
	
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->whereIn ( 'feed_id', $feedId_arr )->where ( 'status', $status );
	
	// // $article = ArticleSub::with('article.feed')->where('user_id', $user->id)
	// // ->whereIn('feed_id', function($query) use($category_id){
	// // \Log::info('sub query start:'.time());
	// // $query->select('feed_id')
	// // ->from('feed_subs')
	// // ->where('category_id', $category_id)
	// // ->where('status', 1);
	// // \Log::info('sub query end:'.time());
	// // })
	// // ->where('status',$status);
	// /*
	// * $article = \DB::table('article_subs')->with('articles')
	// * ->where(['article_subs.user_id'=>$user->id])
	// * ->where(['article_subs.status'=>$status])
	// * ->join('articles', 'articles.id', '=', 'article_subs.article_id')
	// * ->leftJoin("feed_subs",'feed_subs.feed_id','=','article_subs.feed_id')
	// * ->where(['feed_subs.category_id'=>$category_id])
	// * ->where(['feed_subs.status'=>1]);
	// */
	
	// if ($needPage) {
	// return $article->paginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByStatusFeedId(User $user, string $status, $feedId, $needPage = false, $pageCount = 30) {
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $article->paginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function forUserByFeedId(User $user, $feedId, $needPage = false, $pageCount = 30) {
	// $article = ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'feed_id', $feedId )->orderBy ( 'updated_at', 'desc' );
	// if ($needPage) {
	// return $article->simplePaginate ( $pageCount );
	// } else {
	// return $article->get ();
	// }
	// }
	// public function getRecentPublishList(User $user, string $status, $start_time, $end_time, $limit) {
	// return ArticleSub::with ( 'article.feed' )->where ( 'user_id', $user->id )->where ( 'status', $status )->where ( 'published', '<', $end_time )->where ( 'published', '>', $start_time )->orderBy ( 'feed_id' )->limit ( $limit )->get ();
	// }
}
