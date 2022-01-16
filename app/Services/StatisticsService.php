<?php

namespace App\Services;

use App\Models\Statistics;
use App\Exceptions\CustomException;
use App\Repositories\NoteRepository;
use App\Repositories\TaskRepository;
use App\Repositories\ArticleSubRepository;
use App\Repositories\PomoRepository;
use App\Repositories\MindRepository;

/**
 * 统计业务逻辑
 *
 * @author edison.an
 *        
 */
class StatisticsService {
	/**
	 *
	 * @var NoteRepository
	 */
	protected $noteRepository;
	/**
	 *
	 * @var TaskRepository
	 */
	protected $taskRepository;
	/**
	 *
	 * @var ArticleSubRepository
	 */
	protected $articleSubRepository;
	/**
	 *
	 * @var PomoRepository
	 */
	protected $pomoRepository;
	/**
	 *
	 * @var MindRepository
	 */
	protected $mindRepository;
	
	/**
	 *
	 * @param NoteRepository $noteRepository        	
	 * @param ArticleSubRepository $articleSubRepository        	
	 * @param TaskRepository $taskRepository        	
	 * @param PomoRepository $pomoRepository        	
	 * @param MindRepository $mindRepository        	
	 */
	public function __construct(NoteRepository $noteRepository, ArticleSubRepository $articleSubRepository, TaskRepository $taskRepository, PomoRepository $pomoRepository, MindRepository $mindRepository) {
		$this->noteRepository = $noteRepository;
		$this->articleSubRepository = $articleSubRepository;
		$this->taskRepository = $taskRepository;
		$this->pomoRepository = $pomoRepository;
		$this->mindRepository = $mindRepository;
	}
	
	/**
	 * 图标展示首页数据
	 *
	 * @return string[]|unknown[]
	 */
	public function getIndexInfo() {
		$userId = \Auth::id ();
		
		$days = 30;
		$startDate = date ( 'Y-m-d', strtotime ( "-$days days" ) );
		$endDate = date ( 'Y-m-d' );
		
		$basicInfos = array (
				'task' => '任务量',
				'pomo' => '番茄量',
				'note' => '笔记数',
				'article' => '阅读数',
				'mind' => '思维导图数' 
		);
		$detailFormatInfos = array ();
		$totalInfos = array ();
		
		$basicDetailInfos = array ();
		for($i = $days; $i >= 0; $i --) {
			$basicDetailInfos [date ( 'Y-m-d', strtotime ( "-$i days" ) )] = 0;
		}
		foreach ( $basicInfos as $type => $name ) {
			$statisticDatas = Statistics::where ( 'user_id', $userId )->where ( 'date_type', 'day' )->where ( 'data_type', $type )->where ( 'statistic_date', '>', $startDate )->where ( 'statistic_date', '<=', $endDate )->orderBy ( 'id', 'desc' )->get ();
			
			$sum = 0;
			$currentDetailInfos = $basicDetailInfos;
			foreach ( $statisticDatas as $statisticData ) {
				$currentDetailInfos [date ( 'Y-m-d', strtotime ( $statisticData->statistic_date ) )] = $statisticData->total;
				$sum = $sum + $statisticData->total;
			}
			$detailFormatInfos [$type] = $this->formatDetailInfos ( $currentDetailInfos, $name );
			$totalInfos [$type] = array (
					'value' => $sum,
					'name' => $name 
			);
		}
		
		$totalFormatInfos = $this->formatTotalInfos ( $totalInfos );
		
		return [ 
				'task_bar_statistics' => json_encode ( $detailFormatInfos ['task'] ),
				'pomo_bar_statistics' => json_encode ( $detailFormatInfos ['pomo'] ),
				'note_bar_statistics' => json_encode ( $detailFormatInfos ['note'] ),
				'article_bar_statistics' => json_encode ( $detailFormatInfos ['article'] ),
				'mind_bar_statistics' => json_encode ( $detailFormatInfos ['mind'] ),
				'count_pie_statistics' => json_encode ( $totalFormatInfos ),
				'start_date' => $startDate,
				'end_date' => $endDate 
		];
	}
	
