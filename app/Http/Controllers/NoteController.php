<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Note;
use App\Repositories\NoteRepository;
use App\Tag;
use App\NoteTagMap;
use App\Repositories\TagRepository;

class NoteController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $notes;
    protected $tags;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(NoteRepository $notes, TagRepository $tags)
    {
        $this->middleware('auth');

        $this->notes = $notes;
        $this->tags = $tags;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request,$add_content = '')
    {
    	$notes = $this->notes->forUserByStatus($request->user(), 2, $need_page=true);
    	
    	if($request->has('add_content')){
    		$add_content = $request->add_content;
    		if(\App\Http\Utils\CommonUtil::isUrl($add_content)){
    			$add_content = '#分享链接# '.$add_content.' '.\App\Http\Utils\CommonUtil::page_title($add_content);
    		}
    	}
    	
    	foreach ($notes as $key => $note){
    		if(!empty($note->noteTagMaps)){
    			foreach ($note->noteTagMaps as $noteTagMap){
    				$url = "/notes?tag_id=".$noteTagMap->tag->id;
    				$tag_name = '#'.$noteTagMap->tag->name.'#';
    				
    				$note->name = str_replace($tag_name, "<a href='$url'  target='_blank'>".$tag_name."</a>", $note->name);
    				$notes[$key] = $note;
    			}
    		}
    	}
    	
        return view('notes.index', [
            'add_content' => $add_content,
            'notes' => $notes,
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
        ]);
        
        file_put_contents("/root/cdapp/task/public/temp.mp3", $request->record_file);

        $name = htmlspecialchars($request->name);
        $name = str_replace('&lt;code&gt;', '<code>', $name);
        $name = str_replace('&lt;/code&gt;', '</code>', $name);
        $name = nl2br($name);
        $note = $request->user()->notes()->create([
            'name' => $name,
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

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/notes');
        }
        
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, Note $note)
    {
        $this->authorize('destroy', $note);

        $note->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/notes');
        }
    }
}
