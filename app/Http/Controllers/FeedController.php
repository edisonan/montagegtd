<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use ArandiLopez\Feed\Factories\FeedFactory;
use App\Http\Controllers\Controller;

use App\Feed;
use App\Repositories\FeedRepository;
use App\FeedSub;
use App\Repositories\FeedSubRepository;
use App\Category;
use App\Repositories\CategoryRepository;
use App\Article;
use DB;


class FeedController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $feeds;
    protected $feedSubs;
    protected $categorys;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(CategoryRepository $categorys,FeedSubRepository $feedSubs, FeedRepository $feeds)
    {
        $this->middleware('auth');

        $this->feeds = $feeds;
        $this->feedSubs = $feedSubs;
        $this->categorys = $categorys;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$feedSubs = $this->feedSubs->forUserByStatus($request->user(), 1, $need_page=true);
    	$categorys = $this->categorys->forUser($request->user());
    	
    	if(count($categorys) == 0){
    		$category = $request->user()->categorys()->create([
	            'name' => '未分类',
	        	'category_order' => 0,
        	]);
    		$categorys = array($category);
    	}
    	
    	$title = $url = '';
    	
    	if($request->has('url')){
    		$url = $request->url;
    		$title = \App\Http\Utils\CommonUtil::page_title($request->url);
    	}
    	
        return view('feeds.index', [
            'feedSubs' => $feedSubs,
        	'categorys' => $categorys,
        	'url' => $url,
        	'title' => $title,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     */
    public function explorer(Request $request)
    {
    	$feeds = $this->feeds->forIsRecommend(1, $need_page=true);
		
		$categorys = $this->categorys->forUser($request->user());
    	
    	if(count($categorys) == 0){
    		$category = $request->user()->categorys()->create([
	            'name' => '未分类',
	        	'category_order' => 0,
        	]);
    		$categorys = array($category);
    	}
		
        return view('feeds.explorer', [
            'feeds' => $feeds,
            'categorys' => $categorys,
            'recommend_categorys' => Feed::$recommend_categorys,
        ]);
    }
	
	/**
     * 
     * @param Request $request
     */
    public function search(Request $request)
    {
    	if($request->has('recommend_category_id')){
    		$feeds = $this->feeds->findByRecommendCategoryId($request->recommend_category_id);
    	} else if($request->has('name')){
	    	$feeds = $this->feeds->findByName($request->name , $need_page=true);
    	} else {
    		echo 'error params'; exit;
    	}
    	
        return view('feeds.search', [
            'feeds' => $feeds,
        ]);
    }
    
    public function setting(Request $request)
    {
//     	$feedSubs = $this->feedSubs->forUserByStatus($request->user(), 1, $need_page=true);
//     	$categorys = $this->categorys->forUser($request->user());

    	$category_feed_infos = DB::select('select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name,f.id as feed_sub_id from feed_subs f right join categories c on f.category_id = c.id where c.user_id = :user_id2 and f.user_id = :user_id  and f.status =1 order by c.category_order asc,f.feed_order asc', [':user_id'=>$request->user()->id,':user_id2'=>$request->user()->id]);
    	
    	$nav_infos = array();
    	foreach ($category_feed_infos as $item){
    		$nav_infos[$item->category_id]['category_info'] = array('category_name'=>$item->category_name,'category_id'=>$item->category_id);
    	
    		$feed = array(
    				'feed_id' => $item->feed_id,
    				'feed_name' => $item->feed_name,
    				'feed_count' => isset($counts_info[$item->feed_id])?$counts_info[$item->feed_id]:0,
    				'feed_sub_id' => $item->feed_sub_id,
    		);
    	
    		$nav_infos[$item->category_id]['list'][] = $feed;
    	}
    	
    	if(count($nav_infos) == 0){
    		$category = $request->user()->categorys()->create([
    				'name' => '未分类',
    				'category_order' => 0,
    		]);
    		$nav_infos[] = array('category_info'=>array('category_name'=>$category->name,'category_id'=>$category->id),'list'=>array());
    	}
    	 
    	$title = $url = '';
    	 
    	if($request->has('url')){
    		$url = $request->url;
    		$title = \App\Http\Utils\CommonUtil::page_title($request->url);
    	}
    	 
    	return view('feeds.setting', [
    			'nav_infos' => $nav_infos,
    			'url' => $url,
    			'title' => $title,
    	]);
    }
    
    /**
     * Create a new note.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'feed_name' => 'required',
        	'url' => 'required',
        	'category_id' => 'required',
        ]);
        
        $category = $this->categorys->forCategoryId($request->user(),$request->category_id);
        if(empty($category)){
        	echo 'error:'.$request->category_id;exit;
        }
        
        $feed = Feed::where('url',$request->url)->first();
        if(empty($feed)){
        	$feed = new Feed();
        	$feed->user_id = $request->user()->id;
        	$feed->feed_name = $request->feed_name;
        	$feed->url = $request->url;
        	$feed->category_id = $request->category_id;
        	$feed->sub_count = 1;
        	$feed->save();
        } else {
	        $feedSub = $this->feedSubs->forUserByFeedId($request->user(),$feed->id,1);
	        
	        if(!empty($feedSub)){
	        	echo 'error:has exist feed!';exit;
	        }
	        
        	//如果未锁定，那么更改Feed的名称
        	if(empty($feed->recommend_name) && $feed->name != $request->feed_name){
        		$feed->feed_name = $request->feed_name;
        	}
        	$feed->sub_count = $feed->sub_count + 1;
        	$feed->save();
        }
		
        $feedSub = $request->user()->feedSubs()->create([
        	'feed_id' => $feed->id,
        	'feed_name' => $request->feed_name,
        	'category_id' => $request->category_id,
        ]);
        
        $articles = Article::where('feed_id',$feed->id)->orderBy('published','desc')->take(30)->get();
        if(!empty($articles)){
        	foreach ($articles as $article){
        		$articleSub = new \App\ArticleSub();
        		$articleSub->feed_id = $feed->id;
        		$articleSub->user_id = $request->user()->id;
        		$articleSub->article_id = $article->id;
        		$articleSub->status = 'unread';
        		$articleSub->save();
        	}
        } else {
	        $this->feeds->checkFeed($feed);
        }

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds')->with('message', 'IT WORKS!');
        }
        
    }
    
    /**
     * 快速订阅
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function quickstore(Request $request)
    {
    	$this->validate($request, [
    		'feed_id' => 'required',
    	]);
    
    	$category = $this->categorys->forCategoryName($request->user(), '未分类');
    	if(empty($category)){
    		$category = $request->user()->categorys()->create([
    			'name' => '未分类',
    			'category_order' => 0,
    		]);
    	}
    
    	$feed = Feed::where('id', $request->feed_id)->first();
    	if(!empty($feed)){
    		$feedSub = $this->feedSubs->forUserByFeedId($request->user(),$feed->id,1);
    		 
    		if(!empty($feedSub)){
    			$resp = $this->responseJson(1000,'','已经关注');
    			return response($resp);
    		}
    		$feed->sub_count = $feed->sub_count + 1;
    		$feed->save();
    	}
    
    	$feedSub = $request->user()->feedSubs()->create([
    		'feed_id' => $feed->id,
    		'feed_name' => $feed->feed_name,
    		'category_id' => $category->id,
    	]);
    
    	$articles = Article::where('feed_id',$feed->id)->orderBy('published','desc')->take(30)->get();
    	if(!empty($articles)){
    		foreach ($articles as $article){
    			$articleSub = new \App\ArticleSub();
    			$articleSub->feed_id = $feed->id;
    			$articleSub->user_id = $request->user()->id;
    			$articleSub->article_id = $article->id;
    			$articleSub->status = 'unread';
    			$articleSub->save();
    		}
    	} else {
    		$this->feeds->checkFeed($feed);
    	}
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE, '', '关注成功');
    		return response($resp);
    	} else {
    		return redirect('/feeds')->with('message', 'IT WORKS!');
    	}
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, FeedSub $feedSub)
    {
        $this->authorize('destroy', $feedSub);

        $feedSub->status = 2;
        $feedSub->update();
        
        $feed = $feedSub->feed;
        $feed->sub_count = $feed->sub_count-1;
        $feed->save();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds')->with('message', 'IT WORKS!');
        }
    }
    
    public function update(Request $request, FeedSub $feedSub)
    {
    	$this->authorize('destroy', $feedSub);
    	
    	if($request->method() == 'GET'){
    		$categorys = $this->categorys->forUser($request->user());
    		return view('feeds.update',array('feedSub'=>$feedSub,'categorys'=>$categorys));
    	}
    	
    	$this->validate($request, [
    			'feed_name' => 'required',
    			'category_id' => 'required',
    	]);
    	
    	$category = $this->categorys->forCategoryId($request->user(),$request->category_id);
    	if(empty($category)){
    		echo 'error:'.$request->category_id;exit;
    	}
    
    	$feedSub->update($request->all());
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/feeds')->with('message', 'IT WORKS!');
    	}
    }
    
    public function checkNewFeed(Request $request)
    {
    	$feedSubs = FeedSub::where('user_id',$request->user()->id)->orderBy('updated_at', 'asc')->take(15)->get();
    	
    	if (! empty($feedSubs)) {
    		foreach ($feedSubs as $feedSub) {
    			//update feed, see update function
    			$this->feeds->checkFeed($feedSub->feed);
    		}
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/articles')->with('message', 'IT WORKS!');
    	}
    	
    }
    
    public function checkFeedUrl(Request $request)
    {
    	$result_code = 1001;
    	
    	if($request->has('url')){
    		$feedFactory = new FeedFactory(['cache.enabled' => false]);
    		$feeder = $feedFactory->make($request->url);
    		$simplePieInstance = $feeder->getRawFeederObject();
    		 
    		//only add articles and update feed when results are found
    		if (!empty($simplePieInstance)) {
    			$result_code = self::OK_CODE;
    		}
    		
    		 
    		$title = \App\Http\Utils\CommonUtil::page_title($request->url);
    	}
    	
    	$resp = $this->responseJson($result_code, array('title'=>$title));
    	echo $resp;exit;
    	return response($resp);
    }
    
    public function sort(Request $request, FeedSub $feedSub)
    {
    	$this->validate($request, [
    			'feed_sub_ids' => 'required',
    	]);
    	 
    	$feed_sub_ids_arr = explode(',', $request->feed_sub_ids);
    	
    	$sort = 0;
    	foreach ($feed_sub_ids_arr as $feed_sub_id){
    		$feedSub = $this->feedSubs->forUserByFeedSubId($request->user(), $feed_sub_id, 1);
    		if(!empty($feedSub)){
    			$feedSub->update(array('feed_order' => $sort++));
    		}
    	}
    	
    	if(!empty($request->change_feed_sub_id) && !empty($request->change_feed_sub_category)){
    		$category = Category::where('user_id',$request->user()->id)->where("id", $request->change_feed_sub_category)->first();
    		if(!empty($category)){
    			$feedSub = FeedSub::where('user_id',$request->user()->id)->where("id", $request->change_feed_sub_id)->first();
    			if(!empty($feedSub)){
	    			$feedSub->update(array('category_id'=>$request->change_feed_sub_category));
    			}
    		}
    	}
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/feeds')->with('message', 'IT WORKS!');
    	}
    }
    
    public function importOpml(Request $request){
	    if ($request->file('opml_file')->isValid()) {
	    	$path = $request->opml_file->path();
	    	
	    	$importer = new \Celd\Opml\Importer(file_get_contents($path));
	    	$feedList = $importer->getFeedList();
	    	
		    $categorys = $this->categorys->forUser($request->user());
	    	 
	    	if(count($categorys) == 0){
	    		$category = $request->user()->categorys()->create([
	    				'name' => '未分类',
	    				'category_order' => 0,
	    		]);
	    		$categorys = array($category);
	    	}
	    	
	    	foreach ($feedList->getItems() as $item) {
	    		if ($item->getType() == 'category') {
	    			echo $item->getTitle()."bbbbbbbbbbbbbbbbb\n"; // Category title
	    			
	    			foreach($item->getFeeds() as $feed) {
	    				echo $feed->getTitle().'|'.$feed->getXmlUrl() . "\n";
	    			}
	    		} else {
		    		echo $item->getTitle().'|'.$item->getXmlUrl()." aaaaaaaaaaaaaaa\n"; //Root feed title
	    		}
	    	}
	    } else {
	    	echo 'error param!';exit;
	    }
	    exit;
    	
    }
    
    public function weiborss(Request $request)
    {
    	$categorys = $this->categorys->forUser($request->user());
    	 
    	if(count($categorys) == 0){
    		$category = $request->user()->categorys()->create([
    				'name' => '未分类',
    				'category_order' => 0,
    		]);
    		$categorys = array($category);
    	}
    	 
    	 
    	return view('feeds.weixinrss', [
    			'categorys' => $categorys,
    	]);
    }
    
    public function weixinrss(Request $request)
    {
    	$categorys = $this->categorys->forUser($request->user());
    
    	if(count($categorys) == 0){
    		$category = $request->user()->categorys()->create([
    				'name' => '未分类',
    				'category_order' => 0,
    		]);
    		$categorys = array($category);
    	}
    
    
    	return view('feeds.weiborss', [
    			'categorys' => $categorys,
    	]);
    }
    
    public function opml(Request $request)
    {
    	$categorys = $this->categorys->forUser($request->user());
    
    	if(count($categorys) == 0){
    		$category = $request->user()->categorys()->create([
    				'name' => '未分类',
    				'category_order' => 0,
    		]);
    		$categorys = array($category);
    	}
    
    	return view('feeds.opml', [
    			'categorys' => $categorys,
    	]);
    }
    
}
