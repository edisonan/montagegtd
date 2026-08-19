<?php

namespace App\Repositories;

use App\Models\Artifact;

class ArtifactRepository
{
    public function create(array $data)
    {
        return Artifact::create($data);
    }

    public function findByUserIdAndId($userId, $id)
    {
        return Artifact::where('user_id', $userId)->where('id', $id)->first();
    }

    /**
     * 查询某实体的制品列表
     */
    public function paginateByRelated($userId, $relatedType, $relatedId, $perPage = 50)
    {
        return Artifact::where('user_id', $userId)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->orderBy('generated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function listByRelated($userId, $relatedType, $relatedId)
    {
        return Artifact::where('user_id', $userId)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->orderBy('artifact_type', 'asc')
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    /**
     * 查询某实体某类型的制品（唯一）
     */
    public function findByUniqueKey($userId, $relatedType, $relatedId, $artifactType)
    {
        return Artifact::where('user_id', $userId)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->where('artifact_type', $artifactType)
            ->first();
    }

    public function update(Artifact $artifact, array $data)
    {
        $artifact->fill($data);
        $artifact->save();

        return $artifact;
    }

    public function delete(Artifact $artifact)
    {
        return $artifact->delete();
    }

    /**
     * 分页搜索「关联实体」（管理页按实体聚合卡片）
     * 每个实体一行：related_type + related_id，附带已生成制品类型与最新时间。
     * 支持 related_type / related_id / has_artifact_type / status / keyword。
     */
    public function paginateRelatedEntities($userId, array $filters = array(), $perPage = 18, $page = 1)
    {
        $query = Artifact::where('user_id', $userId)
            ->select('related_type', 'related_id', 'artifact_type', 'status', 'generated_at', 'id')
            ->orderBy('generated_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['related_type'])) {
            $query->where('related_type', $filters['related_type']);
        }
        if (!empty($filters['related_id'])) {
            $query->where('related_id', (int)$filters['related_id']);
        }
        if (!empty($filters['has_artifact_type'])) {
            $query->where('artifact_type', $filters['has_artifact_type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('related_type', 'like', '%' . $keyword . '%')
                    ->orWhere('related_id', (string)(int)$keyword);
            });
        }

        // 取全量匹配记录后在内存按实体聚合（避免 MySQL 5.7 ONLY_FULL_GROUP_BY 问题）
        $rows = $query->limit(2000)->get();

        $entities = array();
        foreach ($rows as $row) {
            $key = $row->related_type . ':' . $row->related_id;
            if (!isset($entities[$key])) {
                $entities[$key] = array(
                    'related_type' => $row->related_type,
                    'related_id' => (int)$row->related_id,
                    'artifact_types' => array(),
                    'success_types' => array(),
                    'failed_types' => array(),
                    'total' => 0,
                    'last_generated_at' => $row->generated_at,
                );
            }
            $entities[$key]['artifact_types'][$row->artifact_type] = true;
            if ($row->status === 'success') {
                $entities[$key]['success_types'][$row->artifact_type] = true;
            } else {
                $entities[$key]['failed_types'][$row->artifact_type] = true;
            }
            $entities[$key]['total']++;
        }

        $entities = array_values($entities);

        // 手动分页
        $total = count($entities);
        $perPage = max(1, $perPage);
        $lastPage = (int)max(1, ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($entities, $offset, $perPage);

        return array(
            'items' => $slice,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
        );
    }
}