<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteTagMap;
use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use Illuminate\Support\Facades\DB;
use App\Repositories\ArticleRepository;
use App\Repositories\PomoRepository;
use Auth;
use App\Repositories\TaskRepository;
use App\Repositories\NoteRepository;

/**
 * 笔记业务逻辑
 *
 * @author edison.an
 *        
 */
class NoteService {
	
	/**
	 * The note repository instance.
	 *
	 * @var NoteRepository
	 */
	protected $noteRepository;
	
	/**
	 * The task repository instance.
	 *
	 * @var TaskRepository
	 */
	protected $taskRepository;
	
	/**
	 * The article Repository instance.
	 *
	 * @var ArticleRepository
	 */
	protected $articleRepository;
	
	/**
	 * The pomo repository instance.
	 *
	 * @var PomoRepository
	 */
	protected $pomoRepository;
	
	/**
	 * The tag service instance.
	 *
	 * @var TagService
	 */
	protected $tagService;
	
	/**
	 *
	 * @param NoteRepository $noteRepository        	
	 * @param TaskRepository $taskRepository        	
	 * @param ArticleService $articleRepository        	
	 * @param PomoRepository $pomoRepository        	
	 * @param TagService $tagService        	
	 */
	public function __construct(NoteRepository $noteRepository, TaskRepository $taskRepository, ArticleService $articleRepository, PomoRepository $pomoRepository, TagService $tagService) {
		$this->noteRepository = $noteRepository;
		$this->taskRepository = $taskRepository;
		$this->articleRepository = $articleRepository;
		$this->pomoRepository = $pomoRepository;
		$this->tagService = $tagService;
	}
	
	/**
	 * 获取首页信息
	 * 
	 * @param unknown $addContent        	
	 * @param string $type        	
	 * @param number $tagId        	
	 * @param string $keyword        	
	 * @param number $pomoId        	
	 * @param number $articleId        	
	 * @param number $taskId        	
	 * @return string[]|unknown[]|number[]
	 */
	public function getIndexInfo($addContent, $type = '', $tagId = 0, $keyword = '', $pomoId = 0, $articleId = 0, $taskId = 0) {
		$formatAddContent = $this->getFormatContent ( $addContent, $type, $pomoId, $articleId, $taskId );
		
		$notes = $this->noteRepository->getUserList ( Auth::id (), $tagId, $keyword, $pomoId, $articleId, $taskId );
		foreach ( $notes as $key => $note ) {
			foreach ( $note->noteTagMaps as $noteTagMap ) {
				$url = "/notes?tag_id=" . $noteTagMap->tag->id;
				$tagName = '#' . $noteTagMap->tag->name . '#';
				
				$note->name = str_replace ( $tagName, "<a href='$url'  target='_blank'>" . $tagName . "</a>", $note->name );
				$notes [$key] = $note;
			}
		}
		
		return [ 
				'add_content' => $formatAddContent,
				'add_image' => $type == 'image' ? $addContent : '',
				'notes' => $notes,
				'pomo_id' => ! empty ( $pomoId ) ? $pomoId : '',
				'task_id' => ! empty ( $taskId ) ? $taskId : '',
				'article_id' => ! empty ( $articleId ) ? $articleId : '' 
		];
	}
	
