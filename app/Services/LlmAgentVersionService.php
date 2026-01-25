<?php

namespace App\Services;

use App\Repositories\LlmAgentVersionRepository;
use App\Repositories\LlmAgentRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class LlmAgentVersionService
{
    protected $repository;
    protected $agentRepository;

    public function __construct(
        LlmAgentVersionRepository $versionRepository,
        LlmAgentRepository $agentRepository
    ) {
        $this->repository = $versionRepository;
        $this->agentRepository = $agentRepository;
    }

    /**
     * 获取智能体版本列表
     */
    public function getAgentVersionsList(int $agentId, array $filters = [], int $perPage = 15, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentVersionsList($agentId, $filters, $perPage, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@getAgentVersionsList error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的所有版本
     */
    public function getAgentVersions(int $agentId, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentVersions($agentId, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@getAgentVersions error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的特定版本
     */
    public function getAgentVersionById(int $id, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentVersionById($id, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@getAgentVersionById error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的特定版本通过版本号
     */
    public function getAgentVersionByNumber(int $agentId, int $versionNumber, bool $withTrashed = false)
    {
        try {
            return $this->repository->getAgentVersionByNumber($agentId, $versionNumber, $withTrashed);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@getAgentVersionByNumber error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取智能体的默认版本
     */
    public function getDefaultVersion(int $agentId)
    {
        try {
            return $this->repository->getDefaultVersion($agentId);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@getDefaultVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建智能体版本
     */
    public function createAgentVersion(array $data)
    {
        try {
            // 确保版本号是唯一的
            if (!isset($data['version_number'])) {
                // 如果没有提供版本号，自动计算下一个版本号
                $latestVersion = $this->repository->getModel()->where('agent_id', $data['agent_id'])
                    ->max('version_number');
                $data['version_number'] = $latestVersion ? $latestVersion + 1 : 1;
            }

            // 如果是第一个版本且没有设置为默认版本，则设置为默认版本
            if (!isset($data['is_default']) && $data['version_number'] == 1) {
                $data['is_default'] = true;
            }

            // 设置版本名称
            if (!isset($data['version_name'])) {
                $data['version_name'] = 'v' . $data['version_number'];
            }

            return $this->repository->createAgentVersion($data);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@createAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建或更新智能体及其初始版本（用于创建草稿）
     */
    public function createDraftAgentWithVersion(array $agentData, array $versionData)
    {
        try {
            DB::beginTransaction();

            // 创建智能体
            $agent = $this->agentRepository->createAgent([
                'user_id' => $agentData['user_id'],
                'name' => $agentData['name'],
                'description' => $agentData['description'] ?? '',
                'avatar' => $agentData['avatar'] ?? null,
                'is_public' => $agentData['is_public'] ?? 0,
                'is_active' => $agentData['is_active'] ?? 1,
                'builtin_slug' => $agentData['builtin_slug'] ?? null
            ]);

            // 创建初始版本（草稿版本）
            $version = $this->createAgentVersion([
                'agent_id' => $agent->id,
                'version_name' => 'draft',
                'version_number' => 1,
                'model_id' => $versionData['model_id'],
                'system_prompt' => $versionData['system_prompt'] ?? '',
                'temperature' => $versionData['temperature'] ?? 0.7,
                'top_p' => $versionData['top_p'] ?? 0.9,
                'max_tokens' => $versionData['max_tokens'] ?? null,
                'context_length' => $versionData['context_length'] ?? 4000,
                'tools_config' => $versionData['tools_config'] ?? null,
                'is_default' => true, // 草稿版本作为默认版本
                'is_active' => true,
                'created_by' => $versionData['created_by'],
                'change_log' => $versionData['change_log'] ?? 'Initial draft version'
            ]);

            // 更新智能体的 current_version_id
            $agent->update(['current_version_id' => $version->id]);

            DB::commit();

            return $agent;
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('LlmAgentVersionService@createDraftAgentWithVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 更新智能体版本
     */
    public function updateAgentVersion(int $id, array $data)
    {
        try {
            return $this->repository->updateAgentVersion($id, $data);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@updateAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除智能体版本
     */
    public function deleteAgentVersion(int $id, bool $force = false)
    {
        try {
            return $this->repository->deleteAgentVersion($id, $force);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@deleteAgentVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 设置为默认版本
     */
    public function setAsDefaultVersion(int $id)
    {
        try {
            return $this->repository->setAsDefaultVersion($id);
        } catch (Exception $e) {
            \Log::error('LlmAgentVersionService@setAsDefaultVersion error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 发布版本（将草稿版本发布为正式版本）
     */
    public function publishDraftVersion(int $agentId, string $versionName = null, string $changeLog = null)
    {
        try {
            DB::beginTransaction();

            // 获取当前草稿版本
            $draftVersion = $this->repository->getModel()->where('agent_id', $agentId)
                ->where('version_name', 'draft')
                ->first();

            if (!$draftVersion) {
                throw new Exception('No draft version found for this agent.');
            }

            // 获取最新的正式版本号
            $latestFormalVersion = $this->repository->getModel()->where('agent_id', $agentId)
                ->where('version_name', '!=', 'draft')
                ->max('version_number');

            // 创建新的正式版本
            $newVersionNumber = $latestFormalVersion ? $latestFormalVersion + 1 : 1;
            $newVersionName = $versionName ?: 'v' . $newVersionNumber;

            $publishedVersion = $this->createAgentVersion([
                'agent_id' => $agentId,
                'version_name' => $newVersionName,
                'version_number' => $newVersionNumber,
                'model_id' => $draftVersion->model_id,
                'system_prompt' => $draftVersion->system_prompt,
                'temperature' => $draftVersion->temperature,
                'top_p' => $draftVersion->top_p,
                'max_tokens' => $draftVersion->max_tokens,
                'context_length' => $draftVersion->context_length,
                'tools_config' => $draftVersion->tools_config,
                'is_default' => true, // 设置为默认版本
                'is_active' => true,
                'created_by' => $draftVersion->created_by,
                'change_log' => $changeLog ?: 'Published from draft'
            ]);

            // 更新智能体的 current_version_id
            $agent = $this->agentRepository->getAgentById($agentId);
            if ($agent) {
                $agent->update(['current_version_id' => $publishedVersion->id]);
            }

            // 将之前的默认版本设为非默认
            $this->repository->getModel()->where('agent_id', $agentId)
                ->where('id', '!=', $publishedVersion->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            DB::commit();

            return $publishedVersion;
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('LlmAgentVersionService@publishDraftVersion error: ' . $e->getMessage());
            throw $e;
        }
    }
}