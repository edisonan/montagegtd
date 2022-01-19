<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\Request;
use App\Services\TagService;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

/**
 * 笔记控制器
 *
 * @author edison.an
 *        
 */
class NoteController extends Controller {
	
	/**
	 * The note service instance.
	 *
	 * @var NoteService
	 */
	protected $noteService;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param NoteService $noteService        	
	 * @return void
	 */
	public function __construct(NoteService $noteService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		
		$this->noteService = $noteService;
	}
	
	/**
	 * 欢迎页
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function welcome(Request $request) {
		return view ( 'notes.welcome', [ ] );
	}
	
	/**
	 * 首页.
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request, $add_content = '') {
		$type = $request->input ( 'type', '' );
		$addContent = $request->input ( 'add_content', '' );
		
		$pomoId = $request->input ( 'pomo_id', 0 );
		$articleId = $request->input ( 'article_id', 0 );
		$taskId = $request->input ( 'task_id', 0 );
		
		$tagId = $request->input ( 'tag_id', 0 );
		$keyword = $request->input ( 'keyword', '' );
		
		$datas = $this->noteService->getIndexInfo ( $addContent, $type, $tagId, $keyword, $pomoId, $articleId, $taskId );
		
		return view ( 'notes.index', $datas );
	}
	
	/**
	 * 创建.
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'name' => 'required',
				'status' => 'required' 
		] );
		
		$fname = $request->input ( 'fname', '' );
		$addImage = $request->input ( 'add_image', '' );
		$name = $request->get ( 'name' );
		$status = $request->get ( 'status' );
		
		$pomoId = $request->input ( 'pomo_id', 0 );
		$articleId = $request->input ( 'article_id', 0 );
		$taskId = $request->input ( 'task_id', 0 );
		
		$this->noteService->store ( $name, $status, $addImage, $fname, $taskId, $articleId, $pomoId );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/notes' );
	}
	
	/**
	 * 删除.
	 *
	 * @param Request $request        	
	 * @param Note $note        	
	 */
	public function destroy(Request $request, Note $note) {
		$this->authorize ( 'destroy', $note );
		
		$note->delete ();
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/notes' );
	}
	
	/**
	 * 上传音频
	 *
	 * @param Request $request        	
	 * @return
	 *
	 */
	public function upload(Request $request) {
		if ($_FILES ["file"] ["type"] == 'audio/mp3') {
			$record_name = $request->user ()->id . $request->fname . '.mp3';
			move_uploaded_file ( $_FILES ["file"] ["tmp_name"], config ( "app.storage_path" ) . 'recorders/temp/' . $record_name );
		}
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/notes' );
	}
	
	/**
	 * 获取音频
	 *
	 * @param Request $request        	
	 * @param Note $note        	
	 */
	public function getRecord(Request $request, Note $note) {
		if ($note->user_id == $request->user ()->id || $note->status == 2) {
			header ( 'Content-type: audio/mp3' );
			readfile ( config ( "app.storage_path" ) . $note->record_path );
		} else {
			throw new CustomException ( "无权限" );
		}
	}
	public function update(Request $request, Note $note) {
	    $this->authorize ( 'destroy', $note );
	    
	    if ($request->method () == 'GET') {
		$note->name = preg_replace('/<br\\s*?\/??>/i',"",$note->name);
	        return view ( 'notes.update', array (
	            'note' => $note,
	        ) );
	    }
	    
	    $this->validate ( $request, [
	        'name' => 'required',
	        'status' => 'required'
	    ] );
	    
	    $this->noteService->update ( $note, $request->name, $request->status );
	    
	    return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/notes' );
	}
}
