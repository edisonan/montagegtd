<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Feed;
use App\Repositories\FeedRepository;
use App\FeedSub;
use App\Repositories\FeedSubRepository;
use App\Category;
use App\Repositories\CategoryRepository;
use App\Article;


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
    	$feedSubs = $this->feedSubs->forUserByStatus($request->user(),1, $need_page=true);
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
        	$feed->feed_name = $request->feed_name;
        	$feed->url = $request->url;
        	$feed->category_id = $request->category_id;
        	$feed->save();
        }

        $feedSub = $request->user()->feedSubs()->create([
        	'feed_id' => $feed->id,
        	'feed_name' => $request->feed_name,
        	'category_id' => $request->category_id,
        ]);
        
        $this->feeds->checkFeed($feed);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds');
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

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds');
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
    		return redirect('/feeds');
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
    		return redirect('/articles');
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
    
    
}
