<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PointAccountService;
use App\Services\PointRecordService;

class PointController extends Controller
{
    protected $pointAccountService;
    protected $pointRecordService;

    public function __construct(
        PointAccountService $pointAccountService,
        PointRecordService $pointRecordService
    ) {
        $this->middleware('auth');
        $this->pointAccountService = $pointAccountService;
        $this->pointRecordService = $pointRecordService;
    }

    public function index(Request $request)
    {
        return view('points.index');
    }
}
