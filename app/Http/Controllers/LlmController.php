<?php

namespace App\Http\Controllers;

use App\Http\Utils\ResponseDataUtil;
use App\Models\LlmAgent;
use App\Models\LlmAgentVersion;
use App\Models\LlmModel;
use App\Models\LlmProviderCredential;
use App\Models\LlmProvider;
use App\Models\LlmConversation;
use App\Services\LlmPolishService;
use App\Services\LlmConversationService;
use App\Services\PointGrantService;
use App\Repositories\LlmSessionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
    protected $pointGrantService;
    protected $conversationHasSessionId = null;

    public function __construct(
        LlmConversationService $conversationService,
        LlmSessionRepository $sessionRepository,
        PointGrantService $pointGrantService
    )
    {
        $this->conversationService = $conversationService;
        $this->sessionRepository = $sessionRepository;
        $this->pointGrantService = $pointGrantService;
    }

    public function getProviders(Request $request)
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

            return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $providers ) );
//            return response()->json([
//                'result' => [
//                    'providers' => $providers
//                ]
//            ]);
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

            $rules = [
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
            ];

            $messages = [
                'slug.unique' => '供应商标识符已存在，请换一个标识符',
                'base_url.url' => 'API基础URL格式不正确',
                'api_type.in' => 'API类型不支持',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                Log::warning('保存供应商参数校验失败', array('errors' => $errors));
                return response()->json(
                    ResponseDataUtil::genFail(ResponseDataUtil::COMMON_ERROR, '参数校验失败', $errors),
                    422
                );
            }

            $providerData = $request->only(array(
                'name',
                'slug',
                'description',
                'base_url',
                'api_type',
                'is_active',
                'priority',
                'config_schema',
                'rate_limit_per_minute',
                'concurrent_limit'
            ));
            
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
                
                $provider->update($providerData);
            } else {
                $providerData['user_id'] = $user->id;
                $provider = LlmProvider::create($providerData);
            }
            
            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($provider));
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

    public function getModels(Request $request)
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

            return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $models ) );
//            return response()->json([
//                'result' => [
//                    'models' => $models
//                ]
//            ]);
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

    public function getCredentials(Request $request)
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

            return $this->jsonResponse ( $request, ResponseDataUtil::genSimpleSucc ( $credentials ) );
