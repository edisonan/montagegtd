<?php

namespace App\Http\Controllers;

use App\Models\LlmAgent;
use App\Models\LlmAgentVersion;
use App\Models\LlmModel;
use App\Models\LlmProviderCredential;
use App\Models\LlmProvider;
use App\Models\LlmConversation;
use App\Services\LlmPolishService;
use App\Services\LlmConversationService;
use App\Repositories\LlmSessionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class LlmController extends Controller
{

    
    /**
     * LlmConversationService 实例.
     *
     * @var LlmConversationService
     */
    protected $conversationService;

    /**
     * LlmSessionRepository 实例.
     *
     * @var LlmSessionRepository
     */
    protected $sessionRepository;

    public function __construct(LlmConversationService $conversationService, LlmSessionRepository $sessionRepository)
    {
        $this->middleware('auth');
        
        $this->conversationService = $conversationService;
        $this->sessionRepository = $sessionRepository;
    }

    public function getProviders()
    {
        try {
            $user = Auth::user();
            
            // 查询所有可用的供应商
            $providers = LlmProvider::when(!$user->is_admin, function ($query) use ($user) {
                // 如果用户不是管理员，则查询公共供应商或属于自己的供应商
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            })
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
            
            return response()->json([
                'result' => [
                    'providers' => $providers
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取供应商失败: ' . $e->getMessage());
            return response()->json(['message' => '获取供应商失败'], 500);
        }
    }

    public function getProvider($id)
    {
        try {
            $user = Auth::user();
            
            $provider = LlmProvider::when(!$user->is_admin, function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            })
            ->find($id);
            
            if (!$provider) {
                return response()->json(['message' => '供应商不存在'], 404);
            }
            
            return response()->json(['result' => $provider]);
        } catch (\Exception $e) {
            Log::error('获取供应商详情失败: ' . $e->getMessage());
            return response()->json(['message' => '获取供应商详情失败'], 500);
        }
    }

    public function saveProvider(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            
            $this->validate($request, [
                'name' => 'required|string|max:100',
                'slug' => 'required|string|max:50|unique:llm_providers,slug,' . $id,
                'description' => 'nullable|string',
                'base_url' => 'nullable|url',
                'api_type' => 'required|in:openai,anthropic,custom',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'rate_limit_per_minute' => 'nullable|integer|min:0',
                'concurrent_limit' => 'nullable|integer|min:0',
                'config_schema' => 'nullable|array'
            ]);
            
            if ($id) {
                $provider = LlmProvider::when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->find($id);
                
                if (!$provider) {
                    return response()->json(['message' => '供应商不存在'], 404);
                }
                
                $provider->update($request->all());
            } else {
                $validatedData = $request->all();
                $validatedData['user_id'] = $user->id;
                $provider = LlmProvider::create($validatedData);
            }
            
            return response()->json(['result' => $provider]);
        } catch (\Exception $e) {
            Log::error('保存供应商失败: ' . $e->getMessage());
            return response()->json(['message' => '保存供应商失败'], 500);
        }
    }

    public function deleteProvider($id)
    {
        try {
            $user = Auth::user();
            
            $provider = LlmProvider::when(!$user->is_admin, function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            })
            ->find($id);
            
            if (!$provider) {
                return response()->json(['message' => '供应商不存在'], 404);
            }
            
            $provider->delete();
            
            return response()->json(['message' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除供应商失败: ' . $e->getMessage());
            return response()->json(['message' => '删除供应商失败'], 500);
        }
    }

    public function getModels()
    {
        try {
            $user = Auth::user();
            
            $models = LlmModel::with('provider')
                ->when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->orderBy('provider_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'result' => [
                    'models' => $models
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取模型失败: ' . $e->getMessage());
            return response()->json(['message' => '获取模型失败'], 500);
        }
    }

    public function getModel($id)
    {
        try {
            $user = Auth::user();
            
            $model = LlmModel::with('provider')
                ->when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->find($id);
            
            if (!$model) {
                return response()->json(['message' => '模型不存在'], 404);
            }
            
            return response()->json(['result' => $model]);
        } catch (\Exception $e) {
            Log::error('获取模型详情失败: ' . $e->getMessage());
            return response()->json(['message' => '获取模型详情失败'], 500);
        }
    }

    public function saveModel(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            
            $this->validate($request, [
                'provider_id' => 'required|exists:llm_providers,id',
                'name' => 'required|string|max:100',
                'display_name' => 'nullable|string|max:100',
                'model_type' => 'required|in:chat,completion,embedding,image',
                'context_length' => 'nullable|integer|min:0',
                'max_tokens' => 'nullable|integer|min:0',
                'input_price_per_1k' => 'nullable|numeric|min:0',
                'output_price_per_1k' => 'nullable|numeric|min:0',
                'capabilities' => 'nullable|array',
                'sort_order' => 'integer|min:0',
                'is_active' => 'boolean'
            ]);
            
            if ($id) {
                $model = LlmModel::when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->find($id);
                
                if (!$model) {
                    return response()->json(['message' => '模型不存在'], 404);
                }
                
                $model->update($request->all());
            } else {
                $validatedData = $request->all();
                $validatedData['user_id'] = $user->id;
                $model = LlmModel::create($validatedData);
            }
            
            return response()->json(['result' => $model]);
        } catch (\Exception $e) {
            Log::error('保存模型失败: ' . $e->getMessage());
            return response()->json(['message' => '保存模型失败'], 500);
        }
    }

    public function deleteModel($id)
    {
        try {
            $user = Auth::user();
            
            $model = LlmModel::when(!$user->is_admin, function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            })
            ->find($id);
            
            if (!$model) {
                return response()->json(['message' => '模型不存在'], 404);
            }
            
            $model->delete();
            
            return response()->json(['message' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除模型失败: ' . $e->getMessage());
            return response()->json(['message' => '删除模型失败'], 500);
        }
    }

    public function getCredentials()
    {
        try {
            $user = Auth::user();
            
            $credentials = LlmProviderCredential::with('provider')
                ->when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->orderBy('provider_id')
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'result' => [
                    'credentials' => $credentials
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取凭据失败: ' . $e->getMessage());
            return response()->json(['message' => '获取凭据失败'], 500);
        }
    }

    public function getCredential($id)
    {
        try {
            $user = Auth::user();
            
            $credential = LlmProviderCredential::with('provider')
                ->when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->find($id);
            
            if (!$credential) {
                return response()->json(['message' => '凭据不存在'], 404);
            }
            
            return response()->json(['result' => $credential]);
        } catch (\Exception $e) {
            Log::error('获取凭据详情失败: ' . $e->getMessage());
            return response()->json(['message' => '获取凭据详情失败'], 500);
        }
    }

    public function saveCredential(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            
            $this->validate($request, [
                'provider_id' => 'required|exists:llm_providers,id',
                'name' => 'required|string|max:100',
                'api_key' => 'nullable|string', // 不在创建时强制要求，因为更新时不总是提供
                'config' => 'nullable|array',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'quota_limit' => 'nullable|integer|min:0'
            ]);
            
            if ($id) {
                $credential = LlmProviderCredential::when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where(function ($q) use ($user) {
                        $q->whereNull('user_id')->orWhere('user_id', $user->id);
                    });
                })
                ->find($id);
                
                if (!$credential) {
                    return response()->json(['message' => '凭据不存在'], 404);
                }
                
                $requestData = $request->all();
                
                // 处理API密钥更新（如果提供了新密钥）
                if (isset($requestData['api_key']) && !empty($requestData['api_key'])) {
                    $credential->api_key = bcrypt($requestData['api_key']);
                }
                unset($requestData['api_key']);
                
                $credential->update($requestData);
            } else {
                // 创建新的凭据
                $requestData = $request->all();
                $requestData['user_id'] = $user->id;
                if (isset($requestData['api_key'])) {
                    $requestData['api_key'] = bcrypt($requestData['api_key']);
                }
                $credential = LlmProviderCredential::create($requestData);
            }
            
            // 如果设置了为默认凭据，需要更新其他凭据的状态
            if ($credential->is_default) {
                LlmProviderCredential::where('user_id', $user->id)
                    ->where('provider_id', $credential->provider_id)
                    ->where('id', '!=', $credential->id)
                    ->update(['is_default' => false]);
            }
            
            return response()->json(['result' => $credential]);
        } catch (\Exception $e) {
            Log::error('保存凭据失败: ' . $e->getMessage());
            return response()->json(['message' => '保存凭据失败'], 500);
        }
    }

    public function deleteCredential($id)
    {
        try {
            $user = Auth::user();
            
            $credential = LlmProviderCredential::when(!$user->is_admin, function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                });
            })
            ->find($id);
            
            if (!$credential) {
                return response()->json(['message' => '凭据不存在'], 404);
            }
            
            $credential->delete();
            
            return response()->json(['message' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除凭据失败: ' . $e->getMessage());
            return response()->json(['message' => '删除凭据失败'], 500);
        }
    }
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'nullable|string',
            'session_id' => 'nullable|integer',
            'refer_text' => 'nullable|string',
            'query' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query = $request->input('query');
        $referText = $request->input('refer_text', '');
        $sessionId = $request->input('session_id', '');

        // 获取会话和智能体信息
        $session = $this->sessionRepository->findById($sessionId);
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => '会话不存在或无权限访问'
            ], 404);
        }

        $tempAgentId = $request->input('agent_id', 'builtin_common');
        $agent = null;

        if ($tempAgentId) {
            // 检查是否包含 builtin
            if (strpos($tempAgentId, 'builtin') !== false) {
                // 如果是 builtin，使用 builtin_slug 查询
                $agent = LlmAgent::where('builtin_slug', $tempAgentId)->first();
            } else {
                // 否则使用 id 查询
                $agent = LlmAgent::find($tempAgentId);
            }
        }

        if (!$agent) {
            if ($session->agent_id) {
                $agent = $session->agent;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '指定智能体不存在'
                ], 404);
            }
        }

        if(!empty($agent->current_version_id)) {
            $agentVersion = LlmAgentVersion::where('id', $agent->current_version_id)->first();
        } else {
            $agentVersion = LlmAgentVersion::where('agent_id', $agent->id)->first();
        }

        try {
            $user = Auth::user();
            if($agent->user_id != $user->id) {
                $credential = LlmProviderCredential::where('user_id', $user->id)
                    ->where('is_active', 1)
                    ->first();

                if (!$credential) {
                    return response()->json(['error' => '未找到有效的API凭据'], 400);
                }

                $provider = LLMProvider::where('id', $credential->provider_id)->first();

                $model = LlmModel::where('provider_id', $credential->provider_id)
                    ->first();

                if (!$model) {
                    return response()->json(['error' => '未找到有效的模型'], 400);
                }
            } else {
                $model = LlmModel::where('id', $agentVersion->model_id)
                    ->first();

                $provider = LLMProvider::where('id', $model->provider_id)->first();

                $credential = LlmProviderCredential::where('user_id', $user->id)->where('provider_id', $provider->id)
                    ->where('is_active', 1)
                    ->first();
            }

            $systemContent = $agentVersion->system_content;
            if(!empty($referText)) {
                $systemContent = $systemContent."\n引用文本：\n" .$referText;
            }

            // 记录请求数据
            $requestData = [
                'model' => $model->name,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $referText,
                    ],
                    [
                        'role' => 'user',
                        'content' => $query,
                    ]
                ],
                'stream' => true
            ];

            // 先创建对话记录
            $conversation = $this->conversationService->createConversation([
                'user_id' => $user->id,
                'model_id' => $model->id,
                'credential_id' => $credential->id,
                'question' => $query,
                'request_data' => $requestData,
                'answer' => '' // 初始化为空，后续填充
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $provider->base_url."/chat/completions",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $credential->api_key,
                    'Accept: text/event-stream',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive'
                ],
                CURLOPT_WRITEFUNCTION => function($ch, $data) use (&$completeAnswer) {
                    // 直接输出原始数据，让前端处理
                    echo $data;
                    ob_flush();
                    flush();
                    return strlen($data);
                },
                CURLOPT_HEADERFUNCTION => function($ch, $header) {
                    // 转发响应头
                    if (strpos($header, 'HTTP/') !== 0 &&
                        strpos($header, 'Content-Type:') !== 0 &&
                        strpos($header, 'Transfer-Encoding:') !== 0 &&
                        trim($header) !== '') {
                        header($header);
                    }
                    return strlen($header);
                },
                CURLOPT_TIMEOUT => 300
            ]);

            curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            Log::info("askAi cURL Response:", [
                'http_code' => $httpCode,
                'error' => $error,
                'request_data' => $requestData
            ]);

            if ($error) {
                Log::error("askAi cURL Error:", [$error]);

                // 更新对话记录，保存错误信息
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => '发生错误: ' . $error,
                    'response_data' => ['error' => $error],
                    'answered_at' => now()
                ]);

                echo "data: 发生错误: " . $error . "\n\n";
                ob_flush();
                flush();
            }

            curl_close($curl);

            // 发送结束信号
            echo "\ndata: [DONE]\n\n";
            ob_flush();
            flush();

            exit();
        } catch (\Exception $e) {
            Log::error("askAi Error:", [$e->getMessage()]);

            // 尝试更新对话记录，保存错误信息
            if (isset($conversation)) {
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => '发生异常: ' . $e->getMessage(),
                    'response_data' => ['exception' => $e->getMessage()],
                    'answered_at' => now()
                ]);
            }

            echo "data: 发生异常: " . $e->getMessage() . "\n\n";
            echo "data: [DONE]\n\n";
            ob_flush();
            flush();

            exit();
        }
    }

    public function getAgents()
    {
        try {
            $user = Auth::user();
            
            $agents = \App\Models\LlmAgent::with('model')
                ->where('user_id', $user->id) // 只获取当前用户的智能体
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'result' => [
                    'agents' => $agents
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取智能体失败: ' . $e->getMessage());
            return response()->json(['message' => '获取智能体失败'], 500);
        }
    }

    public function getAgent($id)
    {
        try {
            $user = Auth::user();
            
            $agent = \App\Models\LlmAgent::with('model')
                ->where('user_id', $user->id) // 确保只能访问自己的智能体
                ->find($id);
            
            if (!$agent) {
                return response()->json(['message' => '智能体不存在'], 404);
            }
            
            return response()->json(['result' => $agent]);
        } catch (\Exception $e) {
            Log::error('获取智能体详情失败: ' . $e->getMessage());
            return response()->json(['message' => '获取智能体详情失败'], 500);
        }
    }

    public function saveAgent(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'avatar' => 'nullable|url',
                'model_id' => 'required|exists:llm_models,id',
                'system_prompt' => 'required|string',
                'temperature' => 'nullable|numeric|min:0|max:2',
                'top_p' => 'nullable|numeric|min:0|max:1',
                'max_tokens' => 'nullable|integer|min:1',
                'context_length' => 'nullable|integer|min:1',
                'tools_config' => 'nullable|array',
                'is_active' => 'boolean'
            ]);
            
            if ($id) {
                $agent = LlmAgent::where('user_id', $user->id)->find($id);
                
                if (!$agent) {
                    return response()->json(['message' => '智能体不存在'], 404);
                }
                
                $agent->update($request->all());
            } else {
                $validatedData = $request->all();
                $validatedData['user_id'] = $user->id;
                $agent = LlmAgent::create($validatedData);
            }
            
            return response()->json(['result' => $agent]);
        } catch (\Exception $e) {
            Log::error('保存智能体失败: ' . $e->getMessage());
            return response()->json(['message' => '保存智能体失败'], 500);
        }
    }

    public function deleteAgent($id)
    {
        try {
            $user = Auth::user();
            
            $agent = \App\Models\LlmAgent::where('user_id', $user->id)->find($id);
            
            if (!$agent) {
                return response()->json(['message' => '智能体不存在'], 404);
            }
            
            $agent->delete();
            
            return response()->json(['message' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除智能体失败: ' . $e->getMessage());
            return response()->json(['message' => '删除智能体失败'], 500);
        }
    }
}