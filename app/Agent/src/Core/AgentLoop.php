<?php

namespace App\Agent\Core;

use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Schemas\Message;
use App\Agent\Schemas\UserInputRequest;
use App\Agent\Tools\ToolExecutor;
use App\Agent\Events\EventEmitter;

/**
 * Agent 执行循环配置类
 */
class LoopConfig
{
    public int $maxSteps;
    public bool $parallelTools;
    public ?CheckpointConfig $checkpoint;
    public $onToolResult;

    public function __construct(
        int $maxSteps = 50,
        bool $parallelTools = false,
        ?CheckpointConfig $checkpoint = null,
        $onToolResult = null
    ) {
        $this->maxSteps = $maxSteps;
        $this->parallelTools = $parallelTools;
        $this->checkpoint = $checkpoint;
        $this->onToolResult = $onToolResult;
    }
}

/**
 * 单步执行结果类
 */
class StepResult
{
    public bool $completed;
    public bool $waitingInput;
    public string $content;
    public ?string $error;

    public function __construct(
        bool $completed = false,
        bool $waitingInput = false,
        string $content = '',
        ?string $error = null
    ) {
        $this->completed = $completed;
        $this->waitingInput = $waitingInput;
        $this->content = $content;
        $this->error = $error;
    }
}

/**
 * Agent 执行循环类
 * 
 * 核心执行引擎，负责协调 LLM 调用、工具执行和状态管理。
 */
class AgentLoop
{
    private LlmClient $llm;
    private ToolExecutor $toolExecutor;
    private EventEmitter $events;
    private LoopConfig $config;
    private string $agentId;
    private array $toolSchemas;

    public function __construct(
        LlmClient $llmClient,
        ToolExecutor $toolExecutor,
        EventEmitter $eventEmitter,
        ?LoopConfig $config = null,
        ?string $agentId = null
    ) {
        $this->llm = $llmClient;
        $this->toolExecutor = $toolExecutor;
        $this->events = $eventEmitter;
        $this->config = $config ?? new LoopConfig();
        $this->agentId = $agentId ?? uniqid('agent_', true);
        $this->toolSchemas = [];
    }

    /**
     * 设置工具
     *
     * @param array $tools 工具数组
     * @return void
     */
    public function setTools(array $tools): void
    {
        $this->toolExecutor->setTools($tools);
        $this->toolSchemas = array_map(fn($tool) => $tool->toSchema(), $tools);
    }

    /**
     * 获取工具 Schema
     *
     * @return array
     */
    public function getToolSchemas(): array
    {
        return $this->toolSchemas;
    }

    /**
     * 运行 Agent
     *
     * @param AgentState $state Agent 状态
     * @param array|null $metadata 元数据
     * @return string 执行结果
     */
    public function run(AgentState $state, ?array $metadata = null): string
    {
        $state->resetForRun();
        $state->setMaxSteps($this->config->maxSteps);

        // 触发开始事件
        $this->events->emit('before_run', ['state' => $state, 'step' => 0]);

        while ($state->canContinue()) {
            $state->incrementStep();
            
            $result = $this->executeStep($state, $metadata);

            // 触发步骤事件
            $this->events->emit('on_step', [
                'state' => $state,
                'step' => $state->getCurrentStep(),
                'result' => $result
            ]);

            if ($result->completed) {
                $state->markCompleted();
                $this->events->emit('completion', [
                    'message' => $result->content,
                    'total_steps' => $state->getCurrentStep(),
                    'total_tokens' => $state->getTotalTokens()
                ]);
                return $result->content;
            }

            if ($result->waitingInput) {
                return "Waiting for user input";
            }

            if ($result->error) {
                $state->markError($result->error);
                $this->events->emit('error', ['message' => $result->error]);
                return $result->error;
            }
        }

        $errorMsg = "Task couldn't be completed after {$this->config->maxSteps} steps.";
        $state->markError($errorMsg);
        $this->events->emit('error', ['message' => $errorMsg, 'reason' => 'max_steps_reached']);
        return $errorMsg;
    }

