<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\LlmChatAttachment;
use App\Services\LlmAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LlmAttachmentController extends Controller
{
    protected $service;

    public function __construct(LlmAttachmentService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        if ($request->user()) {
            Auth::setUser($request->user());
        }
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $attachment = $this->service->create($request->file('file'), Auth::id());
            return response()->json([
                'success' => true,
                'data' => $this->service->serialize($attachment, true),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()) {
            Auth::setUser($request->user());
        }
        $attachment = LlmChatAttachment::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$attachment) {
            return response()->json(['success' => false, 'message' => '附件不存在'], 404);
        }

        try {
            $this->service->delete($attachment);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }
    }
}
