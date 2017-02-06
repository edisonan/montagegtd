<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Note;
use App\Repositories\NoteRepository;

class NoteController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $notes;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(NoteRepository $notes)
    {
        $this->middleware('auth');

        $this->notes = $notes;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request,$add_content = '')
    {
        return view('notes.index', [
            'add_content' => $add_content,
            'notes' => $this->notes->forUserByStatus($request->user(), 2),
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

        $request->user()->notes()->create([
            'name' => $request->name,
            'status' => $request->status,
        ]);
        print_r([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect('/notes');
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

        return redirect('/notes');
    }
}
