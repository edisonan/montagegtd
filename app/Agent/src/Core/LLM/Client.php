<?php

namespace App\Agent\Core\LLM;

use App\Agent\Schemas\Message;
use App\Agent\Schemas\LlmResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * LLM 客户端抽象类
 * 
 * 提供与各种大语言模型提供商的统一接口。
 */
class Client
{
    /**
     * HTTP 客户端
     *
     * @var \GuzzleHttp\Client
     */
    private Client $httpClient;

    /**
     * API 密钥
     *
     * @var string
     */
    private string $apiKey;

    /**
     * API 基础 URL
     *
     * @var string
     */
    private string $apiBase;

    /**
     * 模型名称
     *
     * @var string
     */
    private string $model;

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     */
    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiBase = $config['api_base'] ?? '';
        $this->model = $config['model'] ?? 'openai/gpt-4';

        $this->httpClient = new Client([
            'timeout' => 300,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PHP-Agent-Framework/0.1.0',
            ],
        ]);
    }

    /**
     * 生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return LlmResponse
     * @throws \Exception
     */
    public function generate(array $messages, ?array $tools = null, ?array $metadata = null): LlmResponse
    {
        $provider = $this->getProvider();
        
        switch ($provider) {
            case 'openai':
                return $this->generateWithOpenAI($messages, $tools, $metadata);
            case 'anthropic':
                return $this->generateWithAnthropic($messages, $tools, $metadata);
            case 'google':
                return $this->generateWithGoogle($messages, $tools, $metadata);
            default:
                throw new \Exception("Unsupported provider: {$provider}");
        }
    }

    /**
     * 流式生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return \Generator
     * @throws \Exception
     */
    public function generateStream(array $messages, ?array $tools = null, ?array $metadata = null): \Generator
    {
        $provider = $this->getProvider();
        
        switch ($provider) {
            case 'openai':
                return $this->generateStreamWithOpenAI($messages, $tools, $metadata);
            case 'anthropic':
                return $this->generateStreamWithAnthropic($messages, $tools, $metadata);
            default:
                throw new \Exception("Streaming not supported for provider: {$provider}");
        }
    }

    /**
     * 使用 OpenAI API 生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return LlmResponse
     * @throws \Exception
     */
    private function generateWithOpenAI(array $messages, ?array $tools = null, ?array $metadata = null): LlmResponse
    {
        $apiBase = $this->apiBase ?: 'https://api.openai.com/v1';
        $modelName = $this->getModelName();

        $payload = [
            'model' => $modelName,
            'messages' => array_map(fn($msg) => $msg instanceof Message ? $msg->toArray() : $msg, $messages),
        ];

        if ($tools) {
            $payload['tools'] = $tools;
        }

        try {
            $response = $this->httpClient->post("{$apiBase}/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
                'json' => $payload,
            ]);

            $data = json_decode((string) $response->getBody(), true);
            return $this->parseOpenAIResponse($data);
        } catch (RequestException $e) {
            throw new \Exception("OpenAI API call failed: " . $e->getMessage());
        }
    }

    /**
     * 使用 OpenAI API 流式生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return \Generator
     * @throws \Exception
     */
    private function generateStreamWithOpenAI(array $messages, ?array $tools = null, ?array $metadata = null): \Generator
    {
        $apiBase = $this->apiBase ?: 'https://api.openai.com/v1';
        $modelName = $this->getModelName();

        $payload = [
            'model' => $modelName,
            'messages' => array_map(fn($msg) => $msg instanceof Message ? $msg->toArray() : $msg, $messages),
            'stream' => true,
        ];

        if ($tools) {
            $payload['tools'] = $tools;
        }

        try {
            $response = $this->httpClient->post("{$apiBase}/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                ],
                'json' => $payload,
            ]);

            $stream = $response->getBody();
            $buffer = '';
            
            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                $buffer .= $chunk;
                
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    
                    if (str_starts_with($line, 'data: ')) {
                        $jsonData = substr($line, 6);
                        
                        if ($jsonData === '[DONE]') {
                            yield ['type' => 'done'];
                            return;
                        }
                        
                        $data = json_decode($jsonData, true);
                        if ($data && isset($data['choices'][0])) {
                            $choice = $data['choices'][0];
                            
                            if (isset($choice['delta']['content'])) {
                                yield [
                                    'type' => 'content_delta',
                                    'delta' => $choice['delta']['content']
                                ];
                            }
                            
                            if (isset($choice['delta']['tool_calls'])) {
                                foreach ($choice['delta']['tool_calls'] as $toolCall) {
                                    yield [
                                        'type' => 'tool_use',
                                        'tool_call' => $toolCall
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        } catch (RequestException $e) {
            throw new \Exception("OpenAI streaming API call failed: " . $e->getMessage());
        }
    }

    /**
     * 使用 Anthropic API 生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return LlmResponse
     * @throws \Exception
     */
    private function generateWithAnthropic(array $messages, ?array $tools = null, ?array $metadata = null): LlmResponse
    {
        $apiBase = $this->apiBase ?: 'https://api.anthropic.com/v1';
        $modelName = $this->getModelName();

        // 转换消息格式为 Anthropic 格式
        $anthropicMessages = $this->convertToAnthropicFormat($messages);

        $payload = [
            'model' => $modelName,
            'messages' => $anthropicMessages,
            'max_tokens' => 4096,
        ];

        if ($tools) {
            $payload['tools'] = $tools;
        }

        try {
            $response = $this->httpClient->post("{$apiBase}/messages", [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                ],
                'json' => $payload,
            ]);

            $data = json_decode((string) $response->getBody(), true);
            return $this->parseAnthropicResponse($data);
        } catch (RequestException $e) {
            throw new \Exception("Anthropic API call failed: " . $e->getMessage());
        }
    }

    /**
     * 使用 Anthropic API 流式生成响应
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return \Generator
     * @throws \Exception
     */
    private function generateStreamWithAnthropic(array $messages, ?array $tools = null, ?array $metadata = null): \Generator
    {
        $apiBase = $this->apiBase ?: 'https://api.anthropic.com/v1';
        $modelName = $this->getModelName();

        $anthropicMessages = $this->convertToAnthropicFormat($messages);

        $payload = [
            'model' => $modelName,
            'messages' => $anthropicMessages,
            'max_tokens' => 4096,
            'stream' => true,
        ];

        if ($tools) {
            $payload['tools'] = $tools;
        }

        try {
            $response = $this->httpClient->post("{$apiBase}/messages", [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                ],
                'json' => $payload,
            ]);

            $stream = $response->getBody();
            $buffer = '';
            
            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                $buffer .= $chunk;
                
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    
                    if (str_starts_with($line, 'event: ')) {
                        $eventType = substr($line, 7);
                        
                        // 读取 data 行
                        $dataLine = '';
                        while (($pos2 = strpos($buffer, "\n")) !== false) {
                            $dataLine = substr($buffer, 0, $pos2);
                            $buffer = substr($buffer, $pos2 + 1);
                            if (str_starts_with($dataLine, 'data: ')) {
                                break;
                            }
                        }
                        
                        if (str_starts_with($dataLine, 'data: ')) {
                            $jsonData = substr($dataLine, 6);
                            $data = json_decode($jsonData, true);
                            
                            if ($data) {
                                yield from $this->parseAnthropicStreamEvent($eventType, $data);
                            }
                        }
                    }
                }
            }
        } catch (RequestException $e) {
            throw new \Exception("Anthropic streaming API call failed: " . $e->getMessage());
        }
    }

    /**
     * 使用 Google API 生成响应 (TODO)
     *
     * @param array $messages 消息数组
     * @param array|null $tools 工具数组
     * @param array|null $metadata 元数据
     * @return LlmResponse
     * @throws \Exception
     */
    private function generateWithGoogle(array $messages, ?array $tools = null, ?array $metadata = null): LlmResponse
    {
        // TODO: 实现 Google Gemini API 调用
        throw new \Exception("Google API not implemented yet");
    }

    /**
     * 解析 OpenAI 响应
     *
     * @param array $data 响应数据
     * @return LlmResponse
     */
    private function parseOpenAIResponse(array $data): LlmResponse
    {
        $choice = $data['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $usage = $data['usage'] ?? [];

        return new LlmResponse(
            $message['content'] ?? '',
            null, // OpenAI 不支持 thinking
            $message['tool_calls'] ?? null,
            new \App\Agent\Schemas\Usage(
                $usage['prompt_tokens'] ?? 0,
                $usage['completion_tokens'] ?? 0
            )
        );
    }

    /**
     * 解析 Anthropic 响应
     *
     * @param array $data 响应数据
     * @return LlmResponse
     */
    private function parseAnthropicResponse(array $data): LlmResponse
    {
        $content = '';
        $thinking = null;
        $toolCalls = null;

        if (isset($data['content'])) {
            foreach ($data['content'] as $item) {
                if ($item['type'] === 'text') {
                    $content = $item['text'];
                } elseif ($item['type'] === 'tool_use') {
                    $toolCalls[] = $item;
                }
            }
        }

        $usage = new \App\Agent\Schemas\Usage(
            $data['usage']['input_tokens'] ?? 0,
            $data['usage']['output_tokens'] ?? 0
        );

        return new LlmResponse($content, $thinking, $toolCalls, $usage);
    }

    /**
     * 解析 Anthropic 流式事件
     *
     * @param string $eventType 事件类型
     * @param array $data 事件数据
     * @return \Generator
     */
    private function parseAnthropicStreamEvent(string $eventType, array $data): \Generator
    {
        switch ($eventType) {
            case 'content_block_delta':
                if (isset($data['delta']['text'])) {
                    yield [
                        'type' => 'content_delta',
                        'delta' => $data['delta']['text']
                    ];
                }
                if (isset($data['delta']['partial_json'])) {
                    yield [
                        'type' => 'thinking_delta',
                        'delta' => $data['delta']['partial_json']
                    ];
                }
                break;
                
            case 'content_block_stop':
                yield ['type' => 'done'];
                break;
                
            case 'message_delta':
                if (isset($data['delta']['stop_reason']) && $data['delta']['stop_reason'] === 'tool_use') {
                    yield ['type' => 'tool_call_complete'];
                }
                break;
        }
    }

    /**
     * 转换消息格式为 Anthropic 格式
     *
     * @param array $messages 消息数组
     * @return array
     */
    private function convertToAnthropicFormat(array $messages): array
    {
        $result = [];
        
        foreach ($messages as $message) {
            if ($message instanceof Message) {
                $message = $message->toArray();
            }
            
            // 过滤掉 system 消息，Anthropic 需要单独处理
            if ($message['role'] === 'system') {
                continue;
            }
            
            $result[] = $message;
        }
        
        return $result;
    }

    /**
     * 获取提供商名称
     *
     * @return string
     */
    private function getProvider(): string
    {
        $parts = explode('/', $this->model);
        return $parts[0];
    }

    /**
     * 获取模型名称（不含提供商前缀）
     *
     * @return string
     */
    private function getModelName(): string
    {
        $parts = explode('/', $this->model);
        return $parts[1] ?? $parts[0];
    }

    /**
     * 设置 API 密钥
     *
     * @param string $apiKey API 密钥
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * 设置 API 基础 URL
     *
     * @param string $apiBase API 基础 URL
     * @return void
     */
    public function setApiBase(string $apiBase): void
    {
        $this->apiBase = $apiBase;
    }

    /**
     * 设置模型
     *
     * @param string $model 模型名称
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}