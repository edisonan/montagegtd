<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\StatisticsRepository;

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
    public function __construct(StatisticsRepository $notes)
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
    	$start_date = date('Y-m-d');
    	$end_date = date('Y-m-d',strtotime("-7 days"));
    	$statistics = $this->statistics->forUserSpecial($request->user(), 'day', 'task', $start_date, $end_date);
    	
        return view('statistics.index', [
            'statistics' => $statistics,
        ]);
    }
    
}
