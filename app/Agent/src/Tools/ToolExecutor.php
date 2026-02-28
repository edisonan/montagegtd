<?php

namespace App\Agent\Tools;

use App\Agent\Schemas\ToolExecutionResult;

/**
 * 工具执行器类
 * 
 * 负责管理和执行各种工具，支持批量执行和并行执行。
 */
class ToolExecutor
{
    /**
     * 工具集合
     *
     * @var array<string, BaseTool>
     */
    private array $tools = [];

    /**
     * 输出长度限制
     *
     * @var int
     */
    private int $outputLimit;

    /**
     * 是否启用并行执行
     *
     * @var bool
     */
    private bool $parallelExecution;

    /**
     * 构造函数
     *
     * @param array|null $tools 工具数组
     * @param int $outputLimit 输出长度限制
     * @param bool $parallelExecution 是否并行执行
     */
    public function __construct(?array $tools = null, int $outputLimit = 10000, bool $parallelExecution = false)
    {
        $this->outputLimit = $outputLimit;
        $this->parallelExecution = $parallelExecution;

        if ($tools) {
            foreach ($tools as $tool) {
                $this->addTool($tool);
            }
        }
    }

    /**
     * 添加工具
     *
     * @param BaseTool $tool 工具实例
     * @return void
     */
    public function addTool(BaseTool $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * 批量设置工具
     *
     * @param array $tools 工具数组
     * @return void
     */
    public function setTools(array $tools): void
    {
        $this->tools = [];
        foreach ($tools as $tool) {
            $this->addTool($tool);
        }
    }

    /**
     * 获取工具
     *
     * @param string $name 工具名称
     * @return BaseTool|null
     */
    public function getTool(string $name): ?BaseTool
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * 检查是否存在指定工具
     *
     * @param string $name 工具名称
     * @return bool
     */
    public function hasTool(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * 获取所有工具名称
     *
     * @return array
     */
    public function getToolNames(): array
    {
        return array_keys($this->tools);
    }

    /**
     * 获取所有工具 Schema
     *
     * @return array
     */
    public function getToolSchemas(): array
    {
        return array_map(fn($tool) => $tool->toSchema(), $this->tools);
    }

    /**
     * 执行单个工具调用
     *
     * @param string $toolCallId 工具调用 ID
     * @param string $functionName 函数名称
     * @param array $arguments 函数参数
     * @return ToolExecutionResult
     */
    public function executeSingle(string $toolCallId, string $functionName, array $arguments): ToolExecutionResult
    {
        $startTime = microtime(true);

        if (!isset($this->tools[$functionName])) {
            $result = ToolResult::failure("Unknown tool: {$functionName}");
        } else {
            try {
                $tool = $this->tools[$functionName];
                $toolResult = $tool->execute($arguments);
                
                if ($toolResult->isSuccess()) {
                    $toolResult = new ToolResult(
                        true,
                        $this->truncateOutput($toolResult->getContent()),
                        null
                    );
                }
                
                $result = $toolResult;
            } catch (\Exception $e) {
                $result = ToolResult::failure("Tool execution failed: " . $e->getMessage());
            }
        }

        $executionTime = microtime(true) - $startTime;

        return new ToolExecutionResult(
            $functionName,
            $toolCallId,
            $result,
            $executionTime,
            $arguments
        );
    }

    /**
     * 批量执行工具调用
     *
     * @param array $toolCalls 工具调用数组 [['id', 'name', 'arguments'], ...]
     * @return array
     */
    public function executeBatch(array $toolCalls): array
    {
        if (empty($toolCalls)) {
            return [];
        }

        // 如果启用并行执行且有多个工具调用
        if ($this->parallelExecution && count($toolCalls) > 1) {
            return $this->executeParallel($toolCalls);
        }

        // 串行执行
        $results = [];
        foreach ($toolCalls as $toolCall) {
            [$callId, $name, $args] = $toolCall;
            $results[] = $this->executeSingle($callId, $name, $args);
        }

        return $results;
    }

    /**
     * 并行执行工具调用
     * TODO: 实现真正的并行执行（需要使用 ReactPHP 或 Amp 等异步库）
     *
     * @param array $toolCalls 工具调用数组
     * @return array
     */
    private function executeParallel(array $toolCalls): array
    {
        // 当前实现仍然是串行的，因为 PHP 原生不支持真正的并行
        // 在实际生产环境中，可以使用 ReactPHP、Amp 或 Swoole 来实现真正的异步并行
        
        $results = [];
        foreach ($toolCalls as $toolCall) {
            [$callId, $name, $args] = $toolCall;
            $results[] = $this->executeSingle($callId, $name, $args);
        }

        return $results;
    }

    /**
     * 截断输出内容
     *
     * @param string $content 原始内容
     * @return string 截断后的内容
     */
    private function truncateOutput(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        if (strlen($content) > $this->outputLimit) {
            return substr($content, 0, $this->outputLimit) . 
                   "\n...[truncated, total " . strlen($content) . " chars]";
        }

        return $content;
    }

    /**
     * 获取工具总数
     *
     * @return int
     */
    public function getCount(): int
    {
        return count($this->tools);
    }

    /**
     * 清空所有工具
     *
     * @return void
     */
    public function clear(): void
    {
        $this->tools = [];
    }
}