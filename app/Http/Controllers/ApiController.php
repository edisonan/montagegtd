<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    /**
     * 获取用户列表
     */
    public function getUsers()
    {
        $users = \App\Models\User::select('id', 'name')->get();
        
        return response()->json($users);
    }
}