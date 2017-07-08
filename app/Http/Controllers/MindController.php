<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Mind;
use App\Repositories\MindRepository;

class MindController extends Controller
{
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
     * @param  MindRepository  $minds
     * @return void
     */
    public function __construct(MindRepository $minds)
    {
        $this->middleware('auth', ['except' => ['welcome']]);

        $this->minds = $minds;
    }
    
    public function welcome(Request $request)
    {
    	return view('minds.welcome', []);
    }

    /**
     * Display a list of all of the user's mind.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request,$add_content = '')
    {
    	$minds = $this->minds->forUserByStatus($request->user(), 1, 1, $need_page=true);
    	
        return view('minds.index', [
            'minds' => $minds,
        ]);
    }
    
    /**
     * Create a new mind.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    	$is_root = 1;
    	$parent_mind_id = 0;
    	
    	if($request->has('parent_mind_id')){
    		$parentMind = Mind::where('id',$request->parent_mind_id)->where('user_id',$request->user()->id)->first();
    		if(empty($parentMind)){
    			redirect('/minds');
    		}
    		$parent_mind_id = $request->parent_mind_id;
    		$is_root = 0;
    	}
    	
        $this->validate($request, [
            'name' => 'required',
        ]);
        
        $mind = $request->user()->minds()->create([
            'name' => htmlspecialchars($request->name),
            'parent_mind_id' => $parent_mind_id,
        	'is_root' => $is_root,
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE,array(
        			'id'=>$mind->id,
        			'name'=>$mind->name
        	));
        	return response($resp);
        } else {
        	return redirect('/mind/'.$mind->id);
        }
    }

    /**
     * Destroy the given mind.
     *
     * @param  Request  $request
     * @param  Mind  $mind
     * @return Response
     */
    public function destroy(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);

        $this->removeMind($mind);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/minds');
        }
    }
    
    public function update(Request $request, Mind $mind)
    {
    	$this->authorize('destroy', $mind);
    
    	$mind->name = $request->name;
    	$mind->update();
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/minds');
    	}
    }
    
    public function view(Request $request, Mind $mind)
    {
    	$this->authorize('destroy', $mind);
    	
    	$datas = $this->getNodeTreeData($mind);
    	$jsmind_datas = array();
    	$jsmind_datas['meta'] = array(
    		'name'=>$mind->name,
    		'author'=>$request->user()->name,
    		'version'=>"1.0",
    	);
    	$jsmind_datas['format'] = 'node_tree';
    	$jsmind_datas['data'] = $datas;
    	
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return view('minds.view', [
	            'mind' => $mind,
	            'jsmind_datas' => json_encode($jsmind_datas),
	        ]);
    	}
    }
    
    public function getNodeTreeData($mind , $level=0){
    	$data = array();
    	$data['id'] = $mind->id;
    	$data['topic'] = $mind->name;
    	$data['data']['content'] = $mind->content;
    	if(count($mind->childrenMinds) > 0){
    		foreach ($mind->childrenMinds as $childMind){
    			$data['children'][] = $this->getNodeTreeData($childMind, $level+1);
    		}
    	}
    	return $data;
    }
    
    public function removeMind($mind){
    	if(count($mind->childrenMinds) != 0){
    		foreach ($mind->childrenMinds as $childMind){
    			$this->removeMind($childMind);
    		}
    	}
    	$mind->delete();
    	return true;
    }
}
