<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\DiscussionReply;
use App\Models\PublicDiscussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function index(Request $request, $courseId)
    {
        $discussions = PublicDiscussion::where('course_id', $courseId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'discussions' => $discussions,
        )));
    }

    public function store(Request $request, $courseId)
    {
        $this->validate($request, array(
            'title' => 'required|max:255',
            'content' => 'required',
            'type' => 'in:question,note,resource,tip',
            'course_item_id' => 'nullable|integer',
        ));

        $discussion = PublicDiscussion::create(array(
            'course_id' => $courseId,
            'course_item_id' => $request->input('course_item_id'),
            'user_id' => $this->getAuthUserId($request),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'type' => $request->input('type', 'note'),
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'discussion' => $discussion,
        )));
    }

    public function show(Request $request, $courseId, $id)
    {
        $discussion = PublicDiscussion::with('user', 'replies.user')->find($id);
        if (!$discussion || (int)$discussion->course_id !== (int)$courseId) {
            throw new CustomException('讨论不存在');
        }

        $discussion->increment('view_count');

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'discussion' => $discussion,
        )));
    }

    public function reply(Request $request, $courseId, $id)
    {
        $this->validate($request, array(
            'content' => 'required',
        ));

        $discussion = PublicDiscussion::where('id', $id)->where('course_id', $courseId)->first();
        if (!$discussion) {
            throw new CustomException('讨论不存在');
        }

        $reply = DiscussionReply::create(array(
            'discussion_id' => $id,
            'user_id' => $this->getAuthUserId($request),
            'content' => $request->input('content'),
        ));

        $discussion->increment('reply_count');

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'reply' => $reply,
        )));
    }
}