    /**
     * 执行单步
     *
     * @param AgentState $state Agent 状态
     * @param array|null $metadata 元数据
     * @return StepResult 步骤结果
     */
    private function executeStep(AgentState $state, ?array $metadata): StepResult
    {
        // 发送步骤开始事件
        $this->events->emit('step_start', [
            'step' => $state->getCurrentStep(),
            'tokens' => $state->getTotalTokens(),
            'max_steps' => $this->config->maxSteps
        ]);

        try {
            // 调用 LLM
            $response = $this->llm->generate(
                $state->getMessages(),
                $this->toolSchemas,
                $metadata
            );
        } catch (\Exception $e) {
            return new StepResult(error: "LLM call failed: " . $e->getMessage());
        }

        // 更新 token 统计
        if ($response->getUsage()) {
            $state->addTokens(
                $response->getInputTokens(),
                $response->getOutputTokens()
            );
        }

        // 发送 LLM 响应事件
        $this->events->emit('llm_response', [
            'content' => $response->getContent(),
            'has_tool_calls' => $response->hasToolCalls(),
            'tool_count' => $response->getToolCallCount(),
            'input_tokens' => $response->getInputTokens(),
            'output_tokens' => $response->getOutputTokens()
        ]);

        // 添加助手消息
        $assistantMsg = Message::createAssistant(
            $response->getContent(),
            $response->getThinking(),
            $response->getToolCalls()
        );
        $state->addMessage($assistantMsg);

        // 检查是否有工具调用
        if (!$response->hasToolCalls()) {
            return new StepResult(completed: true, content: $response->getContent());
        }

        // 处理工具调用
        $toolCalls = $response->getToolCalls();
        
        // 检查用户输入请求
        foreach ($toolCalls as $toolCall) {
            if ($this->isUserInputToolCall($toolCall)) {
                $inputFields = $this->parseUserInputFields($toolCall['function']['arguments']);
                $request = new UserInputRequest(
                    $toolCall['id'],
                    $inputFields,
                    $toolCall['function']['arguments']['context'] ?? null
                );
                
                $state->markWaitingInput($request, $toolCall['id']);
                
                $this->events->emit('user_input_required', [
                    'tool_call_id' => $toolCall['id'],
                    'fields' => $inputFields,
                    'context' => $toolCall['function']['arguments']['context'] ?? null
                ]);

                return new StepResult(waitingInput: true);
            }
        }

        // 执行工具调用
        $toolCallsData = array_map(function ($call) {
            return [
                $call['id'],
                $call['function']['name'],
                $call['function']['arguments']
            ];
        }, $toolCalls);

        $results = $this->toolExecutor->executeBatch($toolCallsData);

        // 处理工具执行结果
        foreach ($results as $execResult) {
            $this->events->emit('tool_end', [
                'tool' => $execResult->getToolName(),
                'tool_call_id' => $execResult->getToolCallId(),
                'success' => $execResult->isSuccess(),
                'content' => $execResult->getContent(),
                'error' => $execResult->getError(),
                'execution_time' => $execResult->getExecutionTime()
            ]);

            $toolContent = $execResult->isSuccess() 
                ? $execResult->getContent() 
                : "Error: " . $execResult->getError();

            $toolMsg = Message::createTool(
                $toolContent,
                $execResult->getToolCallId(),
                $execResult->getToolName()
            );
            $state->addMessage($toolMsg);

            // 调用工具结果回调
            if ($this->config->onToolResult) {
                call_user_func(
                    $this->config->onToolResult,
                    $execResult->getToolCallId(),
                    $execResult->getToolName(),
                    $execResult->getArguments(),
                    $toolContent
                );
            }
        }

        // 发送步骤结束事件
        $this->events->emit('step_end', [
            'tools_executed' => count($results)
        ]);

        return new StepResult();
    }

    /**
     * 检查是否为用户输入工具调用
     *
     * @param array $toolCall 工具调用
     * @return bool
     */
    private function isUserInputToolCall(array $toolCall): bool
    {
        return $toolCall['function']['name'] === 'get_user_input';
    }

    /**
     * 解析用户输入字段
     *
     * @param array $arguments 参数
     * @return array
     */
    private function parseUserInputFields(array $arguments): array
    {
        $fields = [];
        if (isset($arguments['user_input_fields']) && is_array($arguments['user_input_fields'])) {
            foreach ($arguments['user_input_fields'] as $fieldData) {
                $fields[] = [
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'field_description' => $fieldData['field_description']
                ];
            }
        }
        return $fields;
    }

    /**
     * 从用户输入恢复执行
     *
     * @param AgentState $state Agent 状态
     * @param array $userResponse 用户响应
     * @param array|null $metadata 元数据
     * @return string 执行结果
     */
    public function resumeFromInput(AgentState $state, array $userResponse, ?array $metadata = null): string
    {
        if (!$state->isWaitingInput() || !$state->getPausedToolCallId()) {
            return "Agent is not waiting for user input";
        }

        $toolMsg = Message::createTool(
            json_encode($userResponse, JSON_UNESCAPED_UNICODE),
            $state->getPausedToolCallId(),
            'get_user_input'
        );
        
        $state->addMessage($toolMsg);
        $state->resumeFromInput();

        return $this->run($state, $metadata);
    }
}