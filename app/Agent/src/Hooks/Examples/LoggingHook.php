<?php

namespace Agent\Hooks\Examples;

use Agent\Hooks\AgentHook;
use Agent\Core\AgentContext;

/**
 * 日志记录 Hook 示例
 * 记录 Agent 执行过程中的关键事件
 */
class LoggingHook extends AgentHook
{
    private $logLevel;

    public function __construct($priority = 50)
    {
        parent::__construct('logging_hook', $priority);
        $this->logLevel = 'info';
    }

    public function onAgentInitialize($context)
    {
        $this->log("Agent 初始化开始", 'info');
    }

    public function onAgentInitialized($context)
    {
        $this->log("Agent 初始化完成", 'info');
    }

    public function onLoopStart($context, $step)
    {
        $this->log("执行循环开始 - 步骤 {$step}", 'debug');
    }

    public function onLoopEnd($context, $step, $result)
    {
        $this->log("执行循环结束 - 步骤 {$step}", 'debug');
    }

    public function onToolCall($context, $toolName, $arguments)
    {
        $this->log("调用工具: {$toolName}", 'info');
        return null;
    }

    public function onToolResult($context, $toolName, $arguments, $result)
    {
        $this->log("工具 {$toolName} 执行完成", 'info');
        return $result;
    }

    public function onLlmCall($context, $messages, $options)
    {
        $messageCount = count($messages);
        $this->log("调用 LLM - 消息数: {$messageCount}", 'info');
        return null;
    }

    public function onLlmResponse($context, $messages, $options, $response)
    {
        $this->log("LLM 响应接收完成", 'info');
        return $response;
    }

    public function onException($context, $exception)
    {
        $this->log("发生异常: " . $exception->getMessage(), 'error');
        return true; // 继续抛出异常
    }

    public function onAgentTerminate($context, $reason)
    {
        $this->log("Agent 终止 - 原因: {$reason}", 'info');
    }

    private function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}";
        
        // 输出到错误日志
        error_log($logMessage);
        
        // 也可以写入文件
        $logFile = sys_get_temp_dir() . '/agent_hooks.log';
        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function getDescription()
    {
        return "记录 Agent 执行过程的日志 Hook";
    }

    public function validateConfig($config)
    {
        if (isset($config['log_level'])) {
            $validLevels = array('debug', 'info', 'warning', 'error');
            if (!in_array($config['log_level'], $validLevels)) {
                return false;
            }
            $this->logLevel = $config['log_level'];
        }
        return true;
    }
}