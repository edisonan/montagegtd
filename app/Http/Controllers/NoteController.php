<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\Request;
use App\Services\TagService;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;
use Illuminate\Support\Facades\Hash;

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
						'welcome',
						'share' 
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
	public function index(Request $request) {
		return view ( 'notes.index', [ ] );
	}

	public function manage(Request $request) {
		return view ( 'notes.manage', [ ] );
	}
	
	/**
	 * 创建.
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'status' => 'required' 
		] );
		
		$fname = $request->input ( 'fname', '' );
		$addImage = $request->input ( 'add_image', '' );
		list($name, $content) = $this->resolveNameAndContent($request);
		if (trim($content) === '' && trim($name) === '' && empty($fname) && empty($addImage)) {
			throw new CustomException('笔记正文不能为空');
		}
		$status = $request->get ( 'status' );
		
		$sourceType = $request->input ( 'source_type', 0 );
		$sourceId = $request->input ( 'source_id', 0 );
		$tags = $request->has('tags') ? $request->input('tags') : null;

		$this->noteService->store ( $name, $content, $status, $addImage, $fname, $sourceType, $sourceId, $tags );
		
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
	        return view ( 'notes.update', array (
	            'note_id' => (int)$note->id
	        ) );
	    }
	    
	    $this->validate ( $request, [
	        'status' => 'required'
	    ] );
	    list($name, $content) = $this->resolveNameAndContent($request);
	    if (trim($content) === '' && trim($name) === '') {
	        throw new CustomException('笔记正文不能为空');
	    }
	    $tags = $request->has('tags') ? $request->input('tags') : null;
	    
	    $this->noteService->update ( $note, $name, $content, $request->status, $tags );
	    
	    return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/notes' );
	}

	/**
	 * 公开笔记分享页（免登录）。
	 * 通过随机码 share_token 定位笔记（不使用笔记ID），
	 * 若笔记带分享密码，则需要 URL 中携带正确的 key 才能查看。
	 *
	 * @param Request $request        	
	 * @param string $token        	
	 */
	public function share(Request $request, $token) {
		$note = Note::where ( 'share_token', $token )->first ();
		
		// 随机码不存在或笔记非公开：不泄露任何信息
		if (empty ( $note ) || ( int ) $note->status !== 2) {
			return view ( 'notes.share', array (
					'note' => null,
					'locked' => false,
					'wrong' => false 
			) );
		}
		
		// 带密码分享：校验 URL 中的 key
		if (! empty ( $note->share_password )) {
			$key = ( string ) $request->input ( 'key', '' );
			if ($key === '' || ! Hash::check ( $key, $note->share_password )) {
				return view ( 'notes.share', array (
						'note' => $note,
						'locked' => true,
						'wrong' => $key !== '' 
				) );
			}
		}
		
		$note->load ( 'user', 'noteTagMaps.tag' );
		
		return view ( 'notes.share', array (
				'note' => $note,
				'locked' => false,
				'wrong' => false 
		) );
	}

	protected function resolveNameAndContent(Request $request) {
		if ($request->has('content')) {
			return array(
				$request->input('name', ''),
				$request->input('content', '')
			);
		}

		return array('', $request->input('name', ''));
	}
}
