<?php

namespace App\Services;

use App\Models\User;

use App\Repositories\CategoryRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;

/**
 * ArticleService文章管理相关
 * @author edison.an
 *
 */
class ArticleService {
	
	/**
     * The category repository instance.
     *
     * @var CategoryRepository
     */
    protected $categorys;
    
    /**
     * The article repository instance.
     *
     * @var ArticleRepository
     */
    protected $articles;
    
    /**
     * The feedSubs repository instance.
     *
     * @var FeedSubRepository
     */
    protected $feedSubs;
    
    /**
     * The articleSubs repository instance.
     *
     * @var ArticleSubRepository
     */
    protected $articleSubs;
	
    /**
     * 
     * @param CategoryRepository $categorys
     * @param ArticleRepository $articles
     * @param FeedSubRepository $feedSubs
     * @param ArticleSubRepository $articleSubs
     */
	public function __construct(CategoryRepository $categorys, ArticleRepository $articles, FeedSubRepository $feedSubs, ArticleSubRepository $articleSubs) {
		$this->categorys = $categorys;
        $this->articles = $articles;
        $this->articleSubs = $articleSubs;
        $this->articleSubs = $articleSubs;
	}
	
	/**
	 * 获取当前状态下，每个订阅的数量 
	 * @param User $user
	 * @param unknown $status
	 * @return array like feed_id=>count
	 */
	public function getCountInfos(User $user, $status) {
		// get the status count info every feed.
		$temp_counts = ArticleSub::select ( 'feed_id', DB::raw ( 'count(*) as total' ) )->where ( 'user_id', $user->id )->where ( 'status', $status )->groupBy ( 'feed_id' )->get ();
		
		// count infos array: feed_id=>count
		$counts_info = array ();
		foreach ( $temp_counts as $temp_count ) {
			$counts_info [$temp_count ['feed_id']] = $temp_count ['total'];
		}
		
		return $counts_info;
	}
	
	/**
	 * 
	 * @param User $user
	 * @param string $feed_id
	 * @return number[][]|unknown[][]|NULL[][]
	 */
	public function getNavInfoAndNextRecommend(User $user, $feed_id = ''){
		// get the feed infos by user_id , with the category name and id.
		$category_feed_infos = DB::select ( 'select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name from feed_subs f,categories c where f.category_id = c.id and f.user_id = :user_id and f.status =1 order by c.category_order asc,f.feed_order asc', [
				':user_id' => $user->id
		] );
		
		// nav infos: category_id category_info => category_name category_id
		$nav_infos = array ();
		
		// recommend other feed infos: feed_id feed_name feed_count
		$next_recommend_feed = array ();
		
		foreach ( $category_feed_infos as $item ) {
			$nav_infos [$item->category_id] ['category_info'] = array (
					'category_name' => $item->category_name,
					'category_id' => $item->category_id
			);
				
			$feed = array (
					'feed_id' => $item->feed_id,
					'feed_name' => $item->feed_name,
					'feed_count' => isset ( $counts_info [$item->feed_id] ) ? $counts_info [$item->feed_id] : 0
			);
				
			if ($feed ['feed_count'] != 0 && empty ( $next_recommend_feed )) {
				if (!empty($feed_id)) {
					$feed_id != $feed ['feed_id'] ? $next_recommend_feed = $feed : '';
				} else {
					$next_recommend_feed = $feed;
				}
			}
				
			$nav_infos [$item->category_id] ['list'] [] = $feed;
		}
		
		foreach ( $nav_infos as $key => $val ) {
			$nav_infos [$key] ['list'] = $this->sortFeed ( $nav_infos [$key] ['list'] );
		}
		return array('nav_infos' => $nav_infos, 'next_recommend_feed' => $next_recommend_feed);
	}
	
	/**
	 * 
	 * @param string $feed_id
	 * @param string $category_id
	 * @return unknown
	 */
	public function getArticleSubs(User $user, $status, $page_count, $feed_id='', $category_id=''){
		// get article subs by feed_id
		if (!empty($feed_id)) {
			$articleSubs = $this->articleSubs->forUserByStatusFeedId ( $user, $status, $feed_id, $need_page = true, $page_count );
		} else if (!empty($category_id)) {
			// get article subs by category_id
			$articleSubs = $this->articleSubs->forUserByCategoryStatusFeedId ( $user, $status, $category_id, $need_page = true, $page_count );
		} else {
			// get article subs by common status
			$articleSubs = $this->articleSubs->forUserByStatus ( $user, $status, $need_page = true, $page_count );
		}
		return $articleSubs;
	}
	
}
