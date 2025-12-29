<?php

namespace App\Http\Controllers;

use App\Models\PublicDiscussion;
use App\Models\DiscussionReply;
use App\Services\CourseService;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;

class DiscussionController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->middleware('auth');
        $this->courseService = $courseService;
    }

    /**
     * 显示课程讨论列表
     */
    public function index(Request $request, $courseId)
    {
        $discussions = PublicDiscussion::where('course_id', $courseId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'discussions' => $discussions
        ]));
    }

    /**
     * 创建讨论
     */
    public function store(Request $request, $courseId)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'content' => 'required',
            'type' => 'in:question,note,resource,tip',
            'course_item_id' => 'nullable|integer'
        ]);

        $discussion = PublicDiscussion::create([
            'course_id' => $courseId,
            'course_item_id' => $request->input('course_item_id'),
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'type' => $request->input('type', 'note')
        ]);
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'discussion' => $discussion
        ]));
    }

    /**
     * 显示讨论详情及回复
     */
    public function show(Request $request, $courseId, $id)
    {
        $discussion = PublicDiscussion::with('user', 'replies.user')->find($id);
        
        if (!$discussion || $discussion->course_id != $courseId) {
            throw new CustomException('讨论不存在');
        }
        
        // 增加浏览计数
        $discussion->increment('view_count');
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'discussion' => $discussion
        ]));
    }

    /**
     * 回复讨论
     */
    public function reply(Request $request, $courseId, $id)
    {
        $this->validate($request, [
            'content' => 'required'
        ]);

        $reply = DiscussionReply::create([
            'discussion_id' => $id,
            'user_id' => auth()->id(),
            'content' => $request->input('content')
        ]);
        
        // 更新讨论的回复计数
        $discussion = PublicDiscussion::find($id);
        $discussion->increment('reply_count');
        
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc([
            'reply' => $reply
        ]));
    }
}