<?php

namespace App\Http\Controllers;

use App\Services\LlmAgentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class LlmAgentController extends Controller
{
    protected $agentService;

    public function __construct(LlmAgentService $agentService)
    {
        $this->middleware('auth');
        $this->agentService = $agentService;
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
}