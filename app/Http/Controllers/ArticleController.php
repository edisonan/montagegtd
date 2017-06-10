<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Category;
use App\Repositories\CategoryRepository;
use App\Article;
use App\Repositories\ArticleRepository;

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
    	
    	$articles = $this->articles->forUser($request->user(), $status);
    	
        return view('articles.index', [
            'categorys' => $categorys,
        	'articles' => $articles,
        ]);
    }
    
    public function view(Request $request,Article  $article)
    {
    	$this->authorize('destroy', $article);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE,$article);
        	return response($resp);
        } else {
        	return view('articles.index', [
    			'categorys' => $categorys,
    			'articles' => $articles,
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
