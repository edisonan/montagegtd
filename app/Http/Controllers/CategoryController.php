<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Category;
use App\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $categorys;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( CategoryRepository $categorys)
    {
        $this->middleware('auth');

        $this->categorys = $categorys;
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
    	
        return view('categorys.index', [
            'categorys' => $categorys,
        ]);
    }
    
    /**
     * Create a new note.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        	'caegory_order' => 'required',
        ]);
        
        $note = $request->user()->categorys()->create($request->all());

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/categorys');
        }
        
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, Category $category)
    {
        $this->authorize('destroy', $category);
        
        if(empty($category->feeds())){
        	$category->delete();
        } else {
        	echo 'This category has Feeds!cannot delete!';exit;
        }

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/categorys');
        }
    }
    
    public function update(Request $request, Category $category)
    {
    	$this->authorize('destroy', $category);
    	
    	if(empty($request->all())){
    		return view('categorys.update',array('category'=>$category));
    	}
    	
    	$this->validate($request, [
    			'name' => 'required',
    			'category_order' => 'required',
    	]);
    
    	$category->update($request->all());
    
    	if ($request->ajax() || $request->wantsJson()) {
    		$resp = $this->responseJson(self::OK_CODE);
    		return response($resp);
    	} else {
    		return redirect('/categorys');
    	}
    }
}
