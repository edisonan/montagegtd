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

class ArticleController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $categorys;
    
    protected $articles;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( CategoryRepository $categorys, ArticleRepository $articles)
    {
        $this->middleware('auth');

        $this->categorys = $categorys;
        $this->articles = $articles;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$categorys = $this->categorys->forUser($request->user());
    	if($request->has('status')){
    		$status = $request->status;
    	} else {
    		$status = 'unread';
    	}
    	
    	if($request->has('feed_id')){
    		$articles = $this->articles->forUserByStatusFeedId($request->user(), $status, $request->feed_id,$need_page=true);
    	} else {
    		$articles = $this->articles->forUserByStatus($request->user(), $status,$need_page=true);
    	}
    	
    	if(count($articles) == 0){
    		$recommend_feeds = Feed::where('user_id','!=' , $request->user()->id)->orderBy(\DB::raw('RAND()'))->take(4)->get();;
    	} else {
    		$recommend_feeds = array();
    	}
    	
        return view('articles.index', [
            'categorys' => $categorys,
        	'articles' => $articles,
        	'status' => $status,
        	'recommend_feeds' => $recommend_feeds,
        ]);
    }
    
    public function view(Request $request,Article  $article)
    {
    	$this->authorize('destroy', $article);
    	
    	if($article->status == 'unread'){
    		$article->status = 'read';
    		$article->update();
    	}

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE,$article);
        	return response($resp);
        } else {
        	return view('articles.view', [
    			'article' => $article,
    		]);
        }
    }
    
    public function star(Request $request,Article  $article)
    {
    	$this->authorize('destroy', $article);
    	 
    	if($article->status == 'star'){
    		$article->status = 'read';
    		$article->update();
    	} else {
    		$article->status = 'star';
    		$article->update();
    	}
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE,$article);
    		return response($resp);
    	} else {
    		return view('articles.view', [
    			'article' => $article,
    		]);
    	}
    }
    
    public function read(Request $request,Article  $article)
    {
    	$this->authorize('destroy', $article);
    
    	if(in_array($request->status,array('read','unread'))){
    		$article->status = 'read';
    		$article->update();
    	}
    	 
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE,$article);
    		return response($resp);
    	} else {
    		return view('articles.view', [
    				'article' => $article,
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
    public function destroy(Request $request, Article $article)
    {
        $this->authorize('destroy', $article);

        $article->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/articles');
        }
    }
}
