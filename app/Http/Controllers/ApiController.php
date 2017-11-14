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
        $this->middleware('auth', ['except' => ['wechatlogin','articles','notes','explorer','articleview','addNote','articleSubStatus']]);

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
    	
    	if($request->has('page')){
    		$page = (int)$request->page;
    	} else {
    		$page = 0;
    	}
    	
    	if($request->has('status')){
    		$status = $request->status;
    	} else {
    		$status = 'read_later';
    	}
    	
    	$sql = 'select b.subject as title,b.image_url as image_url,b.published as published,a.id as article_sub_id, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from article_subs a,articles b,feeds c where b.subject != "" and a.user_id=:user_id and a.article_id = b.id and b.feed_id = c.id and a.status=:status';
    	$sql_param = [':user_id'=>$user->id,':status'=>$status];
    	
    	if($request->has('page_date')){
    		$sql .= ' and a.updated_at <= :page_date ';
    		$sql_param[':page_date'] = $request->page_date;
    	}
    	
    	if($request->has('feed_id')){
    		$sql .= ' and c.feed_id = :feed_id ';
    		$sql_param[':feed_id'] = $request->feed_id;
    	}
    	
    	$sql .= ' order by a.updated_at desc ';
    	$sql .= ' limit '. ($page*10) . ',10';
    	$articles = DB::select($sql, $sql_param);
    	
    	$resp = $this->responseJson(self::OK_CODE, $articles);
    	return response($resp);
    }
    
    public function articleview(Request $request)
    {
    	//     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	if(!$request->has('article_id')){
    		echo 'error';exit;
    	}
    	 
    	$sql = 'select b.subject as title,b.content as content,b.published as published, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from articles b,feeds c where  b.feed_id = c.id and b.id=:article_id limit 1';
    	$sql_param = [':article_id'=>$request->article_id];
    	$article = DB::select($sql, $sql_param);
    	if(count($article) == 1){
    		$article = $article[0];
    	} else {
    		echo 'error';exit;
    	}
    	 
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
    
    public function notes(Request $request)
    {
 //     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	$sql = 'select n.id as id,n.name as name,n.record_path as record_path,n.image_path as image_path,n.created_at as created_at,u.name as user_name from notes n,users u where n.user_id=u.id order by n.updated_at desc limit 10';
    	$sql_param = [];
    	$articles = DB::select($sql, $sql_param);
    	
    	$resp = $this->responseJson(self::OK_CODE, $articles);
    	return response($resp);
    }
    
    public function addNote(Request $request)
    {
    	//     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	 
    	$this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);
    	
    	if($request->status == false){
    		$request->status = 2;
    	} else {
    		$request->status = 1;
    	}

        $name = htmlspecialchars($request->name);
        $name = str_replace('&lt;code&gt;', '<code>', $name);
        $name = str_replace('&lt;/code&gt;', '</code>', $name);
        $name = nl2br($name);
        $note = $user->notes()->create([
            'name' => $name,
            'record_path' => '',
            'image_path' => '',
            'status' => $request->status,
        ]);
        
        preg_match_all('/#(.*?)#/i',$request->name,$match);
        foreach ($match[0] as $item){
        	$tag_name = trim($item,'#');
        	if(empty($tag_name)){
        		continue;
        	}
        	
        	$tag = $this->tags->forTagName($tag_name);
        	if(empty($tag)){
        		$tag = Tag::create(array('name'=>$tag_name));
        	}
        	
        	$tagNote = new NoteTagMap();
        	$tagNote->create(array('tag_id'=>$tag->id, 'note_id'=>$note->id));
        }
    	 
    	$resp = $this->responseJson(self::OK_CODE, $note);
    	return response($resp);
    }
    
    public function articleSubStatus(Request $request,ArticleSub  $articleSub)
    {
    	//     	$user = $request->user();
    	$user = new User();$user->id = 1;//TODO 模拟
    	
    	if($request->has('ids')){
    		$id_arr = explode(',', $request->ids);
    		foreach ($id_arr as $id){
    			$articleSub = ArticleSub::where('id',$id)->where('user_id',$user->id)->first();
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
    		$articleSubs = ArticleSub::where('user_id',$user->id)->where('status','unread')->where('feed_id',$request->feed_id)->get();
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
    		if($articleSub->user_id != $user->id){
    			echo 'error user!';exit;
    		}
    		if(in_array($request->status,array('read','unread', 'read_later', 'star'))){
    			$articleSub->status = $request->status;
    			$articleSub->updated_at = date('Y-m-d H:i:s');
    			$articleSub->update();
    		}
    	}
    
    	$resp = $this->responseJson(self::OK_CODE,$articleSub->article);
    	return response($resp);
    }
    
}
