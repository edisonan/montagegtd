<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Category;
use App\Repositories\CategoryRepository;
use App\Article;
use App\Repositories\ArticleRepository;
use App\Feed;
use App\ArticleSub;
use App\FeedSub;
use DB;
use App\Repositories\FeedSubRepository;
use App\Repositories\ArticleSubRepository;

class ArticleController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $categorys;
    
    protected $articles;
    
    protected $feedSubs;
    
    protected $articleSubs;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( CategoryRepository $categorys, ArticleRepository $articles, FeedSubRepository $feedSubs, ArticleSubRepository $articleSubs)
    {
        $this->middleware('auth', ['except' => ['welcome','view']]);

        $this->categorys = $categorys;
        $this->articles = $articles;
        $this->articleSubs = $articleSubs;
        $this->articleSubs = $articleSubs;
    }
    
    public function welcome(Request $request)
    {
    	return view('articles.welcome', []);
    }
    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$page_params = array();
    	$feed_id = '';
    	
    	if($request->has('status')){
    		$status = $request->status;
    	} else {
    		$status = 'unread';
    	}
    	$page_params['status'] = $status;
    	
    	$category_feed_infos = DB::select('select c.id as category_id,c.name as category_name,f.feed_id as feed_id,f.feed_name as feed_name from feed_subs f,categories c where f.category_id = c.id and f.user_id = :user_id and f.status =1 order by c.category_order asc,f.feed_order asc', [':user_id'=>$request->user()->id]);
    	
    	$temp_counts = ArticleSub::select('feed_id',DB::raw('count(*) as total'))->where('user_id',$request->user()->id)->where('status',$status)->groupBy('feed_id')->get();
    	$counts_info = array();
    	foreach ($temp_counts as $temp_count){
    		$counts_info[$temp_count['feed_id']] = $temp_count['total'];
    	}
    	
    	$nav_infos = array();
    	$next_recommend_feed = array();
    	foreach ($category_feed_infos as $item){
    		$nav_infos[$item->category_id]['category_info'] = array('category_name'=>$item->category_name,'category_id'=>$item->category_id);
    		
    		$feed = array(
    			'feed_id' => $item->feed_id,	
    			'feed_name' => $item->feed_name,	
    			'feed_count' => isset($counts_info[$item->feed_id])?$counts_info[$item->feed_id]:0,	
    		);
    		
    		if($feed['feed_count'] != 0 && empty($next_recommend_feed)){
    			if($request->has('feed_id')){
    				$request->feed_id != $feed['feed_id'] ? $next_recommend_feed = $feed : '';
    			} else {
    				$next_recommend_feed = $feed;
    			}
    		}
    		
    		$nav_infos[$item->category_id]['list'][] = $feed;
    	}
    	
    	foreach ($nav_infos as $key=>$val){
    		$nav_infos[$key]['list'] = $this->sortFeed($nav_infos[$key]['list']);
    	}
    	
    	if($request->has('feed_id')){
    		$articleSubs = $this->articleSubs->forUserByStatusFeedId($request->user(), $status, $request->feed_id,$need_page=true);
    		$page_params['feed_id'] = $request->feed_id;
    		$feed_id = $request->feed_id;
    	} else {
    		$articleSubs = $this->articleSubs->forUserByStatus($request->user(), $status,$need_page=true);
    	}
    	
    	if(count($articleSubs) == 0){
    		$recommend_feeds = Feed::where('user_id','!=' , $request->user()->id)->orderBy(\DB::raw('RAND()'))->take(8)->get();
    	} else {
    		$recommend_feeds = array();
    	}
    	
    	$unable_img = isset($_COOKIE['unable_img'])?$_COOKIE['unable_img']:"false";
    	$unable_desc = isset($_COOKIE['unable_desc'])?$_COOKIE['unable_desc']:"false";
    	
        return view('articles.index', [
            'nav_infos' => $nav_infos,
        	'articleSubs' => $articleSubs,
        	'status' => $status,
        	'feed_id' => $feed_id,
        	'page_params' => $page_params,
        	'counts_info' => $counts_info,
        	'recommend_feeds' => $recommend_feeds,
        	'next_recommend_feed' => $next_recommend_feed,
        	'unable_img' => $unable_img,
        	'unable_desc' => $unable_desc,
        ]);
    }
    
    public function list(Request $request)
    {
    	$page_params = array();
    	 
    	if($request->has('feed_id')){
    		$articles = $this->articles->forUserByFeedId($request->user(), $request->feed_id,$need_page=true);
    		$page_params['feed_id'] = $request->feed_id;
    	} else {
    		echo 'error param';exit;
    	}
    	
    	$feed = Feed::where('id',$request->feed_id)->first();
    	
    	return view('articles.list', [
    			'articles' => $articles,
    			'feed' => $feed,
    			'page_params' => $page_params,
    	]);
    }
    
    public function view(Request $request,Article  $article)
    {
        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE,$article);
        	return response($resp);
        } else {
        	return view('articles.view', [
    			'article' => $article,
    		]);
        }
    }
    
    public function star(Request $request,ArticleSub  $articleSub)
    {
    	$this->authorize('destroy', $articleSub);
    	 
    	if(!$articleSub->active){
    		$articleSub->status = 'read';
    		$articleSub->update();
    	} else {
    		$articleSub->status = 'star';
    		$articleSub->update();
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE,$articleSub->article);
    		return response($resp);
    	} else {
    		return view('articles.view', [
    			'article' => $articleSub->article,
    		]);
    	}
    }
    
    public function read_later(Request $request,ArticleSub $articleSub)
    {
    	$this->authorize('destroy', $articleSub);
    
    	if($articleSub->status == 'star'){
    		$articleSub->status = 'read';
    		$articleSub->update();
    	} else {
    		$articleSub->status = 'star';
    		$articleSub->update();
    	}
    	 
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE,$articleSub->article);
    		return response($resp);
    	} else {
    		return view('articles.view', [
    				'article' => $articleSub->article,
    		]);
    	}
    }
    
    public function status(Request $request,ArticleSub  $articleSub)
    {
    	if($request->has('ids')){
			$id_arr = explode(',', $request->ids);
			foreach ($id_arr as $id){
				$articleSub = ArticleSub::where('id',$id)->where('user_id',$request->user()->id)->first();
				if(empty($articleSub)){
					continue;
				} else {
					if($articleSub->status == 'unread'){
						$articleSub->status = 'read';
						$articleSub->updated_at = date('Y-m-d H:i:s');
						$articleSub->update();
					}
				}
			}
    	} else if($request->has('feed_id')) {
    		$articleSubs = ArticleSub::where('user_id',$request->user()->id)->where('status','unread')->where('feed_id',$request->feed_id)->get();
    		foreach ($articleSubs as $articleSub){
	    		if(empty($articleSub)){
	    			continue;
	    		} else {
	    			$articleSub->status = 'read';
	    			$articleSub->updated_at = date('Y-m-d H:i:s');
	    			$articleSub->update();
	    		}
    		}
    	} else {
	    	$this->authorize('destroy', $articleSub);
	    	if(in_array($request->status,array('read','unread', 'read_later', 'star'))){
	    		$articleSub->status = $request->status;
	    		$articleSub->updated_at = date('Y-m-d H:i:s');
	    		$articleSub->update();
	    	}
    	}
    	 
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE,$articleSub->article);
    		return response($resp);
    	} else {
    		return view('articles.view', [
    				'article' => $articleSub->article,
    		]);
    	}
    }
    
    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, ArticleSub $articleSub)
    {
        $this->authorize('destroy', $articleSub);

        $articleSub->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/articles')->with('message', 'IT WORKS!');
        }
    }
    
    private function sortFeed($feeds){
    	foreach ($feeds as $key=>$feed){
    		if($feed['feed_count'] == 0){
    			$feeds[] = $feed;
	    		unset($feeds[$key]);
    		}
    	}
    	return $feeds;
    }
}
