<?php

namespace App\Agent\Tools;

/**
 * Bash 命令执行工具
 */
class BashTool extends BaseTool
{
    private string $workspaceDir;

    public function __construct(string $workspaceDir = './workspace')
    {
        $this->workspaceDir = rtrim($workspaceDir, '/');
    }

    public function getName(): string
    {
        return 'bash';
    }

    public function getDescription(): string
    {
        return 'Execute bash commands in the workspace directory. Use with caution as it can modify the system.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'command' => [
                    'type' => 'string',
                    'description' => 'The bash command to execute'
                ]
            ],
            'required' => ['command']
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        try {
            $arguments = $this->validateArguments($arguments, $this->getParameters());
            
            // 安全检查：拒绝危险命令
            $dangerousPatterns = [
                'rm -rf', 'rm -r', 'rm -f',
                'sudo', 'su ',
                '> /dev/', '>> /dev/',
                'mkfs', 'fdisk', 'dd'
            ];
            
            $command = $arguments['command'];
            foreach ($dangerousPatterns as $pattern) {
                if (stripos($command, $pattern) !== false) {
                    return ToolResult::failure("Command blocked for security reasons: {$pattern}");
                }
            }
            
            // 切换到工作目录并执行命令
            $fullCommand = "cd " . escapeshellarg($this->workspaceDir) . " && " . $command;
            
            // 设置超时和限制
            $descriptors = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w']  // stderr
            ];
            
            $process = proc_open($fullCommand, $descriptors, $pipes);
            
            if (!is_resource($process)) {
                return ToolResult::failure("Failed to execute command");
            }
            
            // 关闭 stdin
            fclose($pipes[0]);
            
            // 读取输出
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            // 等待进程结束并获取退出码
            $exitCode = proc_close($process);
            
            if ($exitCode !== 0) {
                return ToolResult::failure("Command failed with exit code {$exitCode}: " . ($error ?: $output));
            }
            
            $result = $output ?: "Command executed successfully (no output)";
            return ToolResult::success($this->formatOutput($result, 5000));
            
        } catch (\Exception $e) {
            return ToolResult::failure("Error executing command: " . $e->getMessage());
        }
    }
}