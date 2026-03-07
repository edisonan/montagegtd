<?php

namespace App\Services;

use App\Models\Mind;
use App\Models\MindTagMap;
use App\Exceptions\CustomException;
use App\Repositories\MindRepository;
use Auth;

/**
 * 思维导图业务逻辑
 *
 * @author edison.an
 *        
 */
class MindService {
	/**
	 *
	 * @var MindRepository
	 */
	protected $mindRepository;
	
	/**
     *
     * @var TagService
     */
    protected $tagService;
	
	/**
	 *
	 * @param MindRepository $mindRepository
	 * @param TagService $tagService
	 */
	public function __construct(MindRepository $mindRepository, TagService $tagService) {
		$this->mindRepository = $mindRepository;
		$this->tagService = $tagService;
	}
	
	
	/**
	 * 获取首页数据
	 */
	public function getIndexList($tagId, $name) {
		return $this->mindRepository->getUserRootMindList ( Auth::id () , $tagId, $name);
	}
	
	/**
	 * 保存思维导图节点
	 * 
	 * @param unknown $name        	
	 * @param unknown $parentMindId        	
	 * @throws CustomException
	 * @return \App\Models\Mind
	 */
	public function store($name, $parentMindId, $content = null, $sourceType = null, $sourceId = null) {
		$isRoot = 1;
		
		if (! empty ( $parentMindId )) {
			$parentMind = $this->mindRepository->getUserMindById ( Auth::id (), $parentMindId );
			if (empty ( $parentMind )) {
				throw new CustomException ( "父节点信息上送错误" );
			}
			$isRoot = 0;
		}
		
		$mind = new Mind ();
		$mind->name = htmlspecialchars ( $name );
		if (!empty($content)) {
			$mind->content = str_replace ( array ( "\r\n", "\r", "\n" ), "\\r\\n", $content );
		}
		$mind->parent_mind_id = $parentMindId;
		$mind->is_root = $isRoot;
		$mind->user_id = \Auth::id ();
		if (!empty($sourceType)) {
			$mind->source_type = $sourceType;
		}
		if (!empty($sourceId)) {
			$mind->source_id = intval($sourceId);
		}
		$mind->save ();
		
		return $mind;
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
		$mind->status = 2;
		$mind->save ();
		return true;
	}
	
	/**
	 * 获取格式化后的jsmind完整思维导图数据
	 * 
	 * @param unknown $mind        	
	 * @return string[]|\App\Services\mixed[][]|\App\Services\NULL[][]|string[][]|NULL[][]
	 */
	public function getJsMindFormatInfo($mind) {
		$jsmindDatas = array ();
		$jsmindDatas ['meta'] = array (
				'name' => $mind->name,
				'author' => $mind->name,
				'version' => "1.0" 
		);
		$jsmindDatas ['format'] = 'node_tree';
		$jsmindDatas ['data'] = $this->getNodeTreeData ( $mind );
		return $jsmindDatas;
	}
	
	/**
	 * 获取递归展示用数据
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
		$data ['content'] = str_replace ( "\\r\\n", "\r\n", $data ['content'] );
		if (count ( $mind->childrenMinds ) > 0) {
			foreach ( $mind->childrenMinds as $childMind ) {
				if ($childMind->status == 1) {
					$data ['children'] [] = $this->getNodeTreeData ( $childMind, $level + 1 );
				}
			}
		}
		return $data;
	}
	
	/**
	 * 获取markdown版思维导图完整数据
	 * 
	 * @param unknown $mind        	
	 * @param number $level        	
	 * @return string
	 */
	public function getNodeTreeMarkDownData($mind, $level = 0) {
		$name = $mind->name;
		$content = $mind->content;
		
		$prefix = "";
		for($i = 0; $i < $level; $i ++) {
			$prefix .= "	";
		}
		
		$data = $prefix . "* **" . $name . "**\n";
		if (false && ! empty ( $content )) {
			$data .= $prefix . "\n";
			$data .= $prefix . "```\n";
			$data .= $prefix . " " . str_replace ( "\\r\\n", "", str_replace ( "```", "", $content ) ) . "\n";
			$data .= $prefix . "```\n";
		}
		
		if (count ( $mind->childrenMinds ) > 0) {
			foreach ( $mind->childrenMinds as $childMind ) {
				if ($childMind->status == 1) {
					$data .= $this->getNodeTreeMarkdownData ( $childMind, $level + 1 );
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * 获取html版思维导图完整数据
	 * 
	 * @param unknown $mind        	
	 * @param number $level        	
	 * @return string
	 */
	public function getNodeTreeHtmlData($mind, $level = 0) {
		$name = $mind->name;
		$content = $mind->content;
		
		$prefix = "";
		for($i = 0; $i < $level; $i ++) {
			$prefix .= "	";
		}
		
		$data = $prefix . "<li> " . $name . "</li>";
		if (true && ! empty ( $content )) {
			$data .= $prefix . "<code>";
			$data .= $prefix . " " . str_replace ( "\\r\\n", "<br>", $content ) . "\n";
			$data .= $prefix . "</code>";
		}
		
		if (count ( $mind->childrenMinds ) > 0) {
			$data .= "<ul>";
			foreach ( $mind->childrenMinds as $childMind ) {
				if ($childMind->status == 1) {
					$data .= $this->getNodeTreeHtmlData ( $childMind, $level + 1 );
				}
			}
			$data .= "</ul>";
		}
		
		return $data;
	}
	
	/**
     * @param Mind $mind
     * @param $tagName
     * @return mixed
     */
    public function addTag(Mind $mind, $tagName)
    {
        $tag = $this->tagService->getByTagName ( $tagName, true );

        $tagNote = new MindTagMap ();
        $tagNote->create ( array (
            'tag_id' => $tag->id,
            'mind_id' => $mind->id
        ) );

        return $tag;
    }
}
