<?php

namespace App\Services;

use App\Models\LlmModel;
use App\Models\LlmProviderCredential;
use App\Models\LlmUsageLog;
use Illuminate\Support\Facades\Schema;

class LlmStructuredTaskService
{
    public function runTask(string $taskType, array $messages, array $options = array())
    {
        $startedAt = microtime(true);
        if (!$this->hasRequiredTables()) {
            return array(
                'success' => false,
                'content' => null,
                'error' => 'LLM tables not ready',
                'meta' => array(
                    'task_type' => $taskType,
                    'fallback_only' => true,
                ),
            );
        }

        $model = $this->resolveModel($options);
        $credential = $model ? $this->resolveCredential($model, $options) : null;
        $provider = $model ? $model->provider : null;

        $apiKey = $credential ? $credential->getPlainApiKey() : null;
        if (!$model || !$credential || !$provider || empty($provider->base_url) || empty($apiKey)) {
            return array(
                'success' => false,
                'content' => null,
                'error' => '未找到可用的 LLM 模型或凭据，或 API Key 无法读取',
                'meta' => array(
                    'task_type' => $taskType,
                    'fallback_only' => true,
                ),
            );
        }

        $payload = array(
            'model' => $model->name,
            'messages' => $messages,
            'stream' => false,
        );

        if (!empty($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $responseBody = null;
        $status = 'success';
        $errorMessage = null;
        $retryNotes = array();

        try {
            $responseBody = $this->postJson(
                rtrim($provider->base_url, '/') . '/chat/completions',
                $payload,
                array(
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ),
                (int)($options['timeout'] ?? 120)
            );

            if (empty($responseBody['__http_ok'])) {
                $status = 'failed';
                $errorMessage = $responseBody['__error'] ?? 'http request failed';

                if ($status === 'failed' && $this->shouldRetryWithoutResponseFormat($payload, $responseBody)) {
                    unset($payload['response_format']);
                    $retryNotes[] = 'retry_without_response_format';
                    $responseBody = $this->postJson(
                        rtrim($provider->base_url, '/') . '/chat/completions',
                        $payload,
                        array(
                            'Authorization: Bearer ' . $apiKey,
                            'Content-Type: application/json',
                        ),
                        (int)($options['timeout'] ?? 120)
                    );
                    $status = empty($responseBody['__http_ok']) ? 'failed' : 'success';
                    $errorMessage = $status === 'failed' ? ($responseBody['__error'] ?? 'http request failed') : null;
                }
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
        }

        $requestTime = round(microtime(true) - $startedAt, 3);
        $usage = $this->extractUsage($responseBody);
        $content = $this->extractContent($responseBody);

        if (Schema::hasTable('llm_usage_logs')) {
            try {
                LlmUsageLog::create(array(
                    'user_id' => null,
                    'provider_id' => $provider->id,
                    'model_id' => $model->id,
                    'credential_id' => $credential->id,
                    'input_tokens' => $usage['input_tokens'],
                    'output_tokens' => $usage['output_tokens'],
                    'total_tokens' => $usage['total_tokens'],
                    'cost' => $this->estimateCost($model, $usage['input_tokens'], $usage['output_tokens']),
                    'request_time' => $requestTime,
                    'status' => $status,
                    'error_message' => $errorMessage,
                    'request_data' => $this->compactLogData(array(
                        'task_type' => $taskType,
                        'payload' => $payload,
                        'options' => $options,
                        'retry_notes' => $retryNotes,
                    )),
                    'response_data' => $this->compactLogData($responseBody),
                ));
            } catch (\Throwable $e) {
                error_log('llm usage log write failed: ' . $e->getMessage());
            }
        }

        return array(
            'success' => $status === 'success' && !empty($content),
            'content' => $content,
            'error' => $errorMessage,
            'meta' => array(
                'task_type' => $taskType,
                'provider_id' => $provider->id,
                'model_id' => $model->id,
                'credential_id' => $credential->id,
                'model_name' => $payload['model'],
                'configured_model_name' => $model->name,
                'retry_notes' => $retryNotes,
                'request_time' => $requestTime,
                'usage' => $usage,
            ),
        );
    }

    protected function resolveModel(array $options = array())
    {
        if (!empty($options['model_id'])) {
            return LlmModel::with('provider')
                ->where('id', $options['model_id'])
                ->where('is_active', true)
                ->first();
        }

        return LlmModel::with('provider')
            ->where('is_active', true)
            ->where('model_type', 'chat')
            ->orderBy('sort_order', 'asc')
            ->orderBy('provider_id')
            ->orderBy('id')
            ->first();
    }

    protected function compactLogData($data, $maxBytes = 20000)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return array(
                'truncated' => true,
                'error' => 'json encode failed',
            );
        }

        if (strlen($json) <= $maxBytes) {
            return $data;
        }

        return array(
            'truncated' => true,
            'bytes' => strlen($json),
            'preview' => mb_substr($json, 0, $maxBytes),
        );
    }

    protected function shouldRetryOpenRouterFreeAlias($provider, array $payload, $responseBody)
    {
        if (empty($payload['model']) || substr($payload['model'], -5) !== ':free') {
            return false;
        }

        $baseUrl = $provider ? (string)$provider->base_url : '';
        if (stripos($baseUrl, 'openrouter.ai') === false) {
            return false;
        }

        return !empty($responseBody['__error']) && stripos((string)$responseBody['__error'], 'HTTP 404') !== false;
    }

    protected function shouldRetryWithoutResponseFormat(array $payload, $responseBody)
    {
        if (empty($payload['response_format']) || !is_array($responseBody)) {
            return false;
        }

        $json = json_encode($responseBody, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        return stripos($json, 'response_format') !== false
            || stripos($json, 'response format') !== false
            || stripos($json, 'json_object') !== false;
    }

    protected function resolveCredential(LlmModel $model, array $options = array())
    {
        if (!empty($options['credential_id'])) {
            return LlmProviderCredential::where('id', $options['credential_id'])
                ->where('is_active', true)
                ->first();
        }

        return LlmProviderCredential::where('provider_id', $model->provider_id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('id')
            ->first();
    }

    protected function extractContent($responseBody)
    {
        if (!is_array($responseBody)) {
            return null;
        }

        if (isset($responseBody['__body']) && is_array($responseBody['__body'])) {
            $responseBody = $responseBody['__body'];
        }

        return $responseBody['choices'][0]['message']['content'] ?? null;
    }

    protected function extractUsage($responseBody)
    {
        if (is_array($responseBody) && isset($responseBody['__body']) && is_array($responseBody['__body'])) {
            $responseBody = $responseBody['__body'];
        }

        $usage = is_array($responseBody) ? ($responseBody['usage'] ?? array()) : array();

        return array(
            'input_tokens' => (int)($usage['prompt_tokens'] ?? 0),
            'output_tokens' => (int)($usage['completion_tokens'] ?? 0),
            'total_tokens' => (int)($usage['total_tokens'] ?? 0),
        );
    }

    protected function estimateCost(LlmModel $model, int $inputTokens, int $outputTokens)
    {
        $inputCost = ((float)$model->input_price_per_1k) * ($inputTokens / 1000);
        $outputCost = ((float)$model->output_price_per_1k) * ($outputTokens / 1000);

        return round($inputCost + $outputCost, 6);
    }

    protected function hasRequiredTables()
    {
        foreach (array('llm_models', 'llm_provider_credentials', 'llm_providers') as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    protected function postJson($url, array $payload, array $headers, $timeout)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
        ));

        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($errno !== 0) {
            return array(
                '__http_ok' => false,
                '__error' => $error ?: ('curl errno ' . $errno),
                '__body' => null,
            );
        }

        $decoded = json_decode((string)$result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decoded = array('raw' => $result);
        }

        return array(
            '__http_ok' => $httpCode >= 200 && $httpCode < 300,
            '__error' => $httpCode >= 200 && $httpCode < 300 ? null : ('HTTP ' . $httpCode),
            '__body' => $decoded,
        );
    }
}
