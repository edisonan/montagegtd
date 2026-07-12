<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mind;
use App\Services\MindService;
use App\Http\Utils\ResponseDataUtil;

/**
 * 思维导图控制器
 *
 * @author edison.an
 *        
 */
class MindController extends Controller {
	
	/**
	 * MindService 实例.
	 *
	 * @var MindService
	 */
	protected $mindService;
	
	/**
	 * 构造方法
	 *
	 * @param MindService $minds        	
	 * @return void
	 */
	public function __construct(MindService $mindService) {
		$this->middleware ( 'auth', [ 
				'except' => [ 
						'welcome' 
				] 
		] );
		
		$this->mindService = $mindService;
	}
	public function welcome(Request $request) {
		return view ( 'minds.welcome', [ ] );
	}
	
	/**
	 * 首页
	 *
	 * @param Request $request        	
	 */
	public function index(Request $request, $add_content = '') {
		return view ( 'minds.index', [ ] );
	}
	
	/**
	 * 新增思维导图
	 *
	 * @param Request $request        	
	 */
	public function store(Request $request) {
		$this->validate ( $request, [ 
				'name' => 'required',
				'content' => 'nullable|string',
				'source_type' => 'nullable|string|max:50',
				'source_id' => 'nullable|integer|min:1'
		] );
		
		$name = $request->get ( 'name' );
		$parentMindId = $request->input ( 'parent_mind_id', 0 );
		$content = $request->input ( 'content', null );
		$sourceType = $request->input ( 'source_type', null );
		$sourceId = $request->input ( 'source_id', null );
		
		$mind = $this->mindService->store ( $name, $parentMindId, $content, $sourceType, $sourceId );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc ( array (
				'id' => $mind->id,
				'name' => $mind->name 
		) ), '/mind/' . $mind->id );
	}
	
	/**
	 * 删除思维导图
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 */
	public function destroy(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		
		$this->mindService->removeMind ( $mind );
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/minds' );
	}
	
	/**
	 * 更新思维导图
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return
	 *
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
			$mind->content = $content;
		}
		$mind->update ();
		
		return $this->jsonAndRedirectAutoResponse ( $request, ResponseDataUtil::genSimpleSucc (), '/minds' );
	}
	
	/**
	 * 查看思维导图
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return
	 *
	 */
	public function view(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );

		return view('minds.view', []);
	}

	/**
	 * 探索版思维导图：保留原编辑页，同时提供更适合浏览和聚焦的沉浸式渲染。
	 */
	public function exploreView(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );

		return view('minds.explore', []);
	}
	
	/**
	 * 大纲模式思维导图思维导图
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return
	 *
	 */
	public function outlineView(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );

		return view('minds.outlineview', []);
	}
	
	public function outlineViewv2(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );

		return view('minds.outlineviewv2', []);
	}
	
	/**
	 * 通过获取节点思维导图信息
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return
	 *
	 */
	public function ajaxget(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		$jsmindDatas = $this->mindService->getJsMindFormatInfo ( $mind );
		
		return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( array (
				'jsmind_datas' => json_encode ( $jsmindDatas ) 
		) ) );
	}
	
	/**
	 * 通过获取节点思维导图信息
	 *
	 * @param Request $request        	
	 * @param Mind $mind        	
	 * @return
	 *
	 */
	public function ajaxoutlineget(Request $request, Mind $mind) {
		$this->authorize ( 'destroy', $mind );
		// $datas = $this->mindService->getNodeTreeMarkDownData ( $mind );
		$datas = $this->mindService->getNodeTreeHtmlData ( $mind );
		
		return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( array (
				'datas' => $datas 
		) ) );
	}
	
	public function addTag(Request $request, Mind $mind) {
        $this->authorize ( 'destroy', $mind );
        if($mind->is_root != 1) {
            throw new CustomException('Root节点错误');
        }

        $this->validate($request, array(
            'tag_name' => 'required',
        ));

        $tag = $this->mindService->addTag ( $mind,  $request->tag_name);

        return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( array (
            'tag' => $tag
        ) ) );
    }
}