	/**
	 * 格式化详情信息
	 *
	 * @param unknown $currentDetailInfos        	
	 * @param unknown $name        	
	 * @return boolean[][]|unknown[][]|string[][][]|unknown[][][]|NULL[][][]
	 */
	public function formatDetailInfos($currentDetailInfos, $name) {
		return array (
				'tooltip' => array (
						'show' => true 
				),
				'legend' => array (
						'data' => $name 
				),
				'xAxis' => array (
						array (
								'type' => 'category',
								'data' => array_keys ( $currentDetailInfos ) 
						) 
				),
				'yAxis' => array (
						array (
								'type' => 'value' 
						) 
				),
				'series' => array (
						array (
								'name' => $name,
								'type' => 'bar',
								'data' => array_values ( $currentDetailInfos ) 
						) 
				) 
		);
	}
	
	/**
	 * 格式化总数信息
	 *
	 * @param unknown $totalInfos        	
	 * @return boolean[]|string[][]|string[][][]|string[][][][]|unknown
	 */
	public function formatTotalInfos($totalInfos) {
		$formatTotalInfos = array (
				'tooltip' => array (
						'trigger' => 'item',
						'formatter' => '{a} <br/>{b} : {c} ({d}%)' 
				),
				'legend' => array (
						'orient' => 'vertical',
						'x' => 'left',
						'data' => array () 
				),
				'calculable' => true,
				'series' => array (
						array (
								'name' => '数量汇总',
								'type' => 'pie',
								'radius' => '55%',
								'center' => array (
										'50%',
										'60%' 
								),
								'data' => array_values ( $totalInfos ) 
						) 
				) 
		);
		
		foreach ( $totalInfos as $totalInfo ) {
			$formatTotalInfos ['legend'] ['data'] [] = $totalInfo ['name'];
		}
		
		return $formatTotalInfos;
	}
	
	/**
	 * 根据类型获取每日汇总数量信息
	 *
	 * @param unknown $dataType        	
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 * @throws CustomException
	 */
	private function getDailyStatisticCountInfo($dataType, $startTime, $endTime) {
		$counts = array ();
		switch ($dataType) {
			case 'note' :
				$counts = $this->noteRepository->getStatisticCounts ( $startTime, $endTime );
				break;
			case 'task' :
				$counts = $this->taskRepository->getStatisticCounts ( $startTime, $endTime );
				break;
			case 'pomo' :
				$counts = $this->pomoRepository->getStatisticCounts ( $startTime, $endTime );
				break;
			case 'article' :
				$counts = $this->articleSubRepository->getStatisticCounts ( $startTime, $endTime );
				break;
			case 'mind' :
				$counts = $this->mindRepository->getStatisticCounts ( $startTime, $endTime );
				break;
			default :
				throw new CustomException ( "unknow data type" );
		}
		return $counts;
	}
	
	/**
	 * 每日汇总统计
	 *
	 * @param unknown $startTime        	
	 * @param unknown $endTime        	
	 */
	public function dailyStatistic($startTime, $endTime) {
		$dataTypes = array (
				'note',
				'task',
				'pomo',
				'article',
				'mind' 
		);
		foreach ( $dataTypes as $dataType ) {
			$counts = $this->getDailyStatisticCountInfo ( $dataType, $startTime, $endTime );
			
			foreach ( $counts as $count ) {
				$paramArr = [ 
						'user_id' => $count->user_id,
						'data_type' => $dataType,
						'date_type' => 'day',
						'statistic_date' => $startTime 
				];
				
				$statistics = Statistics::where ( $paramArr )->first ();
				$paramArr ['total'] = $count->total;
				
				if (empty ( $statistics )) {
					$statistics = new Statistics ();
					$statistics->create ( $paramArr );
				} else {
					$statistics->update ( $paramArr );
				}
			}
		}
	}
}