//            return response()->json([
//                'result' => [
//                    'credentials' => $credentials
//                ]
//            ]);
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

    public function testCredential($id)
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
                return response()->json([
                    'code' => 1001,
                    'msg' => '凭据不存在',
                    'result' => array(),
                ], 404);
            }

            if ((int)$credential->is_active !== 1) {
                return response()->json([
                    'code' => 1002,
                    'msg' => '凭据未启用',
                    'result' => array(),
                ]);
            }

            return response()->json(ResponseDataUtil::genSimpleSucc(array(
                'credential_id' => $credential->id,
                'provider_id' => $credential->provider_id,
                'provider_name' => $credential->provider ? $credential->provider->name : null,
                'msg' => '连接测试成功',
            )));
        } catch (\Exception $e) {
            Log::error('测试凭据失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1003,
                'msg' => '连接测试失败',
                'result' => array(),
            ], 500);
        }
    }

    public function testModel($id)
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
                return response()->json([
                    'code' => 1001,
                    'msg' => '模型不存在',
                    'result' => array(),
                ], 404);
            }

            if ((int)$model->is_active !== 1) {
                return response()->json([
                    'code' => 1002,
                    'msg' => '模型未启用',
                    'result' => array(),
                ]);
            }

            $provider = $model->provider;
            if (!$provider || (int)$provider->is_active !== 1) {
                return response()->json([
                    'code' => 1003,
                    'msg' => '模型供应商不可用',
                    'result' => array(),
                ]);
            }

            $credential = LlmProviderCredential::where('user_id', $user->id)
                ->where('provider_id', $provider->id)
                ->where('is_active', 1)
                ->orderBy('is_default', 'desc')
                ->orderBy('id', 'asc')
                ->first();

            if (!$credential) {
                return response()->json([
                    'code' => 1004,
                    'msg' => '未找到可用凭据，请先添加并启用该供应商凭据',
                    'result' => array(
                        'provider_id' => $provider->id,
                        'provider_name' => $provider->name,
                    ),
                ]);
            }

            return response()->json(ResponseDataUtil::genSimpleSucc(array(
                'model_id' => $model->id,
                'model_name' => $model->name,
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'credential_id' => $credential->id,
                'credential_name' => $credential->name,
                'msg' => '模型可用性检查通过',
            )));
        } catch (\Exception $e) {
            Log::error('测试模型失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1005,
                'msg' => '模型测试失败',
                'result' => array(),
            ], 500);
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

            // 验证提供商URL格式
            if (!$provider || !$provider->base_url) {
                Log::error("Provider configuration error", [
                    'provider' => $provider,
                    'provider_id' => $provider->id ?? null
                ]);
                return response()->json(['error' => '提供商配置错误'], 500);
            }
            
            $cleanBaseUrl = rtrim($provider->base_url, '/');
            if (!filter_var($cleanBaseUrl, FILTER_VALIDATE_URL)) {
                Log::error("Invalid provider URL", [
                    'base_url' => $provider->base_url,
                    'cleaned_url' => $cleanBaseUrl
                ]);
                return response()->json(['error' => '提供商URL格式错误'], 500);
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

            // 设置流式响应头
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // 禁用Nginx缓冲

            // 先创建对话记录
            $conversationPayload = [
                'user_id' => $user->id,
                'model_id' => $model->id,
                'credential_id' => $credential->id,
                'question' => $query,
                'request_data' => $requestData,
                'answer' => '' // 初始化为空，后续填充
            ];
            if ($sessionId && $this->conversationSupportsSession()) {
                $conversationPayload['session_id'] = (int)$sessionId;
            }
            $conversation = $this->conversationService->createConversation($conversationPayload);

            // 记录详细的请求信息
            Log::info("askAi cURL Request Details:", [
                'provider_base_url' => $provider->base_url,
                'full_request_url' => $provider->base_url . "/chat/completions",
                'request_headers' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . substr($credential->api_key, 0, 10) . '...', // 只记录前10位
                    'Accept: text/event-stream',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive'
                ],
                'request_body' => $requestData,
                'timeout' => 300
            ]);

            $curl = curl_init();
            
            // 启用详细调试信息
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'rw+');
            curl_setopt($curl, CURLOPT_STDERR, $verbose);
            
            $insecureSsl = filter_var(env('LLM_CURL_INSECURE', true), FILTER_VALIDATE_BOOLEAN);

            curl_setopt_array($curl, [
                CURLOPT_URL => $provider->base_url . "/chat/completions",
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
                    // 记录接收到的数据
                    Log::debug("cURL WRITEFUNCTION data received:", [
                        'data_length' => strlen($data),
                        'data_preview' => substr($data, 0, 200), // 只记录前200字符
                        'complete_answer_length' => strlen($completeAnswer ?? '')
                    ]);
                    
                    // 直接输出原始数据，让前端处理
                    echo $data;
                    // 某些运行环境未开启输出缓冲，直接 ob_flush 会抛 warning
                    if (ob_get_level() > 0) {
                        @ob_flush();
                    }
                    @flush();
                    return strlen($data);
                },
                CURLOPT_HEADERFUNCTION => function($ch, $header) {
                    // 记录响应头
                    static $headers = [];
                    $headers[] = trim($header);
                    
                    // 转发响应头
                    if (strpos($header, 'HTTP/') !== 0 &&
                        strpos($header, 'Content-Type:') !== 0 &&
                        strpos($header, 'Transfer-Encoding:') !== 0 &&
                        trim($header) !== '') {
                        header($header);
                    }
                    return strlen($header);
                },
                CURLOPT_TIMEOUT => 300,
                CURLOPT_CONNECTTIMEOUT => 30, // 连接超时
                CURLOPT_FOLLOWLOCATION => true, // 跟随重定向
                CURLOPT_MAXREDIRS => 5, // 最大重定向次数
                // 兼容 NSS 环境：不再强制置空 CAINFO/CAPATH，避免触发 certpath=none 初始化失败
                CURLOPT_SSL_VERIFYPEER => !$insecureSsl,
                CURLOPT_SSL_VERIFYHOST => $insecureSsl ? 0 : 2,
            ]);

            $result = curl_exec($curl);

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            $errno = curl_errno($curl);
            $info = curl_getinfo($curl);
            
            // 获取调试信息
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            fclose($verbose);

            Log::info("askAi cURL Response Summary:", [
                'http_code' => $httpCode,
                'curl_errno' => $errno,
                'curl_error' => $error,
                'curl_info' => $info,
                'result_length' => strlen($result ?? ''),
                'verbose_log' => $verboseLog
            ]);

            if ($error || $errno !== 0) {
                Log::error("askAi cURL Error Details:", [
                    'error' => $error,
                    'errno' => $errno,
                    'http_code' => $httpCode,
                    'curl_info' => $info,
                    'verbose_log' => $verboseLog
                ]);

                // 根据错误代码提供具体建议
                $userFriendlyError = '';
                switch($errno) {
                    case 77:
                        $userFriendlyError = 'SSL证书初始化失败，请检查服务器SSL配置或联系管理员';
                        break;
                    case 6:
                        $userFriendlyError = '无法解析主机名，请检查网络连接';
                        break;
                    case 7:
                        $userFriendlyError = '无法连接到服务器，请检查网络或服务器状态';
                        break;
                    case 28:
                        $userFriendlyError = '请求超时，请稍后重试';
                        break;
                    default:
                        $userFriendlyError = '网络连接错误 (errno: ' . $errno . ')';
                }

                // 更新对话记录，保存错误信息
                $errorMessage = '发生错误: ' . $userFriendlyError . ' - ' . $error;
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => $errorMessage,
                    'response_data' => [
                        'error' => $error,
                        'errno' => $errno,
                        'http_code' => $httpCode,
                        'curl_info' => $info,
                        'user_friendly_error' => $userFriendlyError
                    ],
                    'answered_at' => now()
                ]);

                echo "data: " . $errorMessage . "\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
            } elseif ($httpCode >= 400) {
                Log::warning("askAi HTTP Error Response:", [
                    'http_code' => $httpCode,
                    'response_body' => substr($result ?? '', 0, 500),
                    'curl_info' => $info
                ]);
            } else {
                try {
                    if (!empty($sessionId) && !empty($user->id)) {
                        $this->pointGrantService->grantByEvent(
                            (int)$user->id,
                            'llm_session_completed',
                            'llm_session',
                            (int)$sessionId,
                            array(
                                'event_key' => 'llm_session_completed:conversation:' . (int)$conversation->id,
                            )
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('grant points on llm chat failed', array(
                        'session_id' => $sessionId,
                        'user_id' => $user->id ?? null,
                        'error' => $e->getMessage(),
                    ));
                }
            }

            curl_close($curl);

            // 发送结束信号
            echo "\ndata: [DONE]\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            @flush();

            exit();
        } catch (\Exception $e) {
            Log::error("askAi Exception Occurred:", [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'session_id' => $sessionId ?? null,
                'agent_id' => $agent->id ?? null
            ]);

            // 尝试更新对话记录，保存错误信息
            if (isset($conversation)) {
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => '发生异常: ' . $e->getMessage(),
                    'response_data' => [
                        'exception' => $e->getMessage(),
                        'exception_class' => get_class($e),
                        'exception_trace' => $e->getTraceAsString()
                    ],
                    'answered_at' => now()
                ]);
            }

            echo "data: 发生异常: " . $e->getMessage() . "\n\n";
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            @flush();

            exit();
        }
    }

    public function askAi(Request $request)
    {
        // Legacy alias for chat endpoint.
        return $this->chat($request);
    }

    protected function conversationSupportsSession()
    {
        if ($this->conversationHasSessionId !== null) {
            return $this->conversationHasSessionId;
        }
        $this->conversationHasSessionId = Schema::hasColumn('llm_conversations', 'session_id');
        return $this->conversationHasSessionId;
    }

    public function getUsageStats(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => '未登录'], 401);
            }

            $query = LlmConversation::where('user_id', $user->id);

            $totalConversations = (int)$query->count();
            $totalTokens = (int)$query->sum('total_tokens');
            $totalCost = (float)$query->sum('cost');
            $todayConversations = (int)LlmConversation::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
                'total_conversations' => $totalConversations,
                'today_conversations' => $todayConversations,
                'total_tokens' => $totalTokens,
                'total_cost' => $totalCost,
            )));
        } catch (\Exception $e) {
            Log::error('获取使用统计失败: ' . $e->getMessage());
            return response()->json(['message' => '获取使用统计失败'], 500);
        }
    }

}
