<?php

namespace App\Repositories;

use App\Models\LlmUsageLog;
use Illuminate\Support\Facades\DB;

class LlmUsageLogRepository
{
    /**
     * 获取所有使用记录
     */
    public function getAllUsageLogs($withTrashed = false)
    {
        $query = LlmUsageLog::with(['provider', 'model', 'credential', 'user']);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * 根据ID获取使用记录
     */
    public function getUsageLogById($id, $withTrashed = false)
    {
        $query = LlmUsageLog::with(['provider', 'model', 'credential', 'user'])->where('id', $id);
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query->first();
    }

    /**
     * 根据条件获取使用记录分页
     */
    public function getUsageLogsByFilters($filters = [], $perPage = 20, $userId = null)
    {
        $query = LlmUsageLog::with(['provider', 'model', 'credential', 'user'])
                          ->orderBy('created_at', 'desc');

        // 如果指定了用户ID，只查询该用户的数据
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // 根据供应商过滤
        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        // 根据模型过滤
        if (!empty($filters['model_id'])) {
            $query->where('model_id', $filters['model_id']);
        }

        // 根据凭据过滤
        if (!empty($filters['credential_id'])) {
            $query->where('credential_id', $filters['credential_id']);
        }

        // 根据用户过滤
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // 根据状态过滤
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 根据日期范围过滤
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->paginate($perPage);
    }

    /**
     * 创建使用记录
     */
    public function createUsageLog(array $data)
    {
        return LlmUsageLog::create($data);
    }

    /**
     * 更新使用记录
     */
    public function updateUsageLog($id, array $data)
    {
        $log = LlmUsageLog::where('id', $id)->first();
        if ($log) {
            $log->update($data);
        }
        return $log;
    }

    /**
     * 删除使用记录
     */
    public function deleteUsageLog($id, $force = false)
    {
        $log = LlmUsageLog::where('id', $id)->first();
        if ($log) {
            if ($force) {
                return $log->forceDelete();
            } else {
                return $log->delete();
            }
        }
        return false;
    }

    /**
     * 获取统计信息
     */
    public function getUsageStatistics($filters = [])
    {
        $query = LlmUsageLog::select(
            DB::raw('SUM(input_tokens) as total_input_tokens'),
            DB::raw('SUM(output_tokens) as total_output_tokens'),
            DB::raw('SUM(total_tokens) as total_tokens'),
            DB::raw('SUM(cost) as total_cost'),
            DB::raw('COUNT(*) as total_requests'),
            DB::raw('AVG(request_time) as avg_request_time')
        );

        // 应用过滤条件
        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }
        if (!empty($filters['model_id'])) {
            $query->where('model_id', $filters['model_id']);
        }
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->first();
    }
}