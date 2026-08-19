<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Artifact;
use App\Services\ArtifactService;
use App\Services\PointGrantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArtifactController extends Controller
{
    protected $artifactService;
    protected $pointGrantService;

    public function __construct(ArtifactService $artifactService, PointGrantService $pointGrantService)
    {
        $this->artifactService = $artifactService;
        $this->pointGrantService = $pointGrantService;
    }

    /**
     * 查询某实体的制品列表 / 全局搜索
     * GET /api/v2/artifacts
     * - 实体维度：related_type + related_id（返回该实体全部制品）
     * - 管理维度：keyword / artifact_type / status / page / per_page（返回分组分页）
     */
    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $relatedType = trim((string)$request->input('related_type', ''));
        $relatedId = (int)$request->input('related_id', 0);

        // 只要没有同时提供 related_type + related_id 就进入「管理页全局搜索模式」（按实体聚合）
        if ($relatedType === '' || $relatedId <= 0) {
            $result = $this->artifactService->searchForManage(
                $userId,
                array(
                    'keyword' => trim((string)$request->input('keyword', '')),
                    'related_type' => trim((string)$request->input('related_type', '')),
                    'related_id' => (int)$request->input('related_id', 0),
                    'has_artifact_type' => trim((string)$request->input('artifact_type', '')),
                    'status' => trim((string)$request->input('status', '')),
                ),
                max(1, min(100, (int)$request->input('per_page', 18))),
                max(1, (int)$request->input('page', 1))
            );

            $entities = array();
            foreach ($result['entities'] as $entity) {
                $artifactList = array();
                foreach ($entity['artifacts'] as $artifact) {
                    $artifactList[] = $this->serializeArtifact($artifact, false);
                }
                $entities[] = array(
                    'related_type' => $entity['related_type'],
                    'related_id' => $entity['related_id'],
                    'related_title' => $entity['related_title'],
                    'related_url' => $entity['related_url'],
                    'artifact_types' => array_keys($entity['artifact_types']),
                    'success_types' => array_keys($entity['success_types']),
                    'failed_types' => array_keys($entity['failed_types']),
                    'total' => $entity['total'],
                    'last_generated_at' => $entity['last_generated_at'],
                    'artifacts' => $artifactList,
                );
            }

            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
                'entities' => $entities,
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            )));
        }

        $artifacts = $this->artifactService->listByRelated($userId, $relatedType, $relatedId);

        $result = array();
        foreach ($artifacts as $artifact) {
            // mind_map 需要完整 content（弹窗内嵌 jsMind 用）；其余只返回长度
            $withBody = $artifact->artifact_type === \App\Models\Artifact::TYPE_MIND_MAP;
            $serialized = $this->serializeArtifact($artifact, $withBody);
            if ($request->filled('artifact_type') && $artifact->artifact_type !== (string)$request->input('artifact_type')) {
                continue;
            }
            if ($request->filled('status') && $artifact->status !== (string)$request->input('status')) {
                continue;
            }
            $result[] = $serialized;
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'artifacts' => $result,
        )));
    }

    /**
     * 查询单个制品详情
     * GET /api/v2/artifacts/{id}
     */
    public function show(Request $request, Artifact $artifact)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$artifact->user_id !== $userId) {
            throw new CustomException('制品不存在');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'artifact' => $this->serializeArtifact($artifact, true),
        )));
    }

    /**
     * 生成（或复用）制品
     * POST /api/v2/artifacts/generate
     */
    public function generate(Request $request)
    {
        $this->validate($request, array(
            'related_type' => 'required|string|max:32',
            'related_id' => 'required|integer|min:1',
            'artifact_type' => 'required|string|max:32',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $relatedType = (string)$request->input('related_type');
        $relatedId = (int)$request->input('related_id');
        $artifactType = (string)$request->input('artifact_type');

        $result = $this->artifactService->ensure($userId, $relatedType, $relatedId, $artifactType, array(
            'force' => (int)$request->input('force', 0) === 1,
            'custom_prompt' => (string)$request->input('custom_prompt', ''),
            'template_style' => (string)$request->input('template_style', 'magazine'),
        ));

        $artifact = $result['artifact'];
        if ($result['generated'] && $artifact->status === Artifact::STATUS_SUCCESS) {
            $this->grantPoints($request, $artifact);
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'artifact' => $this->serializeArtifact($artifact, false),
            'generated' => $result['generated'],
        )));
    }

    /**
     * 把 mind_map 制品落库为 minds 节点树
     * POST /api/v2/artifacts/{id}/to-mind
     */
    public function toMind(Request $request, Artifact $artifact)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$artifact->user_id !== $userId) {
            throw new CustomException('制品不存在');
        }

        if ($artifact->status !== Artifact::STATUS_SUCCESS) {
            throw new CustomException('制品尚未生成成功，无法保存为思维导图');
        }

        $root = $this->artifactService->toMindTree($artifact, $userId);
        $this->grantPoints($request, $artifact, 'artifact_mind_map_created');

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'mind_id' => $root->id,
            'mind' => $root,
        )));
    }

    /**
     * 删除制品
     * DELETE /api/v2/artifacts/{id}
     */
    public function destroy(Request $request, Artifact $artifact)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$artifact->user_id !== $userId) {
            throw new CustomException('制品不存在');
        }

        $this->artifactService->delete($artifact);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    protected function grantPoints(Request $request, Artifact $artifact, $event = null)
    {
        try {
            $this->pointGrantService->grantByEvent(
                (int)$artifact->user_id,
                $event ?: ('artifact_' . $artifact->artifact_type . '_created'),
                'artifact',
                (int)$artifact->id
            );
        } catch (\Throwable $e) {
            Log::warning('grant points on artifact failed', array(
                'artifact_id' => $artifact->id,
                'error' => $e->getMessage(),
            ));
        }
    }

    protected function serializeArtifact(Artifact $artifact, $withContent = true)
    {
        $data = array(
            'id' => (int)$artifact->id,
            'name' => $artifact->name,
            'file_type' => $artifact->file_type,
            'artifact_type' => $artifact->artifact_type,
            'related_type' => $artifact->related_type,
            'related_id' => (int)$artifact->related_id,
            'related_title' => $artifact->related_title ?? null,
            'related_url' => $artifact->related_url ?? null,
            'status' => $artifact->status,
            'model_name' => $artifact->model_name,
            'prompt_version' => $artifact->prompt_version,
            'generated_at' => $artifact->generated_at ? $artifact->generated_at->format('Y-m-d H:i:s') : null,
            'error_message' => $artifact->error_message,
        );

        if ($withContent) {
            $data['content'] = $artifact->content;
        } else {
            $data['content_length'] = $artifact->content !== null ? mb_strlen($artifact->content) : 0;
        }

        return $data;
    }
}