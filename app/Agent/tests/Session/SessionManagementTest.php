<?php

namespace App\Agent\Tests\Session;

use PHPUnit\Framework\TestCase;
use App\Agent\Session\SessionManager;
use App\Agent\Session\AgentSession;
use App\Agent\Session\AgentRunRecord;

/**
 * 会话管理测试
 */
class SessionManagementTest extends TestCase
{
    private string $testStoragePath;
    
    protected function setUp(): void
    {
        $this->testStoragePath = __DIR__ . '/test_sessions.json';
        
        // 清理测试文件
        if (file_exists($this->testStoragePath)) {
            unlink($this->testStoragePath);
        }
    }
    
    protected function tearDown(): void
    {
        // 清理测试文件
        if (file_exists($this->testStoragePath)) {
            unlink($this->testStoragePath);
        }
    }
    
    public function testCreateAndGetSession(): void
    {
        $manager = new SessionManager($this->testStoragePath, true);
        
        $session = $manager->getSession('test_session', 'test_agent', 'test_user');
        
        $this->assertInstanceOf(AgentSession::class, $session);
        $this->assertEquals('test_session', $session->getSessionId());
        $this->assertEquals('test_agent', $session->getAgentName());
        $this->assertEquals('test_user', $session->getUserId());
    }
    
    public function testAddRunRecord(): void
    {
        $manager = new SessionManager($this->testStoragePath, true);
        $session = $manager->getSession('test_session', 'test_agent');
        
        $run = new AgentRunRecord(
            'run_123',
            '测试任务',
            '测试响应',
            true,
            3
        );
        
        $result = $manager->addRun('test_session', $run);
        
        $this->assertTrue($result);
        $this->assertEquals(1, $session->getRunsCount());
        
        $runs = $session->getRuns();
        $this->assertCount(1, $runs);
        $this->assertEquals('测试任务', $runs[0]->getTask());
    }
    
    public function testGetHistoryMessages(): void
    {
        $manager = new SessionManager(null, false); // 不使用文件存储进行测试
        $session = $manager->getSession('test_session', 'test_agent');
        
        // 添加多个运行记录
        $run1 = new AgentRunRecord('run_1', '任务1', '响应1', true, 2);
        $run2 = new AgentRunRecord('run_2', '任务2', '响应2', true, 3);
        
        $manager->addRun('test_session', $run1);
        $manager->addRun('test_session', $run2);
        
        // 获取历史消息
        $history = $session->getHistoryMessages(2);
        
        $this->assertCount(4, $history); // 2个任务 + 2个响应
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('任务1', $history[0]['content']);
        $this->assertEquals('assistant', $history[1]['role']);
        $this->assertEquals('响应1', $history[1]['content']);
    }
    
    public function testGetHistoryContext(): void
    {
        $manager = new SessionManager(null, false);
        $session = $manager->getSession('test_session', 'test_agent');
        
        $run = new AgentRunRecord('run_1', '测试任务', '这是一个很长的响应内容...', true, 5);
        $manager->addRun('test_session', $run);
        
        $context = $session->getHistoryContext(1, 100);
        
        $this->assertStringContainsString('<conversation_history>', $context);
        $this->assertStringContainsString('[Round 1]', $context);
        $this->assertStringContainsString('测试任务', $context);
        $this->assertStringContainsString('这是一个很长的响应内容...', $context);
        $this->assertStringContainsString('</conversation_history>', $context);
    }
    
    public function testSessionPersistence(): void
    {
        // 第一次创建并保存
        $manager1 = new SessionManager($this->testStoragePath, true);
        $session1 = $manager1->getSession('persistent_session', 'test_agent');
        
        $run = new AgentRunRecord('run_1', '持久化测试', '响应内容', true, 2);
        $manager1->addRun('persistent_session', $run);
        
        // 第二次从文件加载
        $manager2 = new SessionManager($this->testStoragePath, true);
        $session2 = $manager2->getSession('persistent_session', 'test_agent');
        
        $this->assertEquals(1, $session2->getRunsCount());
        $runs = $session2->getRuns();
        $this->assertEquals('持久化测试', $runs[0]->getTask());
    }
    
    public function testCleanupOldSessions(): void
    {
        $manager = new SessionManager($this->testStoragePath, true);
        
        // 创建会话（模拟旧会话）
        $session = $manager->getSession('old_session', 'test_agent');
        
        // 修改会话的更新时间为很久以前
        $reflection = new \ReflectionClass($session);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setAccessible(true);
        $updatedAtProp->setValue($session, time() - (8 * 86400)); // 8天前
        
        // 重新保存会话
        $manager->getSession('old_session', 'test_agent'); // 这会触发保存
        
        // 清理7天前的会话
        $deletedCount = $manager->cleanupOldSessions(7);
        
        $this->assertEquals(1, $deletedCount);
        $this->assertFalse($manager->hasSession('old_session'));
    }
    
    public function testSessionStats(): void
    {
        $manager = new SessionManager(null, false);
        
        // 创建多个会话
        $session1 = $manager->getSession('session_1', 'agent_1');
        $session2 = $manager->getSession('session_2', 'agent_2');
        
        // 添加运行记录
        $run1 = new AgentRunRecord('run_1', '任务1', '响应1', true, 2);
        $run2 = new AgentRunRecord('run_2', '任务2', '响应2', true, 3);
        $run3 = new AgentRunRecord('run_3', '任务3', '响应3', true, 1);
        
        $manager->addRun('session_1', $run1);
        $manager->addRun('session_1', $run2);
        $manager->addRun('session_2', $run3);
        
        $stats = $manager->getStats();
        
        $this->assertEquals(2, $stats['total_sessions']);
        $this->assertEquals(3, $stats['total_runs']);
        $this->assertGreaterThanOrEqual(0, $stats['oldest_session_age_days']);
    }
}