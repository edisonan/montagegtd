<?php

namespace App\Repositories;

use App\User;
use App\Feed;
use App\Article;
use App\FeedSub;
use App\ArticleSub;
use ArandiLopez\Feed\Factories\FeedFactory; //use SimplePie to parse RSS feeds, see: https://github.com/arandilopez/laravel-feed-parser
use App\Http\Utils\OAuth1\FFClient;


class FeedRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUser(User $user,$need_page=false)
    {
        $feed = Feed::where('user_id', $user->id)
                ->orderBy('created_at', 'asc');
        
        if($need_page){
        	return $feed->paginate(50);
        } else {
        	return $feed->get();
        }
    }
    
        public function forIsRecommend($is_recommend,$need_page=false)
    {
    	$feed = Feed::where('is_recommend', $is_recommend)
    	->orderBy('recommend_order', 'desc');
    
    	if($need_page){
    		return $feed->paginate(9);
    	} else {
    		return $feed->get();
    	}
    }
    
    public function findByRecommendCategoryId($recommend_category_id,$need_page=false)
    {
    	$feed = Feed::where('recommend_category_id', $recommend_category_id)
    	->orderBy('recommend_order', 'desc');
    
    	if($need_page){
    		return $feed->paginate(48);
    	} else {
    		return $feed->get();
    	}
    }
	
	public function findByName($name,$need_page=false)
    {
    	$feed = Feed::where('feed_name', 'like', '%'.$name.'%')
    	->orderBy('recommend_order', 'desc');
    
    	if($need_page){
    		return $feed->paginate(48);
    	} else {
    		return $feed->get();
    	}
    }
    
    /**
     * get feed list by type and status
     * @param unknown $type
     * @param unknown $status
     */
    public function getListByTypeStatus($type, $status)
    {
    	return Feed::where('type',$type)->where('status',$status)->get();
    }
	
	/**
     * get feed list by type and status
     * @param unknown $type
     * @param unknown $status
     */
    public function getListByActiveLevelStatus($active_level, $status)
    {
    	return Feed::where('status',$status)->where('active_level',$active_level)->get();
    }
    
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
    public function forUserByStatus(User $user,string $status)
    {
    	return Feed::where('user_id', $user->id)
		    	->where('status',$status)
		    	->get();
    }
    
    /**
     * Get goal for goal id.
     *
     * @param  User  $user
     * @param  int  $goal_id
     * @return Collection
     */
    public function forFeedId(User $user,$feed_id)
    {
    	return Feed::where('user_id', $user->id)
    	->where('id',$feed_id)
    	->get();
    }
    
    public function checkFeed(Feed $feed)
    {
    	\Log::info("Check Feed:".$feed->id.'|'.$feed->url);
    	 
    	$feedFactory = new FeedFactory(['cache.enabled' => false]);
    	$feeder = $feedFactory->make($feed->url);
    	$simplePieInstance = $feeder->getRawFeederObject();
    
    	if (!empty($simplePieInstance)) {
    		$feedSubs = FeedSub::where('feed_id',$feed->id)->where('status',1)->get();
    		
    		$previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));
    
    		$feed_last_published = '';
    		foreach ($simplePieInstance->get_items() as $item) {
    			//count the number of items that already exist in the database with the item url and feed_id
    			$results_url = Article::where([ 'feed_id' => $feed->id, 'url' => $item->get_permalink()])->count();
    			$results_title = Article::where([ 'feed_id' => $feed->id, 'subject' => $item->get_title()])->count();
    			$date = $item->get_date('Y-m-j H:i:s');
    
    			if ($results_url == 0 && $results_title == 0 && ! (strtotime($date) < strtotime($previousweek))) {
    				$article = new Article;
    
    				$article->feed_id = $feed->id;
    				$article->status = 'unread';
    				$article->url = $item->get_permalink();
    				$article->subject = $item->get_title();
    				$article->content = $item->get_description();
    				$article->published = $item->get_date('Y-m-j H:i:s');
    
    				$article->user_id = $feed->user_id;
    
    				$description =  $item->get_description();
    				preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $description, $image);
    				if (array_key_exists('src', $image)) {
    					try {
    						$arr = @getimagesize($image['src']);
    						if(!empty($arr) && $arr[0] > 50 && $arr[1] > 50){
    							$article->image_url = $image['src'];
    						}
    					} catch (Exception $e) {
    						\Log::info("getimagesize error:".$image['src']);
    					}
    				}
    
    				$article->save();
    				
    				\Log::info("Save Article:".$article->url);
    				
    				foreach ($feedSubs as $feedSub){
    					$articleSub = ArticleSub::where('user_id',$feedSub->user_id)->where('article_id',$article->id)->first();
    					if(empty($articleSub)){
    						$articleSub = new ArticleSub();
							$articleSub->feed_id = $feedSub->feed_id;
							$articleSub->user_id = $feedSub->user_id;
							$articleSub->article_id = $article->id;
							$articleSub->status = 'unread';
							$articleSub->published = $article->published;
							$articleSub->save();
							
							\Log::info("Save ArticleSub:".$articleSub->user_id.'|'.$articleSub->article_id);
    					}
    				}
    				
    				if(empty($feed_last_published) || strtotime($feed_last_published) < strtotime($article->published)){
    					$feed_last_published = $article->published;
    				}
    			}
    		}
    
    		//update feed updated_at record
    		if(count($simplePieInstance->get_items()) > 0){
    			if(!empty($feed_last_published)){
    				Feed::where('id', $feed->id)->update(['updated_at' => date('Y-m-j H:i:s'),'feed_desc' => $simplePieInstance->get_description(),'favicon' => $simplePieInstance->get_image_url(), 'last_published' => $feed_last_published]);
    			} else {
    				Feed::where('id', $feed->id)->update(['updated_at' => date('Y-m-j H:i:s'),'feed_desc' => $simplePieInstance->get_description(),'favicon' => $simplePieInstance->get_image_url()]);
    			}
    		}
    	}
    }
    
    public function checkFanfouFeed(Feed $feed)
    {
    	//set previous week
    	$previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));
    
    	\Log::info("Check Feed:".$feed->id.'|'.$feed->url);
    	
    	$feedSubs = FeedSub::where('feed_id',$feed->id)->where('status',1)->get();
    
    	$config = config('services.fanfou');
    	
    	$user = new User();
    	$user->id = $feed->user_id;
    	$thirdRepository = new ThirdRepository();
    	$third = $thirdRepository->forUserSource( $user);
    	if(empty($third)){
    		return ;
    	}
    	
    	$oauth_token = $third['token_value'];
    	$oauth_token_secret = $third['token_secret'];
    	
    	$ff_user = new FFClient( config("services.$driver.client_id"), config("services.$driver.client_secret") , $oauth_token , $oauth_token_secret );
    	
    	$items = $ff_user->friends_timeline(1,50);
    	$items = json_decode($items,true);
    	
    	if(empty($items)) {
    		return false;
    	}
    	
    	foreach ($items as $item) {
				if(isset($item['repost_status'])){
					$item = $item['repost_status'];
				}
				
    			$results_url = Article::where([ 'feed_id' => $feed->id, 'url' => 'http://fanfou.com/statuses/'.$item['id']])->count();
    			$date = date('Y-m-d H:i:s',strtotime($item['created_at']));
    			
    			$feed_last_published = '';
    			if ($results_url == 0  && ! (strtotime($date) < strtotime($previousweek))) {
    				$article = new Article;
    				
    				$content = $item['text']."&nbsp;&nbsp; ";
    				
    				if(isset($item['user'])){
    					$content = "<a href='http://fanfou.com/{$item['user']['unique_id']}'>@{$item['user']['name']}</a> &nbsp; $content";
    				}
    				
    				if(isset($item['photo'])){
    					$content = "$content<br><img width='' src='{$item['photo']['largeurl']}'/><a href='{$item['photo']['largeurl']}' target='_blank'>大图</a>";
    				}
    				
    
    				//get article content
    				$article->feed_id = $feed->id;
    				$article->status = 'unread';
    				$article->url = 'http://fanfou.com/statuses/'.$item['id'];
    				$article->subject = '';
    				$article->content = $content;
    				$article->published = date('Y-m-d H:i:s',strtotime($item['created_at']));
    
    				$article->user_id = $feed->user_id;
    
    				$description =  $item['text'];
    				$article->save();
    				
    				\Log::info("Save Article:".$article->url);
    
    				foreach ($feedSubs as $feedSub){
    					$articleSub = ArticleSub::where('user_id',$feedSub->user_id)->where('article_id',$article->id)->first();
    					if(empty($articleSub)){
    						$articleSub = new ArticleSub();
    						$articleSub->feed_id = $feedSub->feed_id;
    						$articleSub->user_id = $feedSub->user_id;
    						$articleSub->article_id = $article->id;
    						$articleSub->status = 'unread';
    						$articleSub->published = $article->published;
    						$articleSub->save();
    						
    						\Log::info("Save ArticleSub:".$articleSub->user_id.'|'.$articleSub->article_id);
    					}
    				}
    				
    				if(empty($feed_last_published) || strtotime($feed_last_published) < strtotime($article->published)){
    					$feed_last_published = $article->published;
    				}
    			}
    		}
    
    		//update feed updated_at record
    		if(count($items) > 0){
    			if(!empty($feed_last_published)){
		    		Feed::where('id', $feed->id)->update(['updated_at' => date('Y-m-j H:i:s'), 'last_published' => $feed_last_published]);
    			} else {
		    		Feed::where('id', $feed->id)->update(['updated_at' => date('Y-m-j H:i:s')]);
    			}
    		}
    	}
}
