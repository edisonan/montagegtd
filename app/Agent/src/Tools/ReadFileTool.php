<?php

namespace App\Agent\Tools;

/**
 * 文件读取工具
 */
class ReadFileTool extends BaseTool
{
    private string $workspaceDir;

    public function __construct(string $workspaceDir = './workspace')
    {
        $this->workspaceDir = rtrim($workspaceDir, '/');
    }

    public function getName(): string
    {
        return 'read_file';
    }

    public function getDescription(): string
    {
        return 'Read the contents of a file. Use this when you need to examine the contents of an existing file.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'path' => [
                    'type' => 'string',
                    'description' => 'The path to the file to read'
                ]
            ],
            'required' => ['path']
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        try {
            $arguments = $this->validateArguments($arguments, $this->getParameters());
            $filePath = $this->resolvePath($arguments['path']);
            
            if (!file_exists($filePath)) {
                return ToolResult::failure("File not found: {$arguments['path']}");
            }
            
            if (!is_readable($filePath)) {
                return ToolResult::failure("File is not readable: {$arguments['path']}");
            }
            
            $content = file_get_contents($filePath);
            if ($content === false) {
                return ToolResult::failure("Failed to read file: {$arguments['path']}");
            }
            
            return ToolResult::success($content);
        } catch (\Exception $e) {
            return ToolResult::failure("Error reading file: " . $e->getMessage());
        }
    }

    private function resolvePath(string $path): string
    {
        // 处理相对路径
        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            return realpath($this->workspaceDir . '/' . $path) ?: $path;
        }
        
        // 处理绝对路径
        if (str_starts_with($path, '/')) {
            return $path;
        }
        
        // 相对于工作目录
        return $this->workspaceDir . '/' . $path;
    }
}