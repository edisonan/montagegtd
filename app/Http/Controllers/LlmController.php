<?php

namespace App\Http\Controllers;

use App\Http\Utils\ResponseDataUtil;
use App\Models\LlmAgent;
use App\Models\LlmAgentVersion;
use App\Models\LlmModel;
use App\Models\LlmProvider;
use App\Models\LlmConversation;
use App\Models\LlmChatAttachment;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

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
            
            return response()->json(ResponseDataUtil::genSimpleSucc($provider));
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
                'api_type' => 'required|in:openai,anthropic,google,azure,custom',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'rate_limit_per_minute' => 'nullable|integer|min:0',
                'concurrent_limit' => 'nullable|integer|min:0',
                'api_key' => 'nullable|string',
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

            // 留空表示更新时保留现有 Provider Key。
            if ($request->filled('api_key')) {
                $providerData['api_key'] = trim($request->input('api_key'));
            }
            
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
            
            return response()->json(ResponseDataUtil::genSimpleSucc());
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
            
            return response()->json(ResponseDataUtil::genSimpleSucc($model));
        } catch (\Exception $e) {
            Log::error('获取模型详情失败: ' . $e->getMessage());
            return response()->json(['message' => '获取模型详情失败'], 500);
        }
    }

    public function saveModel(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            
            $rules = [
                'provider_id' => 'required|exists:llm_providers,id',
                'name' => 'required|string|max:100',
                'display_name' => 'nullable|string|max:100',
                'model_type' => 'required|in:chat,completion,embedding,image,audio',
                'context_length' => 'nullable|integer|min:0',
                'max_tokens' => 'nullable|integer|min:0',
                'input_price_per_1k' => 'nullable|numeric|min:0',
                'output_price_per_1k' => 'nullable|numeric|min:0',
                'capabilities' => 'nullable|array',
                'sort_order' => 'integer|min:0',
                'is_active' => 'boolean'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                Log::warning('保存模型参数校验失败', array('errors' => $errors));
                return response()->json(
                    ResponseDataUtil::genFail(ResponseDataUtil::COMMON_ERROR, '参数校验失败', $errors),
                    422
                );
            }

            $modelData = $request->only(array(
                'provider_id',
                'name',
                'display_name',
                'model_type',
                'context_length',
                'max_tokens',
                'input_price_per_1k',
                'output_price_per_1k',
                'capabilities',
                'sort_order',
                'is_active'
            ));
            
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
                
                $model->update($modelData);
            } else {
                $modelData['user_id'] = $user->id;
                $model = LlmModel::create($modelData);
            }
            
            return response()->json(ResponseDataUtil::genSimpleSucc($model));
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
            
            return response()->json(ResponseDataUtil::genSimpleSucc());
        } catch (\Exception $e) {
            Log::error('删除模型失败: ' . $e->getMessage());
            return response()->json(['message' => '删除模型失败'], 500);
        }
    }

    private function resolveProviderApiKey(LlmProvider $provider)
    {
        return $provider->getPlainApiKey();
    }

    private function curlJsonRequest($url, $method, array $headers, $payload = null, $timeout = 20)
    {
        $curl = curl_init();
        $responseHeaders = array();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$responseHeaders) {
                $responseHeaders[] = trim($header);
                return strlen($header);
            },
        );

        if ($payload !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        if (filter_var(env('LLM_CURL_INSECURE', false), FILTER_VALIDATE_BOOLEAN)) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = null;
        if ($body !== false && $body !== '') {
            $decoded = json_decode($body, true);
        }

        return array(
            'ok' => $errno === 0 && $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'errno' => $errno,
            'error' => $errno ? $error : null,
            'body' => $decoded,
            'raw_body' => is_string($body) ? substr($body, 0, 1000) : null,
            'headers' => $responseHeaders,
        );
    }

    private function testOpenAiCompatibleProvider(LlmProvider $provider, $apiKey, LlmModel $model = null)
    {
        $baseUrl = rtrim($provider->base_url, '/');
        if (!$baseUrl || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('供应商 API 基础 URL 未配置或格式不正确');
        }

        if ($model) {
            $payload = array(
                'model' => $model->name,
                'messages' => array(
                    array('role' => 'user', 'content' => 'ping')
                ),
                'stream' => false,
                'max_tokens' => 1,
            );

            return $this->curlJsonRequest(
                $baseUrl . '/chat/completions',
                'POST',
                array(
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ),
                $payload
            );
        }

        return $this->curlJsonRequest(
            $baseUrl . '/models',
            'GET',
            array(
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json',
            )
        );
    }

    private function testAnthropicProvider(LlmProvider $provider, $apiKey, LlmModel $model = null)
    {
        if (!$model) {
            throw new \RuntimeException('Anthropic 凭据测试需要先添加一个模型');
        }

        $baseUrl = rtrim($provider->base_url, '/');
        if (!$baseUrl || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('供应商 API 基础 URL 未配置或格式不正确');
        }

        return $this->curlJsonRequest(
            $baseUrl . '/messages',
            'POST',
            array(
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json',
                'Accept: application/json',
            ),
            array(
                'model' => $model->name,
                'messages' => array(
                    array('role' => 'user', 'content' => 'ping')
                ),
                'max_tokens' => 1,
            )
        );
    }

    private function testProviderRequest(LlmProvider $provider, $apiKey, LlmModel $model = null)
    {
        if ($provider->api_type === 'anthropic') {
            return $this->testAnthropicProvider($provider, $apiKey, $model);
        }

        return $this->testOpenAiCompatibleProvider($provider, $apiKey, $model);
    }

    private function getProviderTestError(array $result)
    {
        if (!empty($result['error'])) {
            return $result['error'];
        }

        if (isset($result['body']['error']['message'])) {
            return $result['body']['error']['message'];
        }

        if (isset($result['body']['message'])) {
            return $result['body']['message'];
        }

        return '供应商请求失败';
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

            $apiKey = $this->resolveProviderApiKey($provider);

            if (!$apiKey) {
                return response()->json([
                    'code' => 1004,
                    'msg' => '未配置该供应商的 API Key，请先在供应商中填写',
                    'result' => array(
                        'provider_id' => $provider->id,
                        'provider_name' => $provider->name,
                    ),
                ]);
            }

            $testResult = $this->testProviderRequest($provider, $apiKey, $model);
            if (empty($testResult['ok'])) {
                Log::warning('测试模型供应商请求失败', array(
                    'model_id' => $model->id,
                    'provider_id' => $provider->id,
                    'status_code' => $testResult['status_code'],
                    'error' => $this->getProviderTestError($testResult),
                ));

                return response()->json([
                    'code' => 1005,
                    'msg' => '模型请求失败: ' . $this->getProviderTestError($testResult),
                    'result' => array(
                        'status_code' => $testResult['status_code'],
                        'model_id' => $model->id,
                        'model_name' => $model->name,
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
                'status_code' => $testResult['status_code'],
                'msg' => '模型真实请求测试通过',
            )));
        } catch (\Exception $e) {
            Log::error('测试模型失败: ' . $e->getMessage());
            return response()->json([
                'code' => 1005,
                'msg' => '模型测试失败: ' . $e->getMessage(),
                'result' => array(),
            ], 500);
        }
    }

    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'nullable',
            'session_id' => 'nullable|integer',
            'refer_text' => 'nullable|string',
            'query' => 'required|string|max:8000',
            'generation_id' => 'nullable|string|max:80',
            'attachment_ids' => 'nullable|array|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $query = $request->input('query');
        $referText = $request->input('refer_text', '');
        $sessionId = $request->input('session_id', '');
        $generationId = $request->input('generation_id') ?: bin2hex(random_bytes(16));
        $generationCacheKey = null;
        $wasStopped = false;

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

        if (!$agentVersion) {
            return response()->json([
                'success' => false,
                'message' => '智能体尚未配置可用版本'
            ], 400);
        }

        try {
            $user = Auth::user();
            $generationCacheKey = $this->generationCacheKey($user->id, $generationId);
            Cache::forget($generationCacheKey);
            ignore_user_abort(true);
            $attachmentIds = array_values(array_unique(array_map('intval', $request->input('attachment_ids', []))));
            $attachments = collect();
            if (!empty($attachmentIds)) {
                $attachments = LlmChatAttachment::where('user_id', $user->id)
                    ->whereIn('id', $attachmentIds)
                    ->whereNull('conversation_id')
                    ->where(function ($query) use ($session) {
                        $query->whereNull('session_id')->orWhere('session_id', $session->id);
                    })
                    ->where('status', 'ready')
                    ->get();
                if ($attachments->count() !== count($attachmentIds)) {
                    return response()->json(['success' => false, 'message' => '附件不存在、已发送或无权访问'], 422);
                }
            }
            if($agent->user_id != $user->id) {
                $model = LlmModel::with('provider')
                    ->where('is_active', 1)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if (!$model) {
                    return response()->json(['error' => '未找到有效的模型'], 400);
                }

                $provider = $model->provider;
            } else {
                $model = LlmModel::with('provider')->where('id', $agentVersion->model_id)
                    ->first();

                $provider = $model ? $model->provider : null;
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

            $apiKey = $this->resolveProviderApiKey($provider);
            if (!$apiKey) {
                return response()->json(['error' => '未配置该供应商的 API Key'], 400);
            }
            $sessionHasPdf = $this->hasPdfAttachments($attachments)
                || LlmChatAttachment::where('session_id', $session->id)->where('extension', 'pdf')->exists();
            $isOpenRouter = stripos((string)$provider->name, 'openrouter') !== false
                || stripos((string)$provider->base_url, 'openrouter.ai') !== false;
            if ($sessionHasPdf && !$isOpenRouter) {
                return response()->json(['success' => false, 'message' => '当前仅 OpenRouter 供应商支持 PDF 对话'], 422);
            }

            $systemContent = $agentVersion->system_prompt;
            if(!empty($referText)) {
                $systemContent = $systemContent."\n引用文本：\n" .$referText;
            }

            // 组装系统提示词和最近会话历史，让页面具备真正的多轮上下文能力。
            $messages = [];
            if (!empty($systemContent)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $systemContent,
                ];
            }

            if ($sessionId && $this->conversationSupportsSession()) {
                foreach ($this->buildConversationHistory((int)$sessionId) as $historyMessage) {
                    $messages[] = $historyMessage;
                }
            }

            $currentUserContent = $this->buildMessageContent($query, $attachments);
            if ((int)$session->message_count === 0 && in_array($session->title, ['新对话', '未命名对话', '未命名会话'], true)) {
                $session->title = $this->makeSessionTitle($query, $attachments);
                $session->save();
            }
            $messages[] = [
                'role' => 'user',
                'content' => $currentUserContent,
            ];

            $requestData = [
                'model' => $model->name,
                'messages' => $messages,
                'stream' => true
            ];
            if ($this->messagesContainPdf($messages)) {
                $requestData['plugins'] = [[
                    'id' => 'file-parser',
                    'pdf' => ['engine' => 'cloudflare-ai'],
                ]];
            }

            if ($agentVersion->temperature !== null) {
                $requestData['temperature'] = (float)$agentVersion->temperature;
            }
            if ($agentVersion->top_p !== null) {
                $requestData['top_p'] = (float)$agentVersion->top_p;
            }
            if (!empty($agentVersion->max_tokens)) {
                $requestData['max_tokens'] = (int)$agentVersion->max_tokens;
            }

            // 设置流式响应头
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // 禁用Nginx缓冲

            // 先创建对话记录
            $conversationPayload = [
                'user_id' => $user->id,
                'model_id' => $model->id,
                'question' => $query,
                'request_data' => array_merge($this->sanitizeRequestData($requestData), ['attachment_ids' => $attachmentIds]),
                'answer' => '' // 初始化为空，后续填充
            ];
            if ($sessionId && $this->conversationSupportsSession()) {
                $conversationPayload['session_id'] = (int)$sessionId;
            }
            $conversation = $this->conversationService->createConversation($conversationPayload);
            if (!$attachments->isEmpty()) {
                LlmChatAttachment::whereIn('id', $attachments->pluck('id')->all())
                    ->update([
                        'session_id' => (int)$session->id,
                        'conversation_id' => (int)$conversation->id,
                        'updated_at' => now(),
                    ]);
            }

            // 记录详细的请求信息
            Log::info("askAi cURL Request Details:", [
                'provider_base_url' => $provider->base_url,
                'full_request_url' => $provider->base_url . "/chat/completions",
                'request_headers' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . substr($apiKey, 0, 10) . '...', // 只记录前10位
                    'Accept: text/event-stream',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive'
                ],
                'request_body' => $this->sanitizeRequestData($requestData),
                'timeout' => 300
            ]);

            $curl = curl_init();
            $completeAnswer = '';
            $streamBuffer = '';
            $usage = [];
            $lastStopCheckAt = 0.0;
            $stopRequested = function () use (&$lastStopCheckAt, &$wasStopped, $generationCacheKey) {
                $now = microtime(true);
                if (($now - $lastStopCheckAt) < 0.25) {
                    return $wasStopped;
                }
                $lastStopCheckAt = $now;
                $wasStopped = (bool)Cache::get($generationCacheKey, false);
                return $wasStopped;
            };
            
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
                    'Authorization: Bearer ' . $apiKey,
                    'Accept: text/event-stream',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive'
                ],
                CURLOPT_WRITEFUNCTION => function($ch, $data) use (&$completeAnswer, &$streamBuffer, &$usage, $stopRequested) {
                    if ($stopRequested()) {
                        return 0;
                    }
                    // 记录接收到的数据
                    Log::debug("cURL WRITEFUNCTION data received:", [
                        'data_length' => strlen($data),
                        'data_preview' => substr($data, 0, 200), // 只记录前200字符
                        'complete_answer_length' => strlen($completeAnswer ?? '')
                    ]);
                    
                    // 同步解析并累计答案，保证刷新页面后仍能恢复完整回复。
                    $streamBuffer .= str_replace("\r\n", "\n", $data);
                    while (($separatorPosition = strpos($streamBuffer, "\n\n")) !== false) {
                        $event = substr($streamBuffer, 0, $separatorPosition);
                        $streamBuffer = substr($streamBuffer, $separatorPosition + 2);
                        $completeAnswer .= $this->extractSseEventContent($event);
                        $eventUsage = $this->extractSseEventUsage($event);
                        if (!empty($eventUsage)) {
                            $usage = $eventUsage;
                        }
                    }

                    // 直接输出供应商的 SSE 数据，让前端实时渲染。
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
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => function ($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($stopRequested) {
                    return $stopRequested() ? 1 : 0;
                },
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

            if ($streamBuffer !== '') {
                $completeAnswer .= $this->extractSseEventContent($streamBuffer);
                $eventUsage = $this->extractSseEventUsage($streamBuffer);
                if (!empty($eventUsage)) {
                    $usage = $eventUsage;
                }
                $streamBuffer = '';
            }

            if ($wasStopped) {
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => $completeAnswer,
                    'response_data' => [
                        'status' => 'stopped',
                        'model' => $model->name,
                        'generation_id' => $generationId,
                    ],
                    'prompt_tokens' => (int)($usage['prompt_tokens'] ?? 0),
                    'completion_tokens' => (int)($usage['completion_tokens'] ?? 0),
                    'total_tokens' => (int)($usage['total_tokens'] ?? 0),
                    'answered_at' => now(),
                ]);
                $session->message_count = LlmConversation::where('session_id', $session->id)->count();
                $session->last_message_at = now();
                $session->save();
                echo "data: " . json_encode([
                    'type' => 'stopped',
                    'conversation_id' => $conversation->id,
                ], JSON_UNESCAPED_UNICODE) . "\n\n";
            } elseif ($error || $errno !== 0) {
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
                $errorMessage = '模型请求失败（HTTP ' . $httpCode . '）';
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => $errorMessage,
                    'response_data' => [
                        'http_code' => $httpCode,
                        'response_body' => substr($result ?? '', 0, 2000),
                    ],
                    'answered_at' => now(),
                ]);
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $errorMessage,
                ], JSON_UNESCAPED_UNICODE) . "\n\n";
            } else {
                $this->conversationService->updateConversation($conversation->id, [
                    'answer' => $completeAnswer,
                    'response_data' => [
                        'http_code' => $httpCode,
                        'model' => $model->name,
                        'generation_id' => $generationId,
                    ],
                    'prompt_tokens' => (int)($usage['prompt_tokens'] ?? 0),
                    'completion_tokens' => (int)($usage['completion_tokens'] ?? 0),
                    'total_tokens' => (int)($usage['total_tokens'] ?? 0),
                    'answered_at' => now(),
                ]);

                $session->message_count = LlmConversation::where('session_id', $session->id)->count();
                $session->last_message_at = now();
                $session->save();

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
            echo "data: " . json_encode([
                'type' => 'done',
                'conversation_id' => $conversation->id,
                'stopped' => $wasStopped,
                'usage' => $usage,
                'session_title' => $session->title,
            ], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "\ndata: [DONE]\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            @flush();

            Cache::forget($generationCacheKey);
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

            if ($generationCacheKey) {
                Cache::forget($generationCacheKey);
            }
            exit();
        }
    }

    public function stopChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'generation_id' => 'required|string|max:80',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '缺少有效的生成标识'], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未登录'], 401);
        }

        Cache::put($this->generationCacheKey($user->id, $request->input('generation_id')), true, 5);

        return response()->json(['success' => true]);
    }

    public function askAi(Request $request)
    {
        // Legacy alias for chat endpoint.
        return $this->chat($request);
    }

    /**
     * 从一个 SSE event 中提取 OpenAI 兼容响应的文本增量。
     */
    protected function extractSseEventContent($event)
    {
        $content = '';
        $lines = preg_split('/\r?\n/', (string)$event);

        foreach ($lines as $line) {
            if (strpos($line, 'data:') !== 0) {
                continue;
            }

            $raw = trim(substr($line, 5));
            if ($raw === '' || $raw === '[DONE]') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (isset($decoded['choices'][0]['delta']['content'])) {
                    $content .= (string)$decoded['choices'][0]['delta']['content'];
                } elseif (isset($decoded['choices'][0]['message']['content'])) {
                    $content .= (string)$decoded['choices'][0]['message']['content'];
                } elseif (isset($decoded['content']) && is_string($decoded['content'])) {
                    $content .= $decoded['content'];
                }
                continue;
            }

            // 兼容少数供应商直接输出纯文本 data 行。
            if (substr($raw, 0, 1) !== '{' && substr($raw, 0, 1) !== '[') {
                $content .= $raw;
            }
        }

        return $content;
    }

    protected function extractSseEventUsage($event)
    {
        foreach (preg_split('/\r?\n/', (string)$event) as $line) {
            if (strpos($line, 'data:') !== 0) {
                continue;
            }
            $decoded = json_decode(trim(substr($line, 5)), true);
            if (is_array($decoded) && !empty($decoded['usage']) && is_array($decoded['usage'])) {
                return $decoded['usage'];
            }
        }
        return [];
    }

    protected function buildConversationHistory($sessionId, $maxCharacters = 48000, $maxTurns = 24)
    {
        $history = [];
        $characters = 0;
        $conversations = LlmConversation::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->limit($maxTurns)
            ->get();
        $attachmentsByConversation = collect();
        if (!$conversations->isEmpty()) {
            $attachmentsByConversation = LlmChatAttachment::whereIn('conversation_id', $conversations->pluck('id')->all())
                ->where('status', 'ready')
                ->get()
                ->groupBy('conversation_id');
        }

        foreach ($conversations as $conversation) {
            $turn = [];
            if (!empty($conversation->question)) {
                $conversationAttachments = $attachmentsByConversation->get($conversation->id, collect());
                $question = mb_substr($conversation->question, 0, 12000);
                $turn[] = ['role' => 'user', 'content' => $this->buildMessageContent($question, $conversationAttachments)];
                $characters += mb_strlen($question . $this->formatAttachmentsForPrompt($conversationAttachments));
            }
            if (!empty($conversation->answer)) {
                $answer = mb_substr($conversation->answer, 0, 24000);
                $turn[] = ['role' => 'assistant', 'content' => $answer];
                $characters += mb_strlen($answer);
            }
            if ($characters > $maxCharacters && !empty($history)) {
                break;
            }
            $history = array_merge($turn, $history);
        }

        return $history;
    }

    protected function formatAttachmentsForPrompt($attachments, $maxCharacters = 30000)
    {
        if (!$attachments || $attachments->isEmpty()) {
            return '';
        }

        $parts = [];
        $used = 0;
        foreach ($attachments as $attachment) {
            if ($attachment->extension === 'pdf') {
                continue;
            }
            $remaining = $maxCharacters - $used;
            if ($remaining <= 0) {
                break;
            }
            $content = mb_substr((string)$attachment->extracted_text, 0, $remaining);
            $parts[] = "[附件：{$attachment->original_name}]\n" . $content;
            $used += mb_strlen($content);
        }

        return empty($parts)
            ? ''
            : "\n\n以下是用户提供的附件内容，请结合问题回答：\n\n" . implode("\n\n", $parts);
    }

    protected function buildMessageContent($query, $attachments)
    {
        $textContent = $query . $this->formatAttachmentsForPrompt($attachments);
        if (!$this->hasPdfAttachments($attachments)) {
            return $textContent;
        }

        $content = [['type' => 'text', 'text' => $textContent]];
        foreach ($attachments as $attachment) {
            if ($attachment->extension !== 'pdf') {
                continue;
            }
            $path = storage_path('app/' . $attachment->storage_path);
            if (!is_file($path)) {
                throw new \RuntimeException('PDF 附件文件不存在');
            }
            $content[] = [
                'type' => 'file',
                'file' => [
                    'filename' => $attachment->original_name,
                    'file_data' => 'data:application/pdf;base64,' . base64_encode(file_get_contents($path)),
                ],
            ];
        }
        return $content;
    }

    protected function hasPdfAttachments($attachments)
    {
        return $attachments && $attachments->contains(function ($attachment) {
            return $attachment->extension === 'pdf';
        });
    }

    protected function messagesContainPdf(array $messages)
    {
        foreach ($messages as $message) {
            if (empty($message['content']) || !is_array($message['content'])) {
                continue;
            }
            foreach ($message['content'] as $part) {
                if (isset($part['type']) && $part['type'] === 'file') {
                    return true;
                }
            }
        }
        return false;
    }

    protected function sanitizeRequestData(array $requestData)
    {
        $sanitized = $requestData;
        if (empty($sanitized['messages'])) {
            return $sanitized;
        }
        foreach ($sanitized['messages'] as &$message) {
            if (!isset($message['content']) || !is_array($message['content'])) {
                continue;
            }
            foreach ($message['content'] as &$part) {
                if (isset($part['file']['file_data'])) {
                    $part['file']['file_data'] = '[PDF data omitted]';
                }
            }
            unset($part);
        }
        unset($message);
        return $sanitized;
    }

    protected function makeSessionTitle($query, $attachments)
    {
        $title = trim(preg_replace('/\s+/u', ' ', (string)$query));
        if (($title === '' || $title === '请阅读并分析这些附件。') && $attachments && !$attachments->isEmpty()) {
            $title = pathinfo($attachments->first()->original_name, PATHINFO_FILENAME);
        }
        $title = preg_replace('/^[\s，。！？!?：:；;]+|[\s，。！？!?：:；;]+$/u', '', $title);
        if ($title === '') {
            $title = '新对话';
        }
        return mb_strlen($title) > 32 ? mb_substr($title, 0, 32) . '…' : $title;
    }

    protected function generationCacheKey($userId, $generationId)
    {
        return 'llm_generation_stop:' . (int)$userId . ':' . sha1((string)$generationId);
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
