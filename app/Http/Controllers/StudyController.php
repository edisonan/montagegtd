<?php

namespace App\Http\Controllers;

use App\Models\StudyCheckin;
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

    /**
     * 对外提供学习打卡媒体文件（仅限归属本人的打卡记录）
     */
    public function media(Request $request, $path)
    {
        $path = (string)$path;
        if (!preg_match('#^study_checkins/[0-9]{8}/[0-9A-Za-z_.\-]+$#', $path)) {
            abort(404);
        }

        $checkin = StudyCheckin::where('user_id', auth()->id())
            ->where(function ($q) use ($path) {
                $q->where('audio_path', $path)
                    ->orWhere('image_path', $path)
                    ->orWhere('video_path', $path);
            })
            ->first();
        if (!$checkin) {
            abort(403);
        }

        $fullPath = rtrim((string)config('app.storage_path'), '/') . '/' . $path;
        if (!is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
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

    /**
     * 学习工具卡片列表页
     */
    public function tools(Request $request)
    {
        return view('study.tools', array(
            'tools' => (array)config('study_tools.tools', array()),
        ));
    }

    /**
     * 远程辅导工具页（WebRTC 音视频 + 内容 + 同步白板）
     */
    public function tutoring(Request $request)
    {
        return view('study.tools.tutoring');
    }
}
