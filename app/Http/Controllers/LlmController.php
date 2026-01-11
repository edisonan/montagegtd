<?php

namespace App\Http\Controllers;

use App\Models\LlmModel;
use App\Models\LlmProviderCredential;
use App\Models\LlmProvider;
use App\Models\LlmConversation;
use App\Services\LlmPolishService;
use App\Services\LlmConversationService;
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

    public function __construct(LlmConversationService $conversationService)
    {
        $this->middleware('auth');
        
        $this->conversationService = $conversationService;
    }

    public function askAi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referText' => 'string|max:5000',
            'query' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // 直接使用 $request->input() 获取数据
        $query = $request->input('query');
        $referText = $request->input('referText', '');

        try {
            $user = Auth::user();
            $credential = LlmProviderCredential::where('user_id', $user->id)
                ->where('is_active', 1)
                ->first();

            if (!$credential) {
                return response()->json(['error' => '未找到有效的API凭据'], 400);
            }

            $provider = LLMProvider::where('id', $credential->provider_id)->first();

            $model = LlmModel::where('provider_id', $credential->provider_id)
//                ->where('status', 1)
                ->first();

            if (!$model) {
                return response()->json(['error' => '未找到有效的模型'], 400);
            }

            // 记录请求数据
            $requestData = [
                'model' => $model->name,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $query."\n引用文本：\n".$referText,
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
}