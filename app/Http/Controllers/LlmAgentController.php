<?php

namespace App\Http\Controllers;

use App\Services\LlmAgentService;
use App\Services\LlmAgentVersionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class LlmAgentController extends Controller
{
    protected $agentService;
    protected $agentVersionService;

    public function __construct(
        LlmAgentService $agentService,
        LlmAgentVersionService $agentVersionService
    ) {
        $this->middleware('auth');
        $this->agentService = $agentService;
        $this->agentVersionService = $agentVersionService;
    }

    /**
     * 获取智能体列表
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'user_id' => Auth::id() // 只返回当前用户的数据
            ];
            if ($request->has('search')) {
                $filters['search'] = $request->get('search');
            }

            $agents = $this->agentService->getAgentsList($filters);

            return response()->json([
                'result' => [
                    'agents' => $agents->items(),
                    'pagination' => [
                        'current_page' => $agents->currentPage(),
                        'per_page' => $agents->perPage(),
                        'total' => $agents->total(),
                        'last_page' => $agents->lastPage(),
                    ]
                ],
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@index error: ' . $e->getMessage());
            return response()->json([
                'message' => '获取智能体列表失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * 获取指定智能体
     */
    public function show($id)
    {
        try {
            $agent = $this->agentService->getAgentById($id);

            if (!$agent || $agent->user_id != Auth::id()) {
                return response()->json([
                    'message' => '智能体不存在或无权访问',
                    'success' => false
                ], 404);
            }

            return response()->json([
                'result' => $agent,
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@show error: ' . $e->getMessage());
            return response()->json([
                'message' => '获取智能体失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * 创建智能体
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'avatar' => 'nullable|string',
                'model_id' => 'required|integer|exists:llm_models,id',
                'system_prompt' => 'required|string',
                'temperature' => 'nullable|numeric|min:0|max:2',
                'top_p' => 'nullable|numeric|min:0|max:1',
                'max_tokens' => 'nullable|integer',
                'context_length' => 'nullable|integer',
                'tools_config' => 'nullable|json',
                'is_active' => 'boolean'
                // 不接受is_public和builtin_slug，这些只在管理端设置
            ]);

            // 设置当前用户ID和默认值
            $validatedData = $request->only([
                'name', 'description', 'avatar', 'model_id', 'system_prompt',
                'temperature', 'top_p', 'max_tokens', 'context_length', 'tools_config', 'is_active'
            ]);
            
            $validatedData['user_id'] = Auth::id();
            $validatedData['is_public'] = 0; // 普通用户创建的智能体默认为私有
            $validatedData['builtin_slug'] = null; // 普通用户不能设置内置标识

            $agent = $this->agentService->createAgent($validatedData);

            return response()->json([
                'result' => $agent,
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@store error: ' . $e->getMessage());
            return response()->json([
                'message' => '创建智能体失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * 更新智能体
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
                'avatar' => 'nullable|string',
                'model_id' => 'sometimes|required|integer|exists:llm_models,id',
                'system_prompt' => 'sometimes|required|string',
                'temperature' => 'nullable|numeric|min:0|max:2',
                'top_p' => 'nullable|numeric|min:0|max:1',
                'max_tokens' => 'nullable|integer',
                'context_length' => 'nullable|integer',
                'tools_config' => 'nullable|json',
                'is_active' => 'boolean'
                // 不接受is_public和builtin_slug，这些只在管理端设置
            ]);

            $agent = $this->agentService->getAgentById($id);
            if (!$agent || $agent->user_id != Auth::id()) {
                return response()->json([
                    'message' => '智能体不存在或无权访问',
                    'success' => false
                ], 404);
            }

            $validatedData = $request->only([
                'name', 'description', 'avatar', 'model_id', 'system_prompt',
                'temperature', 'top_p', 'max_tokens', 'context_length', 'tools_config', 'is_active'
            ]);

            $agent = $this->agentService->updateAgent($id, $validatedData);

            if (!$agent) {
                return response()->json([
                    'message' => '智能体不存在',
                    'success' => false
                ], 404);
            }

            return response()->json([
                'result' => $agent,
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@update error: ' . $e->getMessage());
            return response()->json([
                'message' => '更新智能体失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * 删除智能体
     */
    public function destroy($id)
    {
        try {
            $agent = $this->agentService->getAgentById($id);
            if (!$agent || $agent->user_id != Auth::id()) {
                return response()->json([
                    'message' => '智能体不存在或无权访问',
                    'success' => false
                ], 404);
            }

            $result = $this->agentService->deleteAgent($id);

            if (!$result) {
                return response()->json([
                    'message' => '智能体不存在',
                    'success' => false
                ], 404);
            }

            return response()->json([
                'message' => '删除成功',
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => '删除智能体失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    
    /**
     * 获取当前用户信息
     */
    public function getCurrentUser()
    {
        try {
            $user = Auth::user();
            $isAdmin = in_array($user->email, config('admin.super_users', [])) || ($user->hasRole('administrator') ?? false);
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $isAdmin,
                'success' => true
            ]);
        } catch (Exception $e) {
            \Log::error('LlmAgentController@getCurrentUser error: ' . $e->getMessage());
            return response()->json([
                'message' => '获取用户信息失败: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
    
    /**
     * 显示草稿编辑界面
     */
    public function showDraftEditor($agentId)
    {
        try {
            $agent = \App\Models\LlmAgent::with(['versions', 'currentVersion'])->findOrFail($agentId);

            // 检查用户权限
            if ($agent->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // 获取当前草稿版本
            $draftVersion = $agent->versions()
                ->where('version_name', 'draft')
                ->first();

            if (!$draftVersion) {
                // 如果没有草稿版本，尝试获取默认版本
                $draftVersion = $agent->currentVersion;
            }

            return view('llm.agent-draft-editor', compact('agent', 'draftVersion'));
        } catch (\Exception $e) {
            \Log::error('Error showing draft editor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load draft editor'], 500);
        }
    }

    /**
     * 创建新智能体并进入草稿编辑模式
     */
    public function createDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'avatar' => 'nullable|string|max:255',
                'model_id' => 'nullable|integer|exists:llm_models,id',
                'system_prompt' => 'nullable|string',
                'temperature' => 'nullable|numeric|min:0|max:2',
                'top_p' => 'nullable|numeric|min:0|max:1',
                'max_tokens' => 'nullable|integer|min:1',
                'context_length' => 'nullable|integer|min:1',
                'tools_config' => 'nullable|array',
                'is_public' => 'boolean',
                'is_active' => 'boolean'
            ]);

            $userId = Auth::id();

            // 如果没有提供模型ID，则使用默认模型
            if (!isset($validated['model_id'])) {
                // 获取默认模型，这里假设有一个默认模型，或者可以从中获取第一个可用模型
                $defaultModel = \App\Models\LlmModel::first();
                if ($defaultModel) {
                    $validated['model_id'] = $defaultModel->id;
                } else {
                    return response()->json(['error' => 'No available model found'], 400);
                }
            }

            $agent = $this->agentService->createAgentWithDraftVersion([
                'user_id' => $userId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'avatar' => $validated['avatar'] ?? null,
                'is_public' => $validated['is_public'] ?? 0,
                'is_active' => $validated['is_active'] ?? 1
            ], [
                'model_id' => $validated['model_id'],
                'system_prompt' => $validated['system_prompt'] ?? '',
                'temperature' => $validated['temperature'] ?? 0.7,
                'top_p' => $validated['top_p'] ?? 0.9,
                'max_tokens' => $validated['max_tokens'] ?? null,
                'context_length' => $validated['context_length'] ?? 4000,
                'tools_config' => $validated['tools_config'] ?? null,
                'created_by' => $userId,
                'change_log' => 'Initial draft version'
            ]);

            return response()->json([
                'success' => true,
                'agent' => $agent,
                'message' => 'Draft agent created successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating draft agent: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create draft agent'], 500);
        }
    }

    /**
     * 更新草稿版本
     */
    public function updateDraft(Request $request, $agentId)
    {
        try {
            $agent = \App\Models\LlmAgent::findOrFail($agentId);

            // 检查用户权限
            if ($agent->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:500',
                'avatar' => 'nullable|string|max:255',
                'model_id' => 'nullable|integer|exists:llm_models,id',
                'system_prompt' => 'nullable|string',
                'temperature' => 'nullable|numeric|min:0|max:2',
                'top_p' => 'nullable|numeric|min:0|max:1',
                'max_tokens' => 'nullable|integer|min:1',
                'context_length' => 'nullable|integer|min:1',
                'tools_config' => 'nullable|array',
                'is_public' => 'boolean',
                'is_active' => 'boolean'
            ]);

            // 更新基本的智能体信息
            $agentData = [];
            if (isset($validated['name'])) $agentData['name'] = $validated['name'];
            if (isset($validated['description'])) $agentData['description'] = $validated['description'];
            if (isset($validated['avatar'])) $agentData['avatar'] = $validated['avatar'];
            if (isset($validated['is_public'])) $agentData['is_public'] = $validated['is_public'];
            if (isset($validated['is_active'])) $agentData['is_active'] = $validated['is_active'];

            if (!empty($agentData)) {
                $agent->update($agentData);
            }

            // 获取草稿版本并更新
            $draftVersion = $agent->versions()
                ->where('version_name', 'draft')
                ->first();

            if ($draftVersion) {
                $versionData = [];
                if (isset($validated['model_id'])) $versionData['model_id'] = $validated['model_id'];
                if (isset($validated['system_prompt'])) $versionData['system_prompt'] = $validated['system_prompt'];
                if (isset($validated['temperature'])) $versionData['temperature'] = $validated['temperature'];
                if (isset($validated['top_p'])) $versionData['top_p'] = $validated['top_p'];
                if (isset($validated['max_tokens'])) $versionData['max_tokens'] = $validated['max_tokens'];
                if (isset($validated['context_length'])) $versionData['context_length'] = $validated['context_length'];
                if (isset($validated['tools_config'])) $versionData['tools_config'] = $validated['tools_config'];

                if (!empty($versionData)) {
                    $draftVersion->update($versionData);
                }
            }

            return response()->json([
                'success' => true,
                'agent' => $agent->fresh(),
                'version' => $draftVersion ? $draftVersion->fresh() : null,
                'message' => 'Draft updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating draft: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update draft'], 500);
        }
    }

    /**
     * 发布草稿版本
     */
    public function publishDraft($agentId)
    {
        try {
            $agent = \App\Models\LlmAgent::findOrFail($agentId);

            // 检查用户权限
            if ($agent->user_id != Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // 获取草稿版本
            $draftVersion = $agent->versions()
                ->where('version_name', 'draft')
                ->first();

            if (!$draftVersion) {
                return response()->json(['error' => 'No draft version found'], 404);
            }

            // 使用版本服务发布草稿
            $publishedVersion = $this->agentVersionService->publishDraftVersion(
                $agentId,
                'v' . (time() % 10000), // 使用时间戳生成版本号
                'Published from draft'
            );

            return response()->json([
                'success' => true,
                'version' => $publishedVersion,
                'message' => 'Draft published successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error publishing draft: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to publish draft'], 500);
        }
    }

    /**
     * 测试聊天功能
     */
    public function testChat(Request $request, $agentId)
    {
        try {
            $agent = \App\Models\LlmAgent::with(['currentVersion'])->findOrFail($agentId);

            // 检查用户权限
            if ($agent->user_id != Auth::id() && !$agent->is_public) {
                abort(403, 'Unauthorized');
            }

            $validated = $request->validate([
                'message' => 'required|string',
            ]);

            // 这里应该集成实际的聊天功能
            // 目前我们只返回模拟响应
            $response = "This is a test response for agent: {$agent->name}. Your message was: " . $validated['message'];

            return response()->json([
                'success' => true,
                'response' => $response,
                'agent' => $agent
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in test chat: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process chat'], 500);
        }
    }
}