	/**
	 * 格式化返回的文本信息
	 * 
	 * @param unknown $addContent        	
	 * @param unknown $type        	
	 * @param unknown $pomoId        	
	 * @param unknown $articleId        	
	 * @param unknown $taskId        	
	 * @throws CustomException
	 * @return string|string|unknown
	 */
	public function getFormatContent($addContent, $type, $pomoId, $articleId, $taskId) {
		$formatContent = $addContent;
		if (! empty ( $pomoId )) {
			$pomo = $this->pomoRepository->getPomoById ( $pomoId );
			if (empty ( $pomo ) || $pomo->user_id != Auth::id ()) {
				throw new CustomException ( "系统异常，无此番茄!" );
			}
			$formatContent = "#记录番茄#" . $pomo->name . "\n开始时间：" . date ( 'm月d日 H时i分', strtotime ( $pomo->created_at ) ) . "\n持续时长:20分钟\n";
			return $formatContent;
		}
		
		if (! empty ( $articleId )) {
			$article = $this->articleRepository->getArticleById ( $articleId );
			if (empty ( $article )) {
				throw new CustomException ( "系统异常，无此文章!" );
			}
			$formatContent = "#记录文章#" . $article->subject . "\n时间：" . date ( 'm月d日 H时i分' ) . "\n";
			return $formatContent;
		}
		
		if (! empty ( $taskId )) {
			$task = $this->taskRepository->getTaskById ( $taskId );
			if (empty ( $task ) || $task->user_id != Auth::id ()) {
				throw new CustomException ( "系统异常，无此文章!" );
			}
			$parentTaskName = isset ( $task->parentTask->name ) ? "#" . $task->parentTask->name . "#" : "";
			$modeName = $task->mode == 2 ? "#life#" : "#work#";
			$formatContent = "#记录待办#" . $modeName . $parentTaskName . $task->name . "\n开始时间：" . date ( 'm月d日 H时i分', strtotime ( '-20 minute' ) ) . "\n持续时长:20分钟\n";
			return $formatContent;
		}
		
		if (! empty ( $addContent )) {
			if ($type == 'image') {
				$this->validateImage ( $addContent );
				$formatContent = '#分享图片#';
			} else {
				if (CommonUtil::isUrl ( $addContent )) {
					$title = CommonUtil::page_title ( $addContent );
					$shortUrl = CommonUtil::shortUrl ( $addContent );
					if (! empty ( $shortUrl )) {
						$addContent = $shortUrl;
					}
					$formatContent = '#分享链接# ' . $addContent . ' ' . $title;
				} else if (strpos ( $addContent, '#' ) === false) {
					$formatContent = "#分享#" . $addContent;
				}
			}
		}
		
		return $formatContent;
	}
	
	/**
	 * 保存笔记
	 * 
	 * @param unknown $name        	
	 * @param unknown $status        	
	 * @param unknown $addImage        	
	 * @param unknown $fname        	
	 * @param unknown $taskId        	
	 * @param unknown $articleId        	
	 * @param unknown $pomoId        	
	 */
	public function store($name, $status, $addImage, $fname, $taskId, $articleId, $pomoId) {
		$note = new Note ();
		if (! empty ( $fname )) {
			$note->record_path = $this->storeRecord ( $fname );
		} else {
			$note->record_path = '';
		}
		
		if (! empty ( $addImage )) {
			$this->validateImage ( $addImage );
			$note->image_path = $addImage;
		} else {
			$note->image_path = '';
		}
		
		$note->name = $this->formatName ( $name );
		$note->status = $status;
		$note->article_id = $articleId;
		$note->task_id = $taskId;
		$note->pomo_id = $pomoId;
		$note->user_id = \Auth::id ();
		$note->save ();
		
		preg_match_all ( '/#(.*?)#/i', $name, $match );
		foreach ( $match [0] as $item ) {
			$tagName = trim ( $item, '#' );
			if (empty ( $tagName )) {
				continue;
			}
			
			$tag = $this->tagService->getByTagName ( $tagName, true );
			
			$tagNote = new NoteTagMap ();
			$tagNote->create ( array (
					'tag_id' => $tag->id,
					'note_id' => $note->id 
			) );
		}
	}
	
	/**
	 * 格式化保存的内容
	 * 
	 * @param unknown $name        	
	 * @return string
	 */
	private function formatName($name) {
		$name = htmlspecialchars ( $name );
		$name = str_replace ( '&lt;code&gt;', '<code>', $name );
		$name = str_replace ( '&lt;/code&gt;', '</code>', $name );
		$name = nl2br ( $name );
		return $name;
	}
	
	/**
	 * 保存语音
	 * 
	 * @param unknown $fname        	
	 * @return string
	 */
	private function storeRecord($fname) {
		$recordName = \Auth::id () . $fname . '.mp3';
		$tempPath = config ( "app.storage_path" ) . 'recorders/temp/' . $recordName;
		$realPath = config ( "app.storage_path" ) . 'recorders/' . $recordName;
		
		if (! file_exists ( $tempPath )) {
			$recordPath = '';
		} else {
			rename ( $tempPath, $realPath );
			$recordPath = 'recorders/' . $recordName;
		}
		return $recordPath;
	}
	
	/**
	 * 校验图片类型
	 * 
	 * @param unknown $addImage        	
	 * @throws CustomException
	 */
	private function validateImage($addImage) {
		$imgInfo = getimagesize ( $addImage );
		if (empty ( $imgInfo ) || ! in_array ( $imgInfo ['mime'], array (
				'image/png',
				'image/gif',
				'image/jpeg' 
		) )) {
			throw new CustomException ( "错误的图片类型" );
		}
	}
}
