<?php

namespace App\Agent\Tests;

use PHPUnit\Framework\TestCase;
use App\Agent\Agent;
use App\Agent\Core\Config;
use App\Agent\Core\LLM\Client as LlmClient;
use App\Agent\Tools\ReadFileTool;
use App\Agent\Tools\WriteFileTool;
use App\Agent\Tools\BashTool;

/**
 * Agent 框架集成测试
 */
class AgentIntegrationTest extends TestCase
{
    private string $testWorkspace;
    
    protected function setUp(): void
    {
        $this->testWorkspace = __DIR__ . '/test_workspace';
        if (!is_dir($this->testWorkspace)) {
            mkdir($this->testWorkspace, 0755, true);
        }
    }
    
    protected function tearDown(): void
    {
        // 清理测试工作目录
        if (is_dir($this->testWorkspace)) {
            $this->removeDirectory($this->testWorkspace);
        }
    }
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    public function testBasicAgentCreation(): void
    {
        $config = new Config([
            'LLM_MODEL' => 'openai/gpt-4',
            'AGENT_MAX_STEPS' => 10
        ]);
        
        $llmClient = $this->createMock(LlmClient::class);
        $agent = new \App\Agent\Core\Agent($llmClient, $config);
        
        $this->assertInstanceOf(\App\Agent\Core\Agent::class, $agent);
        $this->assertEquals('agent', $agent->getName());
    }
    
    public function testAgentWithTools(): void
    {
        $config = new Config([
            'AGENT_WORKSPACE_DIR' => $this->testWorkspace
        ]);
        
        $llmClient = $this->createMock(LlmClient::class);
        
        $tools = [
            new ReadFileTool($this->testWorkspace),
            new WriteFileTool($this->testWorkspace),
            new BashTool($this->testWorkspace)
        ];
        
        $agent = new \App\Agent\Core\Agent($llmClient, $config, $tools);
        
        $this->assertEquals(3, $agent->getToolExecutor()->getCount());
        $this->assertContains('read_file', $agent->getToolExecutor()->getToolNames());
        $this->assertContains('write_file', $agent->getToolExecutor()->getToolNames());
        $this->assertContains('bash', $agent->getToolExecutor()->getToolNames());
    }
    
    public function testMessageHandling(): void
    {
        $config = new Config();
        $llmClient = $this->createMock(LlmClient::class);
        $agent = new \App\Agent\Core\Agent($llmClient, $config);
        
        $agent->addUserMessage('Hello, world!')
              ->addSystemMessage('You are a helpful assistant.');
        
        $messages = $agent->getMessages();
        $this->assertCount(2, $messages);
        
        $this->assertEquals('user', $messages[0]->getRole());
        $this->assertEquals('Hello, world!', $messages[0]->getContent());
        
        $this->assertEquals('system', $messages[1]->getRole());
        $this->assertEquals('You are a helpful assistant.', $messages[1]->getContent());
    }
    
    public function testToolExecution(): void
    {
        $config = new Config(['AGENT_WORKSPACE_DIR' => $this->testWorkspace]);
        $llmClient = $this->createMock(LlmClient::class);
        
        $readTool = new ReadFileTool($this->testWorkspace);
        $writeTool = new WriteFileTool($this->testWorkspace);
        
        $executor = new \App\Agent\Tools\ToolExecutor([$readTool, $writeTool]);
        
        // 测试写入文件
        $writeResult = $executor->executeSingle(
            'test_call_1',
            'write_file',
            ['path' => 'test.txt', 'content' => 'Hello, World!']
        );
        
        $this->assertTrue($writeResult->isSuccess());
        $this->assertStringContainsString('Successfully wrote', $writeResult->getContent());
        
        // 测试读取文件
        $readResult = $executor->executeSingle(
            'test_call_2',
            'read_file',
            ['path' => 'test.txt']
        );
        
        $this->assertTrue($readResult->isSuccess());
        $this->assertEquals('Hello, World!', $readResult->getContent());
        
        // 测试读取不存在的文件
        $notFoundResult = $executor->executeSingle(
            'test_call_3',
            'read_file',
            ['path' => 'nonexistent.txt']
        );
        
        $this->assertFalse($notFoundResult->isSuccess());
        $this->assertStringContainsString('File not found', $notFoundResult->getError());
    }
    
    public function testEventSystem(): void
    {
        $config = new Config();
        $llmClient = $this->createMock(LlmClient::class);
        $agent = new \App\Agent\Core\Agent($llmClient, $config);
        
        $eventCount = 0;
        $agent->on('test_event', function ($event, $data) use (&$eventCount) {
            $eventCount++;
            $this->assertEquals('test_value', $data['test_key']);
        });
        
        $agent->getEventEmitter()->emit('test_event', ['test_key' => 'test_value']);
        $this->assertEquals(1, $eventCount);
    }
    
    public function testAgentState(): void
    {
        $state = new \App\Agent\Core\AgentState(10);
        
        $this->assertEquals(\App\Agent\Core\AgentStatus::IDLE, $state->getStatus());
        $this->assertEquals(0, $state->getCurrentStep());
        $this->assertEquals(10, $state->getMaxSteps());
        
        $state->resetForRun();
        $this->assertEquals(\App\Agent\Core\AgentStatus::RUNNING, $state->getStatus());
        
        $state->incrementStep();
        $this->assertEquals(1, $state->getCurrentStep());
        
        $state->addTokens(100, 50);
        $this->assertEquals(100, $state->getTotalInputTokens());
        $this->assertEquals(50, $state->getTotalOutputTokens());
        $this->assertEquals(150, $state->getTotalTokens());
    }
}