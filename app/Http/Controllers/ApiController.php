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
use App\User;

class ApiController extends Controller
{
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
        $this->middleware('auth', ['except' => ['wechatlogin','articles','notes','explorer']]);

        $this->categorys = $categorys;
        $this->articles = $articles;
        $this->articleSubs = $articleSubs;
        $this->articleSubs = $articleSubs;
    }
    
    /**
     * 微信登录相关处理
     * @param Request $request
     */
    public function wechatlogin(Request $request)
    {
    	
    }
    
    /**
     * 获取文章相关操作
     *
     * @param  Request  $request
     * @return Response
     */
    public function articles(Request $request)
    {
//     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	if($request->has('page') && is_int($request->page)){
    		$page = $request->page;
    	} else {
    		$page = 0;
    	}
    	
    	if($request->has('status')){
    		$status = $request->status;
    	} else {
    		$status = 'unread';
    	}
    	$sql = 'select b.subject as title,b.published as published,a.id as article_sub_id, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from article_subs a,articles b,feeds c where b.subject != "" and a.user_id=:user_id and a.article_id = b.id and b.feed_id = c.id and a.status=:status';
    	$sql_param = [':user_id'=>$user->id,':status'=>$status];
    	if($request->has('feed_id')){
    		$sql .= ' and c.feed_id = :feed_id ';
    		$sql_param[':feed_id'] = $request->feed_id;
    	}
    	
    	$sql .= ' order by a.updated_at desc ';
    	$sql .= ' limit '. ($page*20) . ',20';
    	$articles = DB::select($sql, $sql_param);
    	
    	$resp = $this->responseJson(self::OK_CODE, $articles);
    	return response($resp);
    }
    
    public function articles(Request $request)
    {
    	//     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	if(!$request->has('article_id')){
    		echo 'error';exit;
    	}
    	 
    	$sql = 'select b.subject as title,b.content as content,b.published as published, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from articles b,feeds c where  b.feed_id = c.id and b.id=:article_id limit 1';
    	$sql_param = [':article_id'=>$request->article_id];
    	$article = DB::select($sql, $sql_param);
    	 
    	$resp = $this->responseJson(self::OK_CODE, $article);
    	return response($resp);
    }
    
    /**
     * 发现
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function explorer(Request $request)
    {
    	$sql = 'select feed_name,feed_desc,favicon from feeds where is_recommend = 1 order by rand() limit 10';
    	$sql_param = [];
    	$articles = DB::select($sql, $sql_param);
    	 
    	$resp = $this->responseJson(self::OK_CODE, $articles);
    	return response($resp);
    }
    
    public function notes()
    {
 //     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	$sql = 'select name,record_path,image_path,created_at from notes order by updated_at desc limit 10';
    	$sql_param = [];
    	$articles = DB::select($sql, $sql_param);
    	
    	$resp = $this->responseJson(self::OK_CODE, $articles);
    	return response($resp);
    }
    
}
