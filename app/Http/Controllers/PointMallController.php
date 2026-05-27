<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PointMallController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('points.mall');
    }

    public function tree(Request $request)
    {
        return view('points.tree');
    }

    public function lottery(Request $request)
    {
        return view('points.lottery');
    }

    public function bus(Request $request)
    {
        return view('points.bus');
    }

    public function pet(Request $request)
    {
        return view('points.pet');
    }

    public function pond(Request $request)
    {
        return view('points.pond');
    }
}
