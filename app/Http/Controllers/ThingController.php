<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thing;
use App\Repositories\ThingRepository;

class ThingController extends Controller
{

    /**
     * The thing repository instance.
     *
     * @var ThingRepository
     */
    protected $things;

    /**
     * Create a new controller instance.
     *
     * @param ThingRepository $things
     * @return void
     */
    public function __construct(ThingRepository $things)
    {
        $this->middleware('auth');
        
        $this->things = $things;
    }

    /**
     * Display a list of all of the user's thing.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return view('things.index', [
            'things' => $this->things->forUser($request->user(), $needPage = true)
        ]);
    }

    /**
     * Create a new thing.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'start_time' => 'date_format:Y-m-d H:i:s',
            'end_time' => 'date_format:Y-m-d H:i:s'
        ]);
        
        $params = array();
        $params['name'] = $request->name;
        
        if ($request->has('start_time')) {
            $params['start_time'] = $request->start_time;
        } else {
            $params['start_time'] = date('Y-m-d H:i:s');
        }
        
        if ($request->has('end_time')) {
            $params['end_time'] = $request->end_time;
            if (strtotime($params['start_time']) > strtotime($params['end_time'])) {
                return redirect('/things')->with('message', 'Error End Time:' . $params['end_time']);
            }
        }
        
        $thing = $request->user()
            ->things()
            ->create($params);
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/things')->with('message', 'IT WORKS!');
        }
    }

    /**
     * Destroy the given thing.
     *
     * @param Request $request
     * @param Thing $thing
     */
    public function destroy(Request $request, Thing $thing)
    {
        $this->authorize('destroy', $thing);
        
        $thing->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/things')->with('message', 'IT WORKS!');
        }
    }

    public function update(Request $request, Thing $thing)
    {
        $this->authorize('destroy', $thing);
        
        $flag = $thing->update($request->all());
        
        if ($request->ajax() || $request->wantsJson()) {
            $resp = $this->responseJson(self::OK_CODE);
            return response($resp);
        } else {
            return redirect('/things')->with('message', 'IT WORKS!');
        }
    }
}
