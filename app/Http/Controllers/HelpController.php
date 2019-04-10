<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Repositories\TaskRepository;
use App\Repositories\PomoRepository;
use App\Repositories\GoalRepository;

class HelpController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @param TaskRepository $tasks
     * @return void
     */
    public function __construct(TaskRepository $tasks, PomoRepository $pomos, GoalRepository $goals)
    {
        $this->middleware('auth');
    }

    public function feedback(Request $request)
    {
        return view('help.feedback', [
            'from' => $request->has('from') ? $request->from : ''
        ]);
    }

    public function feedbackStore(Request $request)
    {
        $this->validate($request, [
            'content' => 'required'
        ]);
        
        $feedback = new Feedback();
        $feedback->user_id = isset($request->user()->id)?$request->user()->id:null;
        $feedback->from = $request->from;
        $feedback->content = $request->content;
        $feedback->save();
        
        if ($request->ajax() || $request->wantsJson() || $request->has('json_wants')) {
            $resp = $this->responseJson(self::OK_CODE, array());
            return response($resp);
        } else {
            return redirect('/help/feedback')->with('message', 'IT WORKS!');
        }
    }
}
