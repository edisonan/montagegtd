<?php

namespace Agent\Skills\Examples;

use Agent\Skills\Skill;
use Agent\Core\AgentContext;

/**
 * 文件处理技能示例
 * 演示文件操作相关功能
 */
class FileProcessingSkill extends Skill
{
    public function __construct()
    {
        parent::__construct(
            'file_processing',
            '文件处理和分析技能',
            array(
                'max_file_size' => 1024 * 1024, // 1MB
                'allowed_extensions' => array('txt', 'csv', 'json', 'xml')
            )
        );
        
        $this->setVersion('1.0.0');
        $this->setAuthor('Agent Framework Team');
        $this->setRequiredTools(array('file_read', 'file_write'));
    }

    public function execute($context, $parameters = array())
    {
        $this->log("开始执行文件处理技能", 'info');
        
        $action = isset($parameters['action']) ? $parameters['action'] : '';
        $filePath = isset($parameters['file_path']) ? $parameters['file_path'] : '';
        
        if (empty($action) || empty($filePath)) {
            throw new \Exception("缺少必要参数: action 或 file_path");
        }
        
        // 验证文件
        if (!$this->validateFile($filePath)) {
            throw new \Exception("文件验证失败: {$filePath}");
        }
        
        $toolExecutor = $context->getToolExecutor();
        $result = null;
        
        switch ($action) {
            case 'analyze':
                $result = $this->analyzeFile($toolExecutor, $filePath);
                break;
                
            case 'convert':
                $result = $this->convertFile($toolExecutor, $filePath, $parameters);
                break;
                
            case 'summarize':
                $result = $this->summarizeFile($toolExecutor, $filePath);
                break;
                
            default:
                throw new \Exception("不支持的操作: {$action}");
        }
        
        $this->log("文件处理完成", 'info');
        
        return array(
            'action' => $action,
            'file_path' => $filePath,
            'result' => $result,
            'timestamp' => time()
        );
    }

    private function validateFile($filePath)
    {
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 检查文件大小
        $maxSize = $this->getConfig('max_file_size');
        if (filesize($filePath) > $maxSize) {
            return false;
        }
        
        // 检查文件扩展名
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $allowedExtensions = $this->getConfig('allowed_extensions');
        
        return in_array($extension, $allowedExtensions);
    }

    private function analyzeFile($toolExecutor, $filePath)
    {
        $content = $toolExecutor->execute('file_read', array('path' => $filePath));
        
        $analysis = array(
            'file_size' => filesize($filePath),
            'line_count' => substr_count($content, "\n") + 1,
            'word_count' => str_word_count($content),
            'character_count' => strlen($content),
            'encoding' => mb_detect_encoding($content)
        );
        
        return $analysis;
    }

    private function convertFile($toolExecutor, $filePath, $parameters)
    {
        $targetFormat = isset($parameters['target_format']) ? $parameters['target_format'] : 'json';
        $content = $toolExecutor->execute('file_read', array('path' => $filePath));
        
        $convertedContent = '';
        
        switch ($targetFormat) {
            case 'json':
                // 简单的 CSV 转 JSON 示例
                if (pathinfo($filePath, PATHINFO_EXTENSION) === 'csv') {
                    $lines = explode("\n", trim($content));
                    $headers = str_getcsv($lines[0]);
                    $data = array();
                    
                    for ($i = 1; $i < count($lines); $i++) {
                        if (!empty(trim($lines[$i]))) {
                            $row = str_getcsv($lines[$i]);
                            $data[] = array_combine($headers, $row);
                        }
                    }
                    
                    $convertedContent = json_encode($data, JSON_PRETTY_PRINT);
                } else {
                    $convertedContent = json_encode(array('content' => $content), JSON_PRETTY_PRINT);
                }
                break;
                
            case 'uppercase':
                $convertedContent = strtoupper($content);
                break;
                
            case 'lowercase':
                $convertedContent = strtolower($content);
                break;
                
            default:
                throw new \Exception("不支持的目标格式: {$targetFormat}");
        }
        
        // 保存转换后的文件
        $outputPath = $filePath . '.' . $targetFormat;
        $toolExecutor->execute('file_write', array(
            'path' => $outputPath,
            'content' => $convertedContent
        ));
        
        return array(
            'original_file' => $filePath,
            'converted_file' => $outputPath,
            'format' => $targetFormat
        );
    }

    private function summarizeFile($toolExecutor, $filePath)
    {
        $content = $toolExecutor->execute('file_read', array('path' => $filePath));
        
        // 简单的摘要生成
        $lines = explode("\n", $content);
        $firstLines = array_slice($lines, 0, 5);
        $lastLines = array_slice($lines, -3);
        
        return array(
            'total_lines' => count($lines),
            'preview' => array(
                'first_lines' => implode("\n", $firstLines),
                'last_lines' => implode("\n", $lastLines)
            ),
            'file_info' => array(
                'name' => basename($filePath),
                'size' => filesize($filePath) . ' bytes'
            )
        );
    }

    public function validateParameters($parameters)
    {
        if (!is_array($parameters)) {
            return false;
        }
        
        // 必需参数检查
        if (!isset($parameters['action']) || !isset($parameters['file_path'])) {
            return false;
        }
        
        if (!is_string($parameters['action']) || !is_string($parameters['file_path'])) {
            return false;
        }
        
        // 操作类型验证
        $validActions = array('analyze', 'convert', 'summarize');
        if (!in_array($parameters['action'], $validActions)) {
            return false;
        }
        
        return true;
    }

    public function getUsageInstructions()
    {
        return "使用方法: \n" .
               "必需参数: \n" .
               "- action: 操作类型 (analyze|convert|summarize)\n" .
               "- file_path: 文件路径\n" .
               "\n可选参数:\n" .
               "- target_format: 目标格式 (convert 操作时使用)\n" .
               "\n示例: \n" .
               "{'action': 'analyze', 'file_path': '/path/to/file.txt'}\n" .
               "{'action': 'convert', 'file_path': '/path/to/data.csv', 'target_format': 'json'}";
    }
}