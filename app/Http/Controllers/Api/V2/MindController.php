<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Mind;
use App\Services\MindService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MindController extends Controller
{
    protected $mindService;
    protected $pointGrantService;

    public function __construct(MindService $mindService, PointGrantService $pointGrantService)
    {
        $this->mindService = $mindService;
        $this->pointGrantService = $pointGrantService;
    }

    public function index(Request $request)
    {
        $tagId = $request->input('tag_id', '');
        $name = $request->input('name', '');
        $minds = $this->mindService->getIndexList($tagId, $name);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'minds' => $minds,
        )));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required',
            'content' => 'nullable|string',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|integer|min:1',
        ));

        $name = $request->input('name');
        $parentMindId = $request->input('parent_mind_id', 0);
        $content = $request->input('content', null);
        $sourceType = $request->input('source_type', null);
        $sourceId = $request->input('source_id', null);
        $mind = $this->mindService->store($name, $parentMindId, $content, $sourceType, $sourceId);
        try {
            $this->pointGrantService->grantByEvent(
                (int)$mind->user_id,
                'mind_created',
                'mind',
                (int)$mind->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on mind store failed', array(
                'mind_id' => $mind->id,
                'user_id' => $mind->user_id,
                'error' => $e->getMessage(),
            ));
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'id' => $mind->id,
            'name' => $mind->name,
            'mind' => $mind,
        )));
    }

    public function destroy(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);
        $this->mindService->removeMind($mind);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function update(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);

        if ($request->has('name')) {
            $mind->name = $request->input('name');
        }

        if ($request->has('content')) {
            $content = str_replace(array("\r\n", "\r", "\n"), "\\r\\n", $request->input('content'));
            $mind->content = $content;
        }

        $mind->update();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'mind' => $mind->fresh(),
        )));
    }

    public function show(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'mind' => $mind,
        )));
    }

    public function jsmind(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);
        $jsmindDatas = $this->mindService->getJsMindFormatInfo($mind);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'jsmind_datas' => json_encode($jsmindDatas),
        )));
    }

    public function outline(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);
        $datas = $this->mindService->getNodeTreeHtmlData($mind);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'datas' => $datas,
        )));
    }

    public function addTag(Request $request, Mind $mind)
    {
        $this->authorize('destroy', $mind);
        if ((int)$mind->is_root !== 1) {
            throw new CustomException('Root节点错误');
        }

        $this->validate($request, array(
            'tag_name' => 'required',
        ));

        $tag = $this->mindService->addTag($mind, $request->input('tag_name'));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'tag' => $tag,
        )));
    }
}
