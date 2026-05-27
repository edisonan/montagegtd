<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class StudyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('study.index');
    }

    public function focus(Request $request, Task $task)
    {
        $this->authorize('destroy', $task);
        if ((int)$task->mode !== 3) {
            abort(404);
        }

        return view('study.focus', array(
            'task' => $task,
        ));
    }

    public function checkins(Request $request)
    {
        return view('study.checkins');
    }
}
