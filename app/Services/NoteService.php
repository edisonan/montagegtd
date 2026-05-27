<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteTagMap;
use App\Exceptions\CustomException;
use App\Http\Utils\CommonUtil;
use Illuminate\Support\Facades\DB;
use App\Repositories\ArticleRepository;
use App\Repositories\FocusRepository;
use Auth;
use App\Repositories\TaskRepository;
use App\Repositories\JournalRepository;
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
     * The journal repository instance.
     *
     * @var JournalRepository
     */
    protected $journalRepository;
	
	/**
	 * The article Repository instance.
	 *
	 * @var ArticleRepository
	 */
	protected $articleRepository;
	
	/**
	 * The focus repository instance.
	 *
	 * @var FocusRepository
	 */
	protected $focusRepository;
	
	/**
	 * The tag service instance.
	 *
	 * @var TagService
	 */
	protected $tagService;

    const NOTE_SOURCE_TYPE_POMO = 1;
    const NOTE_SOURCE_TYPE_ARTICLE = 2;
    const NOTE_SOURCE_TYPE_TASK = 3;
    const NOTE_SOURCE_TYPE_THING = 4;

	/**
	 *
	 * @param NoteRepository $noteRepository        	
	 * @param TaskRepository $taskRepository        	
	 * @param JournalRepository $journalRepository
	 * @param ArticleService $articleRepository
	 * @param FocusRepository $focusRepository        	
	 * @param TagService $tagService        	
	 */
	public function __construct(NoteRepository $noteRepository, TaskRepository $taskRepository, JournalRepository $journalRepository, ArticleRepository $articleRepository, FocusRepository $focusRepository, TagService $tagService) {
		$this->noteRepository = $noteRepository;
		$this->taskRepository = $taskRepository;
		$this->journalRepository = $journalRepository;
		$this->articleRepository = $articleRepository;
		$this->focusRepository = $focusRepository;
		$this->tagService = $tagService;
	}
	
	/**
	 * 获取首页信息
	 * 
	 * @param unknown $addContent        	
	 * @param string $type        	
	 * @param number $tagId        	
	 * @param string $keyword        	
	 * @param number $focusId        	
	 * @param number $articleId        	
	 * @param number $taskId        	
	 * @return string[]|unknown[]|number[]
	 */
	public function getIndexInfo($addContent, $type = '', $tagId = 0, $keyword = '', $sourceType = 0, $sourceId =0) {
		$formatAddContent = $this->getFormatContent ( $addContent, $type , $sourceType, $sourceId );
		
		$notes = $this->noteRepository->getUserList ( Auth::id (), $tagId, $keyword, $sourceType, $sourceId);
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
				'source_type' => $sourceType,
				'source_id' => $sourceId,
		];
	}
	
	/**
	 * 格式化返回的文本信息
	 * 
	 * @param unknown $addContent        	
	 * @param unknown $type        	
	 * @param unknown $focusId        	
	 * @param unknown $articleId        	
	 * @param unknown $taskId        	
	 * @throws CustomException
	 * @return string|string|unknown
	 */
	public function getFormatContent($addContent, $type, $sourceType = 0, $sourceId =0) {
		$formatContent = $addContent;
		if ($sourceType == self::NOTE_SOURCE_TYPE_POMO && ! empty ( $sourceId )) {
			$focus = $this->focusRepository->getFocusById ( $sourceId );
			if (empty ( $focus ) || $focus->user_id != Auth::id ()) {
				throw new CustomException ( "系统异常，无此专注!" );
			}
			$formatContent = "[[记录专注]] " . $focus->name . "\n开始时间：" . date ( 'm月d日 H时i分', strtotime ( $focus->created_at ) ) . "\n持续时长:20分钟\n";
			return $formatContent;
		}
		
		if ($sourceType == self::NOTE_SOURCE_TYPE_ARTICLE && ! empty ( $sourceId )) {
			$article = $this->articleRepository->getArticleById ( $sourceId );
			if (empty ( $article )) {
				throw new CustomException ( "系统异常，无此文章!" );
			}
			$formatContent = "[[记录文章]] " . $article->subject . "\n时间：" . date ( 'm月d日 H时i分' ) . "\n地址: " . config('app.url').'/article/'. $article->id . "\n原文地址：". $article->url . "\n";
			return $formatContent;
		}
		
		if ($sourceType == self::NOTE_SOURCE_TYPE_TASK && ! empty ( $sourceId )) {
			$task = $this->taskRepository->getTaskById ( $sourceId );
			if (empty ( $task ) || $task->user_id != Auth::id ()) {
				throw new CustomException ( "系统异常，无此待办!" );
			}
			$parentTaskName = isset ( $task->parentTask->name ) ? "#" . $task->parentTask->name . "#" : "";
			$modeName = $task->mode == 2 ? "#life#" : "#work#";
			$formatContent = "[[记录待办]] " . $modeName . $parentTaskName . $task->name . "\n开始时间：" . date ( 'm月d日 H时i分', strtotime ( '-20 minute' ) ) . "\n持续时长:20分钟\n";
			return $formatContent;
		}

        if ($sourceType == self::NOTE_SOURCE_TYPE_THING && ! empty ( $sourceId )) {
            $journal = $this->journalRepository->getJournalById ( $sourceId );
            if (empty ( $journal ) || $journal->user_id != Auth::id ()) {
                throw new CustomException ( "系统异常，无此手账!" );
            }

            $formatContent = "[[记录手账]] "  . $journal->name ;
            if(!empty($journal->start_time) && !empty($journal->end_time)) {
                $duration = round((strtotime($journal->end_time) - strtotime($journal->start_time))/60);
                $formatContent = $formatContent . "\n时间：" . date ( 'm月d日 H时i分', strtotime($journal->start_time) ) . '-' . date ( 'm月d日 H时i分', strtotime($journal->end_time) ). "\n持续时长:". $duration . "分钟\n";
            }

            return $formatContent;
        }
		
		if (! empty ( $addContent )) {
			if ($type == 'image') {
				$this->validateImage ( $addContent );
				$formatContent = '[[分享图片]]';
			} else {
				if (CommonUtil::isUrl ( $addContent )) {
					$title = CommonUtil::pageTitle ( $addContent );
					$shortUrl = CommonUtil::shortUrl ( $addContent );
					if (! empty ( $shortUrl )) {
						$addContent = $shortUrl;
					}
					$formatContent = '[[分享链接]] ' . $addContent . ' ' . $title."\n";
				} else if (strpos ( $addContent, '#' ) === false) {
					$formatContent = "[[分享]]" . $addContent."\n";
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
	 * @param unknown $focusId        	
	 */
	public function store($name, $status, $addImage, $fname, $sourceType, $sourceId) {
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
		$note->source_type = $sourceType;
		$note->source_id = $sourceId;
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

		return $note;
	}
	public function update($note, $name, $status) {
	    $updateParams = array(); 
	    $formatName = $this->formatName ( $name );
	    if($note->name != $formatName) {
	        $note->audit_status = 0;
		$note->name = $formatName;
	    }
	    $note->status = $status;
	    $note->update ();
	    
	    preg_match_all ( '/#(.*?)#/i', $name, $match );
	    foreach ( $match [0] as $item ) {
	        $tagName = trim ( $item, '#' );
	        if (empty ( $tagName )) {
	            continue;
	        }
	        
	        $tag = $this->tagService->getByTagName ( $tagName, true );
	        
	        $tagNote = NoteTagMap::where('tag_id', $tag->id)->where('note_id', $note->id)->first();
	        if(empty($tagNote)) {
    	        $tagNote = new NoteTagMap ();
    	        $tagNote->create ( array (
    	            'tag_id' => $tag->id,
    	            'note_id' => $note->id
    	        ) );
	        }
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
