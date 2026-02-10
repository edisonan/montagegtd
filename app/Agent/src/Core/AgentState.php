<?php

namespace App\Agent\Core;

use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Schemas\Message;
use App\Agent\Schemas\UserInputRequest;
use App\Agent\Tools\ToolExecutor;
use App\Agent\Tools\BaseTool;

/**
 * Agent 状态枚举
 */
class AgentStatus
{
    public const IDLE = 'idle';
    public const RUNNING = 'running';
    public const WAITING_INPUT = 'waiting_input';
    public const COMPLETED = 'completed';
    public const ERROR = 'error';
}

/**
 * Agent 状态类
 * 
 * 管理 Agent 的运行时状态，包括执行状态、消息历史、token 统计等。
 */
class AgentState
{
    /**
     * Agent 状态
     *
     * @var string
     */
    private string $status;

    /**
     * 当前步骤
     *
     * @var int
     */
    private int $currentStep;

    /**
     * 最大步骤数
     *
     * @var int
     */
    private int $maxSteps;

    /**
     * 输入 token 总数
     *
     * @var int
     */
    private int $totalInputTokens;

    /**
     * 输出 token 总数
     *
     * @var int
     */
    private int $totalOutputTokens;

    /**
     * 消息历史
     *
     * @var array
     */
    private array $messages;

    /**
     * 等待的用户输入请求
     *
     * @var UserInputRequest|null
     */
    private ?UserInputRequest $pendingUserInput;

    /**
     * 暂停的工具调用 ID
     *
     * @var string|null
     */
    private ?string $pausedToolCallId;

    /**
     * 错误信息
     *
     * @var string|null
     */
    private ?string $errorMessage;

    /**
     * 最后检查点 ID
     *
     * @var string|null
     */
    private ?string $lastCheckpointId;

    /**
     * 线程 ID
     *
     * @var string|null
     */
    private ?string $threadId;

    /**
     * 构造函数
     *
     * @param int $maxSteps 最大步骤数
     */
    public function __construct(int $maxSteps = 50)
    {
        $this->reset();
        $this->maxSteps = $maxSteps;
    }

    /**
     * 重置状态
     *
     * @param bool $preserveMessages 是否保留消息历史
     * @return void
     */
    public function reset(bool $preserveMessages = false): void
    {
        $this->status = AgentStatus::IDLE;
        $this->currentStep = 0;
        $this->totalInputTokens = 0;
        $this->totalOutputTokens = 0;
        $this->pendingUserInput = null;
        $this->pausedToolCallId = null;
        $this->errorMessage = null;
        
        if (!$preserveMessages) {
            $this->messages = [];
            $this->lastCheckpointId = null;
        }
    }

    /**
     * 为新运行重置状态
     *
     * @param bool $preserveMessages 是否保留消息历史
     * @return void
     */
    public function resetForRun(bool $preserveMessages = false): void
    {
        $this->status = AgentStatus::RUNNING;
        $this->currentStep = 0;
        $this->totalInputTokens = 0;
        $this->totalOutputTokens = 0;
        $this->pendingUserInput = null;
        $this->pausedToolCallId = null;
        $this->errorMessage = null;
        
        if (!$preserveMessages) {
            $this->lastCheckpointId = null;
        }
    }

    /**
     * 增加步骤计数
     *
     * @return int
     */
    public function incrementStep(): int
    {
        $this->currentStep++;
        return $this->currentStep;
    }

    /**
     * 添加 token 统计
     *
     * @param int $inputTokens 输入 token 数
     * @param int $outputTokens 输出 token 数
     * @return void
     */
    public function addTokens(int $inputTokens, int $outputTokens): void
    {
        $this->totalInputTokens += $inputTokens;
        $this->totalOutputTokens += $outputTokens;
    }

    /**
     * 标记等待用户输入
     *
     * @param UserInputRequest $request 用户输入请求
     * @param string $toolCallId 工具调用 ID
     * @return void
     */
    public function markWaitingInput(UserInputRequest $request, string $toolCallId): void
    {
        $this->status = AgentStatus::WAITING_INPUT;
        $this->pendingUserInput = $request;
        $this->pausedToolCallId = $toolCallId;
    }

    /**
     * 标记完成
     *
     * @return void
     */
    public function markCompleted(): void
    {
        $this->status = AgentStatus::COMPLETED;
        $this->pendingUserInput = null;
        $this->pausedToolCallId = null;
    }

    /**
     * 标记错误
     *
     * @param string $message 错误信息
     * @return void
     */
    public function markError(string $message): void
    {
        $this->status = AgentStatus::ERROR;
        $this->errorMessage = $message;
    }

    /**
     * 从用户输入恢复
     *
     * @return void
     */
    public function resumeFromInput(): void
    {
        if ($this->status === AgentStatus::WAITING_INPUT) {
            $this->status = AgentStatus::RUNNING;
            $this->pendingUserInput = null;
            $this->pausedToolCallId = null;
        }
    }

