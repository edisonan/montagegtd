<?php

namespace Agent\Hooks\Examples;

use Agent\Hooks\AgentHook;
use Agent\Core\AgentContext;

/**
 * 性能监控 Hook 示例
 * 监控 Agent 执行性能和资源使用情况
 */
class PerformanceHook extends AgentHook
{
    private $startTime;
    private $stepTimes;
    private $toolTimes;
    private $llmTimes;

    public function __construct($priority = 75)
    {
        parent::__construct('performance_hook', $priority);
        $this->resetMetrics();
    }

    private function resetMetrics()
    {
        $this->startTime = microtime(true);
        $this->stepTimes = array();
        $this->toolTimes = array();
        $this->llmTimes = array();
    }

    public function onAgentInitialize($context)
    {
        $this->startTime = microtime(true);
        $this->logMetric('agent_start', array('timestamp' => time()));
    }

    public function onLoopStart($context, $step)
    {
        $this->stepTimes[$step] = array(
            'start' => microtime(true),
            'tools' => array(),
            'llm_calls' => array()
        );
    }

    public function onLoopEnd($context, $step, $result)
    {
        if (isset($this->stepTimes[$step])) {
            $this->stepTimes[$step]['end'] = microtime(true);
            $this->stepTimes[$step]['duration'] = 
                $this->stepTimes[$step]['end'] - $this->stepTimes[$step]['start'];
            
            $this->logMetric('step_performance', array(
                'step' => $step,
                'duration' => $this->stepTimes[$step]['duration'],
                'tool_count' => count($this->stepTimes[$step]['tools']),
                'llm_count' => count($this->stepTimes[$step]['llm_calls'])
            ));
        }
    }

    public function onToolCall($context, $toolName, $arguments)
    {
        $toolStartTime = microtime(true);
        $this->toolTimes[] = array(
            'name' => $toolName,
            'start' => $toolStartTime,
            'step' => $this->getCurrentStep()
        );
        return null;
    }

    public function onToolResult($context, $toolName, $arguments, $result)
    {
        $toolEndTime = microtime(true);
        
        // 找到对应的工具调用记录
        for ($i = count($this->toolTimes) - 1; $i >= 0; $i--) {
            if ($this->toolTimes[$i]['name'] === $toolName && 
                !isset($this->toolTimes[$i]['end'])) {
                $this->toolTimes[$i]['end'] = $toolEndTime;
                $this->toolTimes[$i]['duration'] = 
                    $toolEndTime - $this->toolTimes[$i]['start'];
                
                // 记录到当前步骤
                $currentStep = $this->getCurrentStep();
                if (isset($this->stepTimes[$currentStep])) {
                    $this->stepTimes[$currentStep]['tools'][] = array(
                        'name' => $toolName,
                        'duration' => $this->toolTimes[$i]['duration']
                    );
                }
                
                $this->logMetric('tool_performance', array(
                    'tool' => $toolName,
                    'duration' => $this->toolTimes[$i]['duration'],
                    'step' => $currentStep
                ));
                break;
            }
        }
        
        return $result;
    }

    public function onLlmCall($context, $messages, $options)
    {
        $llmStartTime = microtime(true);
        $this->llmTimes[] = array(
            'start' => $llmStartTime,
            'message_count' => count($messages),
            'step' => $this->getCurrentStep()
        );
        return null;
    }

    public function onLlmResponse($context, $messages, $options, $response)
    {
        $llmEndTime = microtime(true);
        
        if (!empty($this->llmTimes)) {
            $lastCall = &$this->llmTimes[count($this->llmTimes) - 1];
            if (!isset($lastCall['end'])) {
                $lastCall['end'] = $llmEndTime;
                $lastCall['duration'] = $llmEndTime - $lastCall['start'];
                
                // 记录到当前步骤
                $currentStep = $this->getCurrentStep();
                if (isset($this->stepTimes[$currentStep])) {
                    $this->stepTimes[$currentStep]['llm_calls'][] = array(
                        'duration' => $lastCall['duration'],
                        'message_count' => $lastCall['message_count']
                    );
                }
                
                $this->logMetric('llm_performance', array(
                    'duration' => $lastCall['duration'],
                    'message_count' => $lastCall['message_count'],
                    'step' => $currentStep
                ));
            }
        }
        
        return $response;
    }

    public function onAgentTerminate($context, $reason)
    {
        $totalTime = microtime(true) - $this->startTime;
        
        // 计算统计数据
        $toolStats = $this->calculateToolStats();
        $llmStats = $this->calculateLlmStats();
        $stepStats = $this->calculateStepStats();
        
        $performanceData = array(
            'total_duration' => $totalTime,
            'total_steps' => count($this->stepTimes),
            'tool_stats' => $toolStats,
            'llm_stats' => $llmStats,
            'step_stats' => $stepStats,
            'termination_reason' => $reason
        );
        
        $this->logMetric('agent_performance_summary', $performanceData);
        $this->resetMetrics();
    }

    private function getCurrentStep()
    {
        return count($this->stepTimes);
    }

    private function calculateToolStats()
    {
        if (empty($this->toolTimes)) {
            return array('count' => 0);
        }
        
        $durations = array_filter(array_column($this->toolTimes, 'duration'));
        if (empty($durations)) {
            return array('count' => 0);
        }
        
        return array(
            'count' => count($this->toolTimes),
            'total_time' => array_sum($durations),
            'average_time' => array_sum($durations) / count($durations),
            'max_time' => max($durations),
            'min_time' => min($durations)
        );
    }

    private function calculateLlmStats()
    {
        if (empty($this->llmTimes)) {
            return array('count' => 0);
        }
        
        $durations = array_filter(array_column($this->llmTimes, 'duration'));
        $messageCounts = array_column($this->llmTimes, 'message_count');
        
        return array(
            'count' => count($this->llmTimes),
            'total_time' => array_sum($durations),
            'average_time' => !empty($durations) ? array_sum($durations) / count($durations) : 0,
            'total_messages' => array_sum($messageCounts),
            'average_messages' => array_sum($messageCounts) / count($messageCounts)
        );
    }

    private function calculateStepStats()
    {
        if (empty($this->stepTimes)) {
            return array('count' => 0);
        }
        
        $durations = array_filter(array_column($this->stepTimes, 'duration'));
        if (empty($durations)) {
            return array('count' => 0);
        }
        
        return array(
            'count' => count($this->stepTimes),
            'total_time' => array_sum($durations),
            'average_time' => array_sum($durations) / count($durations),
            'max_time' => max($durations),
            'min_time' => min($durations)
        );
    }

    private function logMetric($metricName, $data)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logData = array(
            'timestamp' => $timestamp,
            'metric' => $metricName,
            'data' => $data
        );
        
        $logFile = sys_get_temp_dir() . '/agent_performance.jsonl';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function getDescription()
    {
        return "监控 Agent 性能指标的 Hook";
    }

    public function validateConfig($config)
    {
        return true;
    }
}