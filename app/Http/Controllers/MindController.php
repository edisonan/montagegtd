<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mind;
use App\Repositories\MindRepository;

/**
 * 思维导图控制器
 *
 * @author edison.an
 *        
 */
class MindController extends Controller {
	
	/**
	 * The mind repository instance.
	 *
	 * @var MindRepository
	 */
	protected $minds;
	protected $tags;
	
	/**
	 * Create a new controller instance.
	 *
	 * @param MindRepository $minds        	
	 * @return void
	 */
	public function __construct(MindRepository $minds) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		
		$this->minds = $minds;
	}
	
	/**
	 * 欢迎页
	 * 
	 * @param Request $request        	
	 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function welcome(Request $request) {
		return view ( 'minds.welcome', [ ] );
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request, $add_content = '') {
		$minds = $this->minds->forUserByStatus ( $request->user (), 1, 1, $needPage = true );
		
		return view ( 'minds.index', [ 
				'minds' => $minds 
		] );
	}
	
	/**
	 * 创建.
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$is_root = 1;
		$parent_mind_id = 0;
		
		if ($request->has ( 'parent_mind_id' )) {
			$parentMind = Mind::where ( 'id', $request->parent_mind_id )->where ( 'user_id', $request->user ()->id )->first ();
			if (empty ( $parentMind )) {
				return redirect ( '/minds' )->with ( 'message', '操作成功!' );
			}
			$parent_mind_id = $request->parent_mind_id;
			$is_root = 0;
		}
		
		$this->validate ( $request, [ 
				'name' => 'required' 
		] );
		
		$mind = $request->user ()->minds ()->create ( [ 
				'name' => htmlspecialchars ( $request->name ),
				'parent_mind_id' => $parent_mind_id,
				'is_root' => $is_root 
		] );
		
		if ($request->ajax () || $request->wantsJson () || $request->has ( 'json_wants' )) {
			$resp = $this->responseJson ( self::OK_CODE, array (
					'id' => $mind->id,
					'name' => $mind->name 
			) );
			return response ( $resp );
		} else {
			return redirect ( '/mind/' . $mind->id )->with ( 'message', '操作成功!' );
		}
	}
	
	/**
	 * 删除.
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 */
	public function destroy(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		
		$this->removeMind ( $mind );
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/minds' )->with ( 'message', '操作成功!' );
		}
	}
	
	/**
	 * 更新
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function update(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		
		if ($request->has ( 'name' )) {
			$mind->name = $request->name;
		}
		
		if ($request->has ( 'content' )) {
			$content = str_replace ( array (
					"\r\n",
					"\r",
					"\n" 
			), "\\r\\n", $request->content );
			$content = str_replace ( array (
					"'",
					'"' 
			), array (
					'`',
					'``' 
			), $content );
			$mind->content = $content;
		}
		$mind->update ();
		
		if ($request->ajax () || $request->wantsJson () || $request->has ( 'json_wants' )) {
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return redirect ( '/minds' )->with ( 'message', '操作成功!' );
		}
	}
	
	/**
	 * 查看
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function view(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		
		$datas = $this->getNodeTreeData ( $mind );
		$jsmind_datas = array ();
		$jsmind_datas ['meta'] = array (
				'name' => $mind->name,
				'author' => $request->user ()->name,
				'version' => "1.0" 
		);
		$jsmind_datas ['format'] = 'node_tree';
		$jsmind_datas ['data'] = $datas;
		
		if ($request->ajax () || $request->wantsJson ()) {
			$resp = $this->responseJson ( self::OK_CODE );
			return response ( $resp );
		} else {
			return view ( 'minds.view', [ 
					'mind' => $mind,
					'jsmind_datas' => json_encode ( $jsmind_datas ) 
			] );
		}
	}
	
	/**
	 * 获取树结构
	 * 
	 * @param unknown $mind        	
	 * @param number $level        	
	 * @return mixed[]|NULL[]
	 */
	public function getNodeTreeData($mind, $level = 0) {
		$data = array ();
		$data ['id'] = $mind->id;
		$data ['topic'] = $mind->name;
		$data ['content'] = $mind->content;
		$data ['content'] = str_replace ( "'", '', $data ['content'] );
		if (count ( $mind->childrenMinds ) > 0) {
			foreach ( $mind->childrenMinds as $childMind ) {
				$data ['children'] [] = $this->getNodeTreeData ( $childMind, $level + 1 );
			}
		}
		return $data;
	}
	
	/**
	 * 移除节点
	 * 
	 * @param unknown $mind        	
	 * @return boolean
	 */
	public function removeMind($mind) {
		if (count ( $mind->childrenMinds ) != 0) {
			foreach ( $mind->childrenMinds as $childMind ) {
				$this->removeMind ( $childMind );
			}
		}
		$mind->delete ();
		return true;
	}
}
