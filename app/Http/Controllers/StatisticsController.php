<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\StatisticsRepository;
use function GuzzleHttp\json_encode;

class StatisticsController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $statistics;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(StatisticsRepository $statistics)
    {
        $this->middleware('auth');

        $this->statistics = $statistics;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request,$add_content = '')
    {
    	$bar_basic_arr = array(
    		'tooltip'=>array(
    			'show'=>true
    		),
    		'legend'=>array(
    			'data'=>'销量'
    		),
    		'xAxis'=>array(
//     			array(
//     				'type'=>'category',
//     				'data'=>array()
//     			)
    		),
    		'yAxis'=>array(
//     			array(
//     				'type'=>'value',
//     			)
    		),
    		'series'=>array(
//     			array(
//     				'name'=>'数量',
//     				'type'=>'bar',
//     				'data'=>array()
//     			)
    		)
    	);
    	
    	$days = 30;
    	
    	$start_date = date('Y-m-d');
    	$end_date = date('Y-m-d',strtotime("-30 days"));
    	
    	$basic_arr = array();
    	for($i=0;$i<$days;$i++){
    		$basic_arr[date('Y-m-d',strtotime("-$i days"))] = 0;
    	}
    	
    	$task_arr = $pomo_arr = $note_arr = $basic_arr;
    	
    	$task_statistics = $this->statistics->forUserSpecial($request->user(), 'day', 'task', $start_date, $end_date);
    	$pomo_statistics = $this->statistics->forUserSpecial($request->user(), 'day', 'pomo', $start_date, $end_date);
    	$note_statistics = $this->statistics->forUserSpecial($request->user(), 'day', 'note', $start_date, $end_date);
    	
    	$count_arr = array('task_count'=>0,'pomo_count'=>0,'note_count'=>0);
    	
    	foreach ($task_statistics as $statistic){
    		$task_arr[date('Y-m-d',strtotime($statistic->statistic_date))] = $statistic->count;
    		$count_arr['task_count'] = $count_arr['task_count'] + $statistic->count;
    	}
    	$task_bar_statistics = $bar_basic_arr;
    	$task_bar_statistics['legend']['data'] = '任务量';
    	$task_bar_statistics['xAxis'][] = array(
    			'type'=>'category',
    			'data'=>array_keys($task_arr),
    	);
    	$task_bar_statistics['yAxis'][] = array(
    			'type'=>'value',
    	);
    	$task_bar_statistics['series'][] = array(
    			'name'=>'任务量',
    			'type'=>'bar',
    			'data'=>array_values($task_arr),
    	);
    	
    	foreach ($pomo_statistics as $statistic){
    		$pomo_arr[date('Y-m-d',strtotime($statistic->statistic_date))] = $statistic->count;
    		$count_arr['pomo_count'] = $count_arr['pomo_count'] + $statistic->count;
    	}
    	$pomo_bar_statistics = $bar_basic_arr;
    	$pomo_bar_statistics['legend']['data'] = '任务量';
    	$pomo_bar_statistics['xAxis'][] = array(
    			'type'=>'category',
    			'data'=>array_keys($pomo_arr),
    	);
    	$pomo_bar_statistics['yAxis'][] = array(
    			'type'=>'value',
    	);
    	$pomo_bar_statistics['series'][] = array(
    			'name'=>'任务量',
    			'type'=>'bar',
    			'data'=>array_values($pomo_arr),
    	);
    	
    	foreach ($note_statistics as $statistic){
    		$note_arr[date('Y-m-d',strtotime($statistic->statistic_date))] = $statistic->count;
    		$count_arr['note_count'] = $count_arr['note_count'] + $statistic->count;
    	}
    	
    	$note_bar_statistics = $bar_basic_arr;
    	$note_bar_statistics['legend']['data'] = '任务量';
    	$note_bar_statistics['xAxis'][] = array(
    			'type'=>'category',
    			'data'=>array_keys($note_arr),
    	);
    	$note_bar_statistics['yAxis'][] = array(
    			'type'=>'value',
    	);
    	$note_bar_statistics['series'][] = array(
    			'name'=>'任务量',
    			'type'=>'bar',
    			'data'=>array_values($note_arr),
    	);
    	
    	$count_pie_statistics = array();
    	
        return view('statistics.index', [
            'task_bar_statistics' => \json_encode($task_bar_statistics),
        	'pomo_bar_statistics' => \json_encode($pomo_bar_statistics),
        	'note_bar_statistics' => \json_encode($note_bar_statistics),
        	'count_pie_statistics' => \json_encode($count_pie_statistics),
        ]);
    }
    
}
