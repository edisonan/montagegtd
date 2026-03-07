<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Note;
use App\Services\AchievementAutoUnlockService;
use App\Services\NoteService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    protected $noteService;
    protected $achievementAutoUnlockService;
    protected $pointGrantService;

    public function __construct(
        NoteService $noteService,
        AchievementAutoUnlockService $achievementAutoUnlockService,
        PointGrantService $pointGrantService
    )
    {
        $this->noteService = $noteService;
        $this->achievementAutoUnlockService = $achievementAutoUnlockService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $type = $request->input('type', '');
        $addContent = $request->input('add_content', '');
        $sourceType = $request->input('source_type', 0);
        $sourceId = $request->input('source_id', 0);
        $tagId = $request->input('tag_id', 0);
        $keyword = $request->input('keyword', '');

        $datas = $this->noteService->getIndexInfo($addContent, $type, $tagId, $keyword, $sourceType, $sourceId);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($datas));
    }

    public function show(Request $request, Note $note)
    {
        $this->authorize('destroy', $note);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($note));
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            throw new CustomException('缺少上传文件');
        }

        $file = $request->file('file');
        if (!$file->isValid()) {
            throw new CustomException('上传文件无效');
        }

        $allowedMimeTypes = array('audio/mp3', 'audio/mpeg', 'audio/mpeg3');
        if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            throw new CustomException('仅支持mp3音频上传');
        }

        $fname = $request->input('fname', '');
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            throw new CustomException('用户未认证');
        }

        $recordName = $userId . $fname . '.mp3';
        $targetDir = rtrim(config('app.storage_path'), '/') . '/recorders/temp/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $file->move($targetDir, $recordName);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'record_name' => $recordName,
        )));
    }

    public function getRecord(Request $request, Note $note)
    {
        $userId = $this->getAuthUserId($request);
        if ($note->user_id != $userId && (int)$note->status !== 2) {
            throw new CustomException('无权限');
        }

        $path = rtrim(config('app.storage_path'), '/') . '/' . ltrim($note->record_path, '/');
        if (empty($note->record_path) || !file_exists($path)) {
            throw new CustomException('语音文件不存在');
        }

        return response()->file($path, array(
            'Content-Type' => 'audio/mpeg',
        ));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required',
            'status' => 'required',
        ));

        $fname = $request->input('fname', '');
        $addImage = $request->input('add_image', '');
        $name = $request->input('name');
        $status = $request->input('status');
        $sourceType = $request->input('source_type', 0);
        $sourceId = $request->input('source_id', 0);

        $note = $this->noteService->store($name, $status, $addImage, $fname, $sourceType, $sourceId);
        $userId = (int)$this->getAuthUserId($request);
        if ($userId > 0) {
            try {
                $this->pointGrantService->grantByEvent(
                    $userId,
                    'note_created',
                    'note',
                    (int)$note->id
                );
            } catch (\Throwable $e) {
                Log::warning('grant points on note store failed', array(
                    'user_id' => $userId,
                    'note_id' => isset($note->id) ? (int)$note->id : 0,
                    'error' => $e->getMessage(),
                ));
            }

            try {
                $this->achievementAutoUnlockService->evaluateForUser($userId);
            } catch (\Throwable $e) {
                Log::warning('auto unlock achievements on note store failed', array(
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ));
            }
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function update(Request $request, Note $note)
    {
        $this->authorize('destroy', $note);

        $this->validate($request, array(
            'name' => 'required',
            'status' => 'required',
        ));

        $this->noteService->update($note, $request->input('name'), $request->input('status'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($note->fresh()));
    }

    public function destroy(Request $request, Note $note)
    {
        $this->authorize('destroy', $note);
        $note->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function like(Request $request, Note $note)
    {
        // Legacy page has a "like" interaction but backend persistence is absent.
        // Keep API compatibility to avoid falling back to legacy web route.
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'note_id' => (int)$note->id,
            'liked' => true,
        )));
    }
}
