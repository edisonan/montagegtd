<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Repositories\NoteRepository;
use App\Models\Tag;
use App\Models\NoteTagMap;
use App\Repositories\TagRepository;
use App\Models\Pomo;
use App\Models\Task;
use App\Models\Article;

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
     * @param NoteRepository $notes
     * @param TagRepository $tags
     * @return void
     */
    public function __construct(NoteRepository $notes, TagRepository $tags)
    {
        $this->middleware('auth', [
            'except' => [
                'welcome'
            ]
        ]);
        
        $this->notes = $notes;
        $this->tags = $tags;
    }

    public function welcome(Request $request)
    {
        return view('notes.welcome', []);
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param Request $request
     */
    public function index(Request $request, $add_content = '')
    {
    	if($request->has('pomo_id')){
	        $notes = $this->notes->forUserByPomo($request->user(), $request->pomo_id, $needPage = true);
	        $pomo = Pomo::where('id',$request->pomo_id)->where('user_id', $request->user()->id)->first();
	        if(empty($pomo)){
	        	echo 'system error,no pomo or not your pomo';exit;
	        }
	        $recommend_add_content = "#记录番茄#".$pomo->name."\n开始时间：".date('m月d日 H时i分',strtotime($pomo->created_at))."\n持续时长:20分钟\n";
    	} else if($request->has('article_id')){
	        $notes = $this->notes->forUserByArticle($request->user(), $request->article_id, $needPage = true);
	        $article = Article::where('id',$request->article_id)->first();
	        if(empty($article)){
	        	echo 'system error,no article';exit;
	        }
	        $recommend_add_content = "#记录文章#".$article->subject."\n时间：".date('m月d日 H时i分')."\n";
    	} else if($request->has('task_id')){
	        $notes = $this->notes->forUserByTask($request->user(), $request->task_id, $needPage = true);
	        $task = Task::where('id',$request->task_id)->where('user_id', $request->user()->id)->first();
	        if(empty($task)){
	        	echo 'system error,no task or not your task';exit;
	        }
	        $recommend_add_content = "#记录待办#".$task->name."\n开始时间：".date('m月d日 H时i分', strtotime('-30 minute'))."\n持续时长:20分钟\n";
    	} else {
	        $notes = $this->notes->forUserByStatus($request->user(), 2, $needPage = true);
    	}
        
        if ($request->has('add_content')) {
            if ($request->has('type') && $request->type = 'image') {
                $add_image = $request->add_content;
                $img_info = getimagesize($add_image);
                if (empty($img_info) && in_array($img_info['mime'], array(
                    'image/png',
                    'image/gif',
                    'image/jpeg'
                ))) {
                    echo '错误的图片类型';
                    exit();
                } else {
                    $add_content = '#分享图片#';
                }
            } else {
                $add_content = $request->add_content;
                if (\App\Http\Utils\CommonUtil::isUrl($add_content)) {
                    $title = \App\Http\Utils\CommonUtil::page_title($add_content);
                    $shortUrl = \App\Http\Utils\CommonUtil::shortUrl($add_content);
                    if(!empty($shortUrl)){
                        $add_content = $shortUrl;
                    }
                    $add_content = '#分享链接# ' . $add_content . ' ' . $title;
                }
                if (strpos($add_content, '#') === false) {
                    $add_content = '#分享# ' . $add_content;
                }
            }
        } else if(!empty($recommend_add_content)){
        	$add_content = $recommend_add_content;
        }
        
        foreach ($notes as $key => $note) {
            $commonUtil = new \App\Http\Utils\CommonUtil();
            $note->name = $commonUtil->auto_link_text($note->name);
            if (! empty($note->noteTagMaps)) {
                foreach ($note->noteTagMaps as $noteTagMap) {
                    $url = "/notes?tag_id=" . $noteTagMap->tag->id;
                    $tag_name = '#' . $noteTagMap->tag->name . '#';
                    
                    $note->name = str_replace($tag_name, "<a href='$url'  target='_blank'>" . $tag_name . "</a>", $note->name);
                    $notes[$key] = $note;
                }
            }
        }
        
        return view('notes.index', [
            'add_content' => $add_content,
            'add_image' => isset($add_image) ? $add_image : '',
            'notes' => $notes,
            'pomo_id' => $request->has('pomo_id') ? $request->pomo_id:'',
            'task_id' => $request->has('task_id') ? $request->task_id:'',
            'article_id' => $request->has('article_id') ? $request->article_id:''
        ]);
    }

    /**
     * Create a new note.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        
        if (empty($request->fname)) {
            $record_path = '';
        } else {
            $record_name = $request->user()->id . $request->fname . '.mp3';
            $temp_path = config("app.storage_path") . 'recorders/temp/' . $record_name;
            $real_path = config("app.storage_path") . 'recorders/' . $record_name;
            
            if (! file_exists($temp_path)) {
                $record_path = '';
            } else {
                rename($temp_path, $real_path);
                $record_path = 'recorders/' . $record_name;
            }
        }
        
        if ($request->has('add_image')) {
            $add_image = $request->add_image;
            $img_info = getimagesize($add_image);
            if (empty($img_info) && in_array($img_info['mime'], array(
                'image/png',
                'image/gif',
                'image/jpeg'
            ))) {
                echo '错误的图片类型';
                exit();
            }
        } else {
            $add_image = '';
        }
        
        $name = htmlspecialchars($request->name);
        $name = str_replace('&lt;code&gt;', '<code>', $name);
        $name = str_replace('&lt;/code&gt;', '</code>', $name);
        $name = nl2br($name);
        $note = $request->user()
            ->notes()
            ->create([
            'name' => $name,
            'article_id' => $request->article_id,
            'task_id' => $request->task_id,
            'pomo_id' => $request->pomo_id,
            'record_path' => $record_path,
            'image_path' => $add_image,
            'status' => $request->status
        ]);
        
        preg_match_all('/#(.*?)#/i', $request->name, $match);
        foreach ($match[0] as $item) {
            $tag_name = trim($item, '#');
            if (empty($tag_name)) {
                continue;
            }
            
            $tag = $this->tags->forTagName($tag_name);
            if (empty($tag)) {
                $tag = Tag::create(array(
                    'name' => $tag_name
                ));
            }
            
            $tagNote = new NoteTagMap();
            $tagNote->create(array(
                'tag_id' => $tag->id,
                'note_id' => $note->id
            ));
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/notes')->with('message', 'IT WORKS!');
        }
    }

    /**
     * Destroy the given task.
     *
     * @param Request $request
     * @param Note $note
     */
    public function destroy(Request $request, Note $note)
    {
        $this->authorize('destroy', $note);
        
        $note->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/notes')->with('message', 'IT WORKS!');
        }
    }

    public function upload(Request $request)
    {
        if ($_FILES["file"]["type"] == 'audio/mp3') {
            $record_name = $request->user()->id . $request->fname . '.mp3';
            move_uploaded_file($_FILES["file"]["tmp_name"], config("app.storage_path") . 'recorders/temp/' . $record_name);
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/notes')->with('message', 'IT WORKS!');
        }
    }

    public function getRecord(Request $request, Note $note)
    {
        if ($note->user_id == $request->user()->id || $note->status == 2) {
            header('Content-type: audio/mp3');
            readfile(config("app.storage_path") . $note->record_path);
        } else {
            echo 'error' . $request->user()->user_id;
            exit();
        }
    }
}