    /**
     * 从检查点恢复
     *
     * @return void
     */
    public function resumeFromCheckpoint(): void
    {
        if (in_array($this->status, [AgentStatus::IDLE, AgentStatus::COMPLETED, AgentStatus::ERROR])) {
            $this->status = AgentStatus::RUNNING;
            $this->errorMessage = null;
        }
    }

    /**
     * 获取状态
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * 获取当前步骤
     *
     * @return int
     */
    public function getCurrentStep(): int
    {
        return $this->currentStep;
    }

    /**
     * 获取最大步骤数
     *
     * @return int
     */
    public function getMaxSteps(): int
    {
        return $this->maxSteps;
    }

    /**
     * 获取总输入 token 数
     *
     * @return int
     */
    public function getTotalInputTokens(): int
    {
        return $this->totalInputTokens;
    }

    /**
     * 获取总输出 token 数
     *
     * @return int
     */
    public function getTotalOutputTokens(): int
    {
        return $this->totalOutputTokens;
    }

    /**
     * 获取总 token 数
     *
     * @return int
     */
    public function getTotalTokens(): int
    {
        return $this->totalInputTokens + $this->totalOutputTokens;
    }

    /**
     * 获取消息历史
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * 添加消息
     *
     * @param Message $message 消息
     * @return void
     */
    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * 设置消息历史
     *
     * @param array $messages 消息数组
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->messages = array_map(function ($msg) {
            return $msg instanceof Message ? $msg : Message::fromArray($msg);
        }, $messages);
    }

    /**
     * 获取等待的用户输入请求
     *
     * @return UserInputRequest|null
     */
    public function getPendingUserInput(): ?UserInputRequest
    {
        return $this->pendingUserInput;
    }

    /**
     * 获取暂停的工具调用 ID
     *
     * @return string|null
     */
    public function getPausedToolCallId(): ?string
    {
        return $this->pausedToolCallId;
    }

    /**
     * 获取错误信息
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * 获取最后检查点 ID
     *
     * @return string|null
     */
    public function getLastCheckpointId(): ?string
    {
        return $this->lastCheckpointId;
    }

    /**
     * 设置最后检查点 ID
     *
     * @param string|null $id 检查点 ID
     * @return void
     */
    public function setLastCheckpointId(?string $id): void
    {
        $this->lastCheckpointId = $id;
    }

    /**
     * 获取线程 ID
     *
     * @return string|null
     */
    public function getThreadId(): ?string
    {
        return $this->threadId;
    }

    /**
     * 设置线程 ID
     *
     * @param string|null $id 线程 ID
     * @return void
     */
    public function setThreadId(?string $id): void
    {
        $this->threadId = $id;
    }

    /**
     * 检查是否正在运行
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->status === AgentStatus::RUNNING;
    }

    /**
     * 检查是否等待用户输入
     *
     * @return bool
     */
    public function isWaitingInput(): bool
    {
        return $this->status === AgentStatus::WAITING_INPUT;
    }

    /**
     * 检查是否已完成
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === AgentStatus::COMPLETED;
    }

    /**
     * 检查是否出错
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->status === AgentStatus::ERROR;
    }

    /**
     * 检查是否可以继续执行
     *
     * @return bool
     */
    public function canContinue(): bool
    {
        return $this->status === AgentStatus::RUNNING && $this->currentStep < $this->maxSteps;
    }

    /**
     * 转换为数组格式（用于检查点）
     *
     * @return array
     */
    public function toCheckpointData(): array
    {
        return [
            'step' => $this->currentStep,
            'status' => $this->status,
            'messages' => array_map(fn($msg) => $msg->toArray(), $this->messages),
            'input_tokens' => $this->totalInputTokens,
            'output_tokens' => $this->totalOutputTokens,
            'pending_user_input' => $this->pendingUserInput?->toArray(),
            'paused_tool_call_id' => $this->pausedToolCallId,
            'error_message' => $this->errorMessage,
        ];
    }

    /**
     * 从检查点数据创建状态实例
     *
     * @param array $checkpointData 检查点数据
     * @param int $maxSteps 最大步骤数
     * @return self
     */
    public static function fromCheckpointData(array $checkpointData, int $maxSteps = 50): self
    {
        $state = new self($maxSteps);
        
        $state->currentStep = $checkpointData['step'] ?? 0;
        $state->status = $checkpointData['status'] ?? AgentStatus::IDLE;
        $state->totalInputTokens = $checkpointData['input_tokens'] ?? 0;
        $state->totalOutputTokens = $checkpointData['output_tokens'] ?? 0;
        $state->pausedToolCallId = $checkpointData['paused_tool_call_id'] ?? null;
        $state->errorMessage = $checkpointData['error_message'] ?? null;
        
        if (isset($checkpointData['messages'])) {
            $state->setMessages($checkpointData['messages']);
        }
        
        if (isset($checkpointData['pending_user_input'])) {
            $state->pendingUserInput = UserInputRequest::fromArray($checkpointData['pending_user_input']);
        }
        
        return $state;
    }
}