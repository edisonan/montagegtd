# PHP Agent Framework

基于 PHP 的 AI Agent 框架，完全复刻自 omni_agent Python 框架。

## 特性

- 🤖 **多模型支持** - 支持 OpenAI, Anthropic, Google Gemini 等 100+ 模型
- 🔧 **工具执行** - 内置文件操作、Bash 命令、用户输入等工具
- 💾 **会话管理** - 持久化会话存储，支持历史上下文和 Laravel 集成
- 🧠 **记忆系统** - 用户画像、任务跟踪、习惯学习（待完善）
- ⚡ **事件驱动** - 完整的事件分发机制
- 🔄 **断点续传** - 支持任务中断后恢复执行（待完善）
- 🎯 **技能系统** - 可扩展的技能加载机制（待完善）
- 📊 **可观测性** - 详细的执行日志和追踪

## 安装

```bash
cd /path/to/your/project
composer require task-gitee/agent-framework
```

## 快速开始

```php
<?php

require_once 'vendor/autoload.php';

use App\Agent\Agent;

// 创建 Agent 实例
$agent = Agent::create([
    'model' => 'openai/gpt-4',
    'api_key' => getenv('OPENAI_API_KEY')
]);

// 执行任务
$result = $agent->run('帮我写一个快速排序算法');
echo $result;
```

## 配置

创建 `.env` 文件：

```env
LLM_API_KEY=your-api-key-here
LLM_MODEL=openai/gpt-4
DEBUG=true
```

## 核心组件

### Agent 核心
- `Core\Agent` - 主要 Agent 类
- `Core\AgentLoop` - 执行循环引擎
- `Core\LLM\Client` - LLM 客户端抽象

### 工具系统
- `Tools\BaseTool` - 工具基类
- `Tools\ToolExecutor` - 工具执行器
- 内置工具：文件操作、Bash、用户输入等

### 会话管理
- `Session\SessionManager` - 会话管理器（支持文件存储）
- `Session\AgentSession` - 会话数据结构
- `Session\AgentRunRecord` - 运行记录
- `Services\AgentSessionService` - Laravel 集成服务
- `Http\Controllers\AgentSessionController` - RESTful API 控制器

### 记忆系统
- `Memory\MemoryManager` - 记忆管理器
- `Memory\Memory` - 记忆存储

## 文档

详细文档请查看 [docs](docs/) 目录。

## License

MIT License