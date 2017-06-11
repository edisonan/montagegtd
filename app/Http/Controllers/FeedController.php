<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Feed;
use App\Repositories\FeedRepository;
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
    protected $categorys;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(CategoryRepository $categorys, FeedRepository $feeds)
    {
        $this->middleware('auth');

        $this->feeds = $feeds;
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
    	$feeds = $this->feeds->forUser($request->user(), $need_page=true);
    	$categorys = $this->categorys->forUser($request->user());
    	
    	$title = $url = '';
    	
    	if($request->has('url')){
    		$url = $request->usl;
    		$title = \App\Http\Utils\CommonUtil::page_title($request->url);
    	}
    	
        return view('feeds.index', [
            'feeds' => $feeds,
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

        $feed = $request->user()->feeds()->create([
            'feed_name' => $request->feed_name,
        	'url' => $request->url,
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
    public function destroy(Request $request, Feed $feed)
    {
        $this->authorize('destroy', $feed);

        $feed->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds');
        }
    }
    
    public function update(Request $request, Feed $feed)
    {
    	$this->authorize('destroy', $feed);
    	
    	if($request->method() == 'GET'){
    		$categorys = $this->categorys->forUser($request->user());
    		return view('feeds.update',array('feed'=>$feed,'categorys'=>$categorys));
    	}
    	
    	$this->validate($request, [
    			'feed_name' => 'required',
    			'url' => 'required',
    			'category_id' => 'required',
    	]);
    	
    	$category = $this->categorys->forCategoryId($request->user(),$request->category_id);
    	if(empty($category)){
    		echo 'error:'.$request->category_id;exit;
    	}
    
    	$feed->update($request->all());
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/feeds');
    	}
    }
    
    public function checkNewFeed(Request $request)
    {
    	$feeds = Feed::where('user_id',$request->user()->id)->orderBy('updated_at', 'asc')->take(15)->get();
    	
    	if (! empty($feeds)) {
    		foreach ($feeds as $feed) {
    			//update feed, see update function
    			$this->feeds->checkFeed($feed);
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
