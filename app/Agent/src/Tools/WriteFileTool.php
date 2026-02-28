<?php

namespace App\Agent\Tools;

/**
 * 文件写入工具
 */
class WriteFileTool extends BaseTool
{
    private string $workspaceDir;

    public function __construct(string $workspaceDir = './workspace')
    {
        $this->workspaceDir = rtrim($workspaceDir, '/');
    }

    public function getName(): string
    {
        return 'write_file';
    }

    public function getDescription(): string
    {
        return 'Write content to a file. Use this when you need to create a new file or overwrite an existing file.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'path' => [
                    'type' => 'string',
                    'description' => 'The path to the file to write'
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'The content to write to the file'
                ]
            ],
            'required' => ['path', 'content']
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        try {
            $arguments = $this->validateArguments($arguments, $this->getParameters());
            $filePath = $this->resolvePath($arguments['path']);
            
            // 确保目录存在
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    return ToolResult::failure("Failed to create directory: " . $directory);
                }
            }
            
            $result = file_put_contents($filePath, $arguments['content']);
            if ($result === false) {
                return ToolResult::failure("Failed to write file: {$arguments['path']}");
            }
            
            return ToolResult::success("Successfully wrote {$result} bytes to {$arguments['path']}");
        } catch (\Exception $e) {
            return ToolResult::failure("Error writing file: " . $e->getMessage());
        }
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            return realpath($this->workspaceDir . '/' . $path) ?: $path;
        }
        
        if (str_starts_with($path, '/')) {
            return $path;
        }
        
        return $this->workspaceDir . '/' . $path;
    }
}