@extends('layouts.app')

@section('title', '编辑智能体 - 蒙太奇')
@section('description', '编辑和配置您的AI智能体，优化助手功能')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题和操作 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center text-sm text-gray-600 mb-3">
                        <a href="{{ route('llm.agents.index') }}" class="text-primary-color hover:text-blue-700 transition-colors duration-200">
                            <i class="fas fa-user-robot mr-1"></i>智能体管理
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                        <span class="text-gray-900 font-medium">编辑智能体</span>
                    </nav>

                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-edit text-primary-color mr-3"></i>
                        编辑 AI 智能体
                    </h1>
                    <div class="flex items-center mt-2">
                        <div class="flex items-center text-gray-600 mr-4">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                            <span class="text-sm">草稿模式</span>
                        </div>
                        <span class="text-sm text-gray-500">
                        <i class="far fa-clock mr-1"></i>
                        最后更新：{{ $draftVersion->updated_at->format('Y-m-d H:i') ?? '暂无' }}
                    </span>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('llm.agents.index') }}" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回列表
                    </a>
                    <button type="button" onclick="saveDraft()" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        保存草稿
                    </button>
                    <button type="button" onclick="publishAgent()" class="btn btn-success">
                        <i class="fas fa-paper-plane mr-2"></i>
                        发布版本
                    </button>
                </div>
            </div>

            <!-- 智能体标题卡片 -->
            <div class="card mb-6">
                <div class="p-5">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center shadow-sm">
                            <i class="fas fa-robot text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900">{{ $agent->name }}</h2>
                            <p class="text-gray-600 mt-1">{{ $agent->description ?? '暂无描述' }}</p>
                            <div class="flex items-center mt-2 space-x-3 text-sm">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-full">
                                <i class="fas fa-code-branch mr-1"></i>
                                版本：v{{ $draftVersion->version ?? '新版本' }}
                            </span>
                                @if($agent->published_version)
                                    <span class="px-2 py-1 bg-green-50 text-green-700 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>
                                已发布：v{{ $agent->published_version }}
                            </span>
                                @endif
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                ID：{{ $agent->id }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 左侧：配置区域 -->
            <div class="space-y-6">
                <!-- 基础配置卡片 -->
                <div class="card">
                    <div class="p-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-cog text-primary-color mr-2"></i>
                            基础配置
                        </h3>
                    </div>

                    <div class="p-5 space-y-6">
                        <!-- AI 模型选择 -->
                        <div>
                            <label for="model-select" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-brain text-gray-400 mr-2 text-sm"></i>
                                AI 模型
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <select id="model-select" class="input w-full" required>
                                    <option value="">加载中...</option>
                                </select>
                                <div class="absolute right-3 top-3 text-gray-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                当前选择：<span id="current-model" class="ml-1 font-medium">加载中...</span>
                            </div>
                        </div>

                        <!-- 系统提示词 -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i class="fas fa-comment-dots text-gray-400 mr-2 text-sm"></i>
                                    系统提示词
                                    <span class="text-red-500 ml-1">*</span>
                                </label>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="insertPromptTemplate('assistant')" class="text-xs text-primary-color hover:underline">
                                        <i class="fas fa-magic mr-1"></i>助手模板
                                    </button>
                                    <button type="button" onclick="insertPromptTemplate('expert')" class="text-xs text-primary-color hover:underline">
                                        <i class="fas fa-user-tie mr-1"></i>专家模板
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                            <textarea
                                    id="system-prompt"
                                    rows="6"
                                    class="input w-full resize-none"
                                    placeholder="例如：你是一个专业的写作助手，擅长各种文体创作..."
                                    required
                            >{{ $draftVersion->system_prompt ?? '' }}</textarea>
                                <div class="absolute bottom-3 right-3">
                                <span id="prompt-char-count" class="text-xs text-gray-400">
                                    {{ strlen($draftVersion->system_prompt ?? '') }} 字符
                                </span>
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-500 flex items-start">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                                定义智能体的角色、能力和行为规范，这将直接影响它的回答风格
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 高级参数配置 -->
                <div class="card">
                    <div class="p-5 border-b border-gray-200">
                        <div class="flex items-center justify-between cursor-pointer" onclick="toggleAdvancedParams()">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-sliders-h text-primary-color mr-2"></i>
                                高级参数配置
                            </h3>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="advanced-arrow"></i>
                        </div>
                    </div>

                    <div class="p-5 space-y-6" id="advanced-params">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Temperature -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-gray-700">
                                        温度 (Temperature)
                                    </label>
                                    <span class="text-sm font-medium text-primary-color" id="temperature-value">
                                    {{ $draftVersion->temperature ?? 0.7 }}
                                </span>
                                </div>
                                <div class="space-y-2">
                                    <input
                                            type="range"
                                            id="temperature"
                                            min="0"
                                            max="2"
                                            step="0.1"
                                            value="{{ $draftVersion->temperature ?? 0.7 }}"
                                            class="w-full"
                                    >
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>更确定</span>
                                        <span>更随机</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    控制回答的随机性，值越高回答越多样有创意
                                </p>
                            </div>

                            <!-- Top P -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-gray-700">
                                        Top P
                                    </label>
                                    <span class="text-sm font-medium text-primary-color" id="top-p-value">
                                    {{ $draftVersion->top_p ?? 0.9 }}
                                </span>
                                </div>
                                <div class="space-y-2">
                                    <input
                                            type="range"
                                            id="top-p"
                                            min="0"
                                            max="1"
                                            step="0.1"
                                            value="{{ $draftVersion->top_p ?? 0.9 }}"
                                            class="w-full"
                                    >
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>更聚焦</span>
                                        <span>更多样</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    控制回答的多样性，值越低回答越集中在常见词汇上
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Max Tokens -->
                            <div>
                                <label for="max-tokens" class="block text-sm font-medium text-gray-700 mb-2">
                                    最大生成长度
                                </label>
                                <input
                                        type="number"
                                        id="max-tokens"
                                        min="1"
                                        max="32000"
                                        value="{{ $draftVersion->max_tokens ?? 2000 }}"
                                        class="input w-full"
                                >
                                <p class="text-xs text-gray-500 mt-2">
                                    限制AI单次回答的最大长度，过长可能会被截断
                                </p>
                            </div>

                            <!-- Context Length -->
                            <div>
                                <label for="context-length" class="block text-sm font-medium text-gray-700 mb-2">
                                    上下文长度
                                </label>
                                <input
                                        type="number"
                                        id="context-length"
                                        min="1"
                                        max="128000"
                                        value="{{ $draftVersion->context_length ?? 4000 }}"
                                        class="input w-full"
                                >
                                <p class="text-xs text-gray-500 mt-2">
                                    对话历史的最大记忆长度，值越高能记住更多对话内容
                                </p>
                            </div>
                        </div>

                        <!-- 更多高级参数 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="frequency-penalty" class="block text-sm font-medium text-gray-700 mb-2">
                                    频率惩罚
                                </label>
                                <input
                                        type="number"
                                        id="frequency-penalty"
                                        min="-2"
                                        max="2"
                                        step="0.1"
                                        value="0"
                                        class="input w-full"
                                >
                            </div>

                            <div>
                                <label for="presence-penalty" class="block text-sm font-medium text-gray-700 mb-2">
                                    存在惩罚
                                </label>
                                <input
                                        type="number"
                                        id="presence-penalty"
                                        min="-2"
                                        max="2"
                                        step="0.1"
                                        value="0"
                                        class="input w-full"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 工具配置 -->
                <div class="card">
                    <div class="p-5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-tools text-primary-color mr-2"></i>
                                工具配置
                            </h3>
                            <div class="flex space-x-2">
                                <button type="button" onclick="validateJson()" class="btn btn-sm btn-outline">
                                    <i class="fas fa-check mr-1"></i>
                                    验证JSON
                                </button>
                                <button type="button" onclick="showToolsHelp()" class="btn btn-sm btn-outline">
                                    <i class="fas fa-question-circle mr-1"></i>
                                    使用帮助
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-code text-gray-400 mr-2 text-sm"></i>
                                工具配置 (JSON格式)
                            </label>
                            <div class="relative">
                            <textarea
                                    id="tools-config"
                                    rows="6"
                                    class="input w-full font-mono text-sm resize-none"
                                    placeholder='[{"type": "function", "function": { "name": "get_weather", ... }}]'
                            >{{ $draftVersion->tools_config ? json_encode($draftVersion->tools_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                                <div class="absolute bottom-3 right-3">
                                <span id="json-valid-badge" class="hidden px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>格式正确
                                </span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                                配置智能体可以调用的外部工具函数，使用 OpenAI Function Calling 格式
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右侧：测试区域 -->
            <div class="space-y-6">
                <!-- 实时测试卡片 -->
                <div class="card h-full flex flex-col">
                    <div class="p-5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-comments text-primary-color mr-2"></i>
                                实时测试
                            </h3>
                            <div class="flex items-center space-x-2">
                                <button type="button" onclick="loadExampleMessage()" class="btn btn-sm btn-outline">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    示例问题
                                </button>
                                <button type="button" onclick="clearChat()" class="btn btn-sm btn-outline">
                                    <i class="fas fa-trash mr-1"></i>
                                    清空对话
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 聊天容器 -->
                    <div class="flex-1 flex flex-col min-h-0">
                        <!-- 消息区域 -->
                        <div id="chat-container" class="flex-1 overflow-y-auto p-5 bg-gray-50 custom-scrollbar space-y-4">
                            <!-- 欢迎消息 -->
                            <div class="flex space-x-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3">
                                        <div class="font-medium text-gray-900 mb-1">{{ $agent->name }}</div>
                                        <div class="text-gray-700">
                                            您好！我是 <span class="font-semibold text-primary-color">{{ $agent->name }}</span>，已准备好为您服务。
                                            <div class="mt-2 text-sm text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                您可以在这里测试我的功能和响应
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 ml-1">
                                        {{ now()->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 输入区域 -->
                        <div class="border-t border-gray-200 bg-white p-5">
                            <div class="space-y-3">
                                <div class="relative">
                                <textarea
                                        id="chat-message"
                                        rows="3"
                                        class="input w-full resize-none pl-10 pr-32"
                                        placeholder="输入消息测试智能体功能 (Ctrl+Enter 发送)"
                                        onkeydown="handleKeyDown(event)"
                                ></textarea>
                                    <div class="absolute left-3 top-3 text-gray-400">
                                        <i class="fas fa-comment"></i>
                                    </div>
                                    <div class="absolute right-3 top-3 flex items-center space-x-2">
                                        <span id="char-count" class="text-xs text-gray-400">0</span>
                                        <button
                                                id="send-btn"
                                                onclick="sendMessage()"
                                                class="btn btn-primary btn-sm"
                                                disabled
                                        >
                                            <i class="fas fa-paper-plane mr-1"></i>
                                            发送
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <div class="flex items-center space-x-3">
                                    <span>
                                        <i class="fas fa-keyboard mr-1"></i>
                                        Ctrl+Enter 发送
                                    </span>
                                        <span>
                                        <i class="fas fa-share mr-1"></i>
                                        Shift+Enter 换行
                                    </span>
                                    </div>
                                    <div>
                                        <i class="fas fa-microchip mr-1"></i>
                                        当前模型：<span id="current-model-display" class="font-medium">加载中...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 状态设置卡片 -->
                <div class="card">
                    <div class="p-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-toggle-on text-primary-color mr-2"></i>
                            状态设置
                        </h3>
                    </div>

                    <div class="p-5">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-eye text-green-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">公开可见</div>
                                        <div class="text-sm text-gray-500">其他用户可以看到并使用此智能体</div>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="is-public" class="sr-only peer" {{ $agent->is_public ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-color"></div>
                                </label>
                            </div>

                            <div class="border-t border-gray-100 pt-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-power-off text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">启用智能体</div>
                                            <div class="text-sm text-gray-500">启用后用户可以在对话中使用此智能体</div>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="is-active" class="sr-only peer" {{ $agent->is_active ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-color"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 工具帮助模态框 -->
    <div class="modal" id="toolsHelpModal">
        <div class="modal-content max-w-3xl">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-question-circle text-primary-color mr-2"></i>
                    工具配置帮助
                </h3>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">什么是工具函数？</h4>
                        <p class="text-sm text-gray-600">
                            工具函数允许您的智能体调用外部API或执行特定任务，例如获取天气、查询数据库、发送邮件等。
                        </p>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">JSON 配置格式</h4>
                        <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-sm overflow-x-auto">
<code class="language-json">{
    "tools": [
        {
            "type": "function",
            "function": {
                "name": "get_weather",
                "description": "获取指定城市的天气信息",
                "parameters": {
                    "type": "object",
                    "properties": {
                        "location": {
                            "type": "string",
                            "description": "城市名称，如：北京"
                        },
                        "unit": {
                            "type": "string",
                            "enum": ["celsius", "fahrenheit"]
                        }
                    },
                    "required": ["location"]
                }
            }
        },
        {
            "type": "function",
            "function": {
                "name": "calculate",
                "description": "执行数学计算",
                "parameters": {
                    "type": "object",
                    "properties": {
                        "expression": {
                            "type": "string",
                            "description": "数学表达式，如：2 + 2 * 3"
                        }
                    },
                    "required": ["expression"]
                }
            }
        }
    ]
}</code></pre>
                    </div>

                    <div class="text-sm text-gray-600">
                        <p class="mb-2">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            <strong>提示：</strong>您可以使用 JSON 验证按钮检查格式是否正确，系统会自动美化格式。
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeToolsHelp()" class="btn btn-secondary">关闭</button>
            </div>
        </div>
    </div>

    <!-- 当前版本和模型信息 -->
    <input type="hidden" id="current-agent-id" value="{{ $agent->id }}">
    <input type="hidden" id="draft-version-model-id" value="{{ $draftVersion && isset($draftVersion->model_id) ? $draftVersion->model_id : 0 }}">

    @section('scripts')
        <script>
            // 全局变量
            let chatHistory = [];
            let currentAgentId = {{ $agent->id }};
            let agentName = '{{ addslashes($agent->name ?? "") }}';

            // 初始化
            document.addEventListener('DOMContentLoaded', function() {
                console.log('智能体编辑页面初始化...');

                // 加载初始数据
                loadModels();
                loadChatHistory();
                initSliders();
                initEventListeners();

                // 初始化字符计数
                updateCharCount();
                updatePromptCharCount();
            });

            // 初始化事件监听器
            function initEventListeners() {
                // 消息输入框
                const messageInput = document.getElementById('chat-message');
                messageInput.addEventListener('input', updateCharCount);
                messageInput.addEventListener('input', toggleSendButton);

                // 系统提示词字符计数
                document.getElementById('system-prompt').addEventListener('input', updatePromptCharCount);

                // 滑块事件
                document.getElementById('temperature').addEventListener('input', function() {
                    document.getElementById('temperature-value').textContent = this.value;
                });

                document.getElementById('top-p').addEventListener('input', function() {
                    document.getElementById('top-p-value').textContent = this.value;
                });

                // JSON 验证
                document.getElementById('tools-config').addEventListener('input', function() {
                    document.getElementById('json-valid-badge').classList.add('hidden');
                });
            }

            // 初始化滑块
            function initSliders() {
                const tempSlider = document.getElementById('temperature');
                const topPSlider = document.getElementById('top-p');

                // 设置滑块值显示
                document.getElementById('temperature-value').textContent = tempSlider.value;
                document.getElementById('top-p-value').textContent = topPSlider.value;
            }

            // 加载模型列表
            async function loadModels() {
                try {
                    const response = await window.taskApiFetch('/api/v2/llm/models', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        const select = document.getElementById('model-select');
                        const draftModelId = document.getElementById('draft-version-model-id').value;

                        // 清空现有选项
                        select.innerHTML = '';

                        // 添加默认选项
                        select.innerHTML = '<option value="">请选择AI模型...</option>';

                        // 添加模型选项
                        if (result.result?.models || result.models) {
                            const models = result.result?.models || result.models;
                            models.forEach(model => {
                                const selected = draftModelId == model.id ? 'selected' : '';
                                const provider = model.provider?.name || '未知供应商';
                                const option = new Option(`${model.name} (${provider})`, model.id, selected, selected);
                                select.appendChild(option);
                            });
                        }

                        // 更新显示
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption) {
                            document.getElementById('current-model').textContent = selectedOption.text;
                            document.getElementById('current-model-display').textContent = selectedOption.text;
                        }
                    }
                } catch (error) {
                    console.error('加载模型失败:', error);
                    document.getElementById('model-select').innerHTML = '<option value="">加载失败</option>';
                }
            }

            // 插入提示词模板
            function insertPromptTemplate(type) {
                const textarea = document.getElementById('system-prompt');
                let template = '';

                if (type === 'assistant') {
                    template = `你是一个乐于助人的AI助手。请遵循以下原则：

1. **核心目标**
   - 准确理解用户的问题
   - 提供清晰、有用的回答
   - 保持友好和专业的语气

2. **回答风格**
   - 使用清晰简洁的语言
   - 结构化的思考过程
   - 必要时提供具体例子

3. **处理边界**
   - 如果不知道答案，诚实地告知
   - 不提供医疗、法律、财务等专业建议
   - 保持中立和客观的立场

4. **交互方式**
   - 主动询问需要的信息
   - 分步骤解释复杂概念
   - 确认是否解决了用户的问题

请根据以上原则为用户提供最好的帮助。`;
                } else if (type === 'expert') {
                    template = `你是一个[具体领域]专家。请按照以下要求进行回答：

**角色定位**
- 身份：资深[领域]专家
- 经验：[X]年相关工作经验
- 专长：[具体技能或知识领域]

**回答规范**
1. **专业性**
   - 使用专业术语和准确的概念
   - 引用行业标准或最佳实践
   - 提供数据支持的观点

2. **深度分析**
   - 分析问题的根本原因
   - 评估不同方案的优缺点
   - 考虑实际应用场景

3. **实用建议**
   - 提供具体的操作步骤
   - 建议相关工具或资源
   - 提示潜在风险及应对措施

**交互原则**
- 根据用户的专业程度调整解释深度
- 在复杂问题中先给出核心结论
- 保持严谨但不失亲和力的语气

请确保你的回答既专业又实用，真正帮助用户解决问题。`;
                }

                if (textarea.value.trim()) {
                    textarea.value += '\n\n' + template;
                } else {
                    textarea.value = template;
                }

                updatePromptCharCount();
                showToast('success', '提示词模板已插入');
            }

            // 切换高级参数
            function toggleAdvancedParams() {
                const paramsDiv = document.getElementById('advanced-params');
                const arrowIcon = document.getElementById('advanced-arrow');

                paramsDiv.classList.toggle('hidden');

                if (paramsDiv.classList.contains('hidden')) {
                    arrowIcon.classList.remove('rotate-180');
                } else {
                    arrowIcon.classList.add('rotate-180');
                }
            }

            // 保存草稿
            async function saveDraft() {
                // 表单验证
                if (!validateForm()) {
                    showToast('请填写所有必填字段', 'error');
                    return;
                }

                // 验证工具配置JSON
                const toolsConfig = document.getElementById('tools-config').value.trim();
                let parsedTools = null;

                if (toolsConfig) {
                    try {
                        parsedTools = JSON.parse(toolsConfig);
                    } catch (error) {
                        showToast('工具配置 JSON 格式错误: ' + error.message, 'error');
                        return;
                    }
                }

                // 准备表单数据
                const formData = {
                    model_id: document.getElementById('model-select').value,
                    system_prompt: document.getElementById('system-prompt').value.trim(),
                    temperature: parseFloat(document.getElementById('temperature').value),
                    top_p: parseFloat(document.getElementById('top-p').value),
                    max_tokens: parseInt(document.getElementById('max-tokens').value) || 2000,
                    context_length: parseInt(document.getElementById('context-length').value) || 4000,
                    frequency_penalty: parseFloat(document.getElementById('frequency-penalty').value) || 0,
                    presence_penalty: parseFloat(document.getElementById('presence-penalty').value) || 0,
                    tools_config: parsedTools,
                    is_public: document.getElementById('is-public').checked ? 1 : 0,
                    is_active: document.getElementById('is-active').checked ? 1 : 0
                };

                // 验证必填字段
                if (!formData.system_prompt) {
                    showToast('请填写系统提示词', 'warning');
                    return;
                }

                if (!formData.model_id) {
                    showToast('请选择AI模型', 'warning');
                    return;
                }

                try {
                    const response = await window.taskApiFetch(`/api/v2/llm/agents/${currentAgentId}/draft`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formData)
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('草稿保存成功', 'success');
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('保存草稿失败:', error);
                    showToast('保存失败: ' + error.message, 'error');
                }
            }

            // 发布智能体
            async function publishAgent() {
                if (!confirm('确定要发布此版本吗？发布后，所有用户将使用新版本的智能体。')) {
                    return;
                }

                try {
                    const response = await window.taskApiFetch(`/api/v2/llm/agents/${currentAgentId}/publish`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('智能体发布成功！', 'success');
                        // 2秒后跳转回列表页
                        setTimeout(() => {
                            window.location.href = '{{ route("llm.agents.index") }}';
                        }, 2000);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('发布智能体失败:', error);
                    showToast('发布失败: ' + error.message, 'error');
                }
            }

            // 发送消息
            async function sendMessage() {
                const messageInput = document.getElementById('chat-message');
                const message = messageInput.value.trim();

                if (!message) {
                    showToast('请输入消息内容', 'warning');
                    return;
                }

                // 添加到聊天
                addToChat(message, 'user');
                messageInput.value = '';
                updateCharCount();
                toggleSendButton();

                // 保存到历史记录
                saveChatToHistory(message, 'user');

                // 禁用发送按钮
                const sendBtn = document.getElementById('send-btn');
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>思考中';

                try {
                    const response = await window.taskApiFetch(`/api/v2/llm/agents/${currentAgentId}/test-chat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ message: message })
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        addToChat(result.response, 'bot');
                        saveChatToHistory(result.response, 'bot');
                    } else {
                        addToChat('抱歉，我遇到了问题: ' + (result.error || '未知错误'), 'bot');
                    }
                } catch (error) {
                    console.error('发送消息失败:', error);
                    addToChat('网络错误: ' + error.message, 'bot');
                } finally {
                    // 恢复发送按钮
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>发送';
                }
            }

            // 处理键盘事件
            function handleKeyDown(e) {
                // Ctrl+Enter 发送
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    sendMessage();
                }
                // Shift+Enter 换行
                else if (e.key === 'Enter' && e.shiftKey) {
                    // 默认行为，允许换行
                }
                // 普通 Enter 在移动端发送
                else if (e.key === 'Enter' && window.innerWidth <= 768) {
                    e.preventDefault();
                    sendMessage();
                }
            }

            // 添加到聊天
            function addToChat(message, sender) {
                const chatContainer = document.getElementById('chat-container');
                const now = new Date();
                const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                const senderName = sender === 'user' ? '您' : agentName;

                let messageHTML = '';

                if (sender === 'user') {
                    messageHTML = `
                <div class="flex justify-end">
                    <div class="max-w-3xl">
                        <div class="bg-primary-color text-white rounded-2xl rounded-br-none px-4 py-3">
                            ${escapeHtml(message).replace(/\n/g, '<br>')}
                        </div>
                        <div class="text-xs text-gray-500 mt-1 text-right">
                            ${timeStr}
                        </div>
                    </div>
                </div>
            `;
                } else {
                    messageHTML = `
                <div class="flex space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3">
                            <div class="font-medium text-gray-900 mb-1">${senderName}</div>
                            <div class="text-gray-700">${formatBotResponse(message)}</div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1 ml-1">
                            ${timeStr}
                        </div>
                    </div>
                </div>
            `;
                }

                chatContainer.insertAdjacentHTML('beforeend', messageHTML);

                // 滚动到底部
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // 格式化AI响应
            function formatBotResponse(text) {
                return text
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`(.*?)`/g, '<code class="bg-gray-100 text-gray-800 px-1 rounded text-sm">$1</code>')
                    .replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-900 text-gray-100 rounded p-3 text-sm overflow-x-auto my-2"><code>$1</code></pre>')
                    .replace(/\n/g, '<br>');
            }

            // 转义HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // 保存聊天历史
            function saveChatToHistory(msg, sender) {
                chatHistory.push({ message: msg, sender, timestamp: new Date().toISOString() });

                // 限制历史记录数量
                if (chatHistory.length > 50) {
                    chatHistory = chatHistory.slice(-50);
                }

                localStorage.setItem('agent_chat_' + currentAgentId, JSON.stringify(chatHistory));
            }

            // 加载聊天历史
            function loadChatHistory() {
                const saved = localStorage.getItem('agent_chat_' + currentAgentId);
                if (saved) {
                    try {
                        chatHistory = JSON.parse(saved);
                        chatHistory.forEach(item => addToChat(item.message, item.sender));
                    } catch (error) {
                        console.error('加载聊天历史失败:', error);
                    }
                }
            }

            // 清空聊天
            function clearChat() {
                if (confirm('确定要清空对话记录吗？此操作不可撤销。')) {
                    const chatContainer = document.getElementById('chat-container');
                    chatContainer.innerHTML = '';
                    chatHistory = [];
                    localStorage.removeItem('agent_chat_' + currentAgentId);

                    // 添加欢迎消息
                    addToChat(`您好！我是 ${agentName}，已准备好为您服务。`, 'bot');
                }
            }

            // 加载示例消息
            function loadExampleMessage() {
                const examples = [
                    "请介绍一下你自己",
                    "你能帮我做什么？",
                    "写一个简单的问候语",
                    "用一句话总结今天的天气",
                    "解释什么是人工智能",
                    "帮我制定一个学习计划",
                    "写一个简短的自我介绍",
                    "给我一些时间管理的建议"
                ];
                const ex = examples[Math.floor(Math.random() * examples.length)];
                document.getElementById('chat-message').value = ex;
                updateCharCount();
                toggleSendButton();
                document.getElementById('chat-message').focus();
            }

            // 验证JSON
            function validateJson() {
                const textarea = document.getElementById('tools-config');
                const value = textarea.value.trim();
                const badge = document.getElementById('json-valid-badge');

                if (!value) {
                    showToast('工具配置为空', 'info');
                    return;
                }

                try {
                    const parsed = JSON.parse(value);
                    // 美化JSON
                    textarea.value = JSON.stringify(parsed, null, 2);
                    badge.classList.remove('hidden');
                    showToast('JSON 格式正确，已美化', 'success');
                } catch (error) {
                    badge.classList.add('hidden');
                    showToast('JSON 格式错误: ' + error.message, 'error');
                }
            }

            // 显示工具帮助
            function showToolsHelp() {
                document.getElementById('toolsHelpModal').classList.add('show');
            }

            // 关闭工具帮助
            function closeToolsHelp() {
                document.getElementById('toolsHelpModal').classList.remove('show');
            }

            // 更新字符计数
            function updateCharCount() {
                const textarea = document.getElementById('chat-message');
                const counter = document.getElementById('char-count');
                counter.textContent = textarea.value.length;
            }

            // 更新提示词字符计数
            function updatePromptCharCount() {
                const textarea = document.getElementById('system-prompt');
                const counter = document.getElementById('prompt-char-count');
                counter.textContent = textarea.value.length + ' 字符';
            }

            // 切换发送按钮状态
            function toggleSendButton() {
                const sendBtn = document.getElementById('send-btn');
                const message = document.getElementById('chat-message').value.trim();
                sendBtn.disabled = !message;
            }

            // 表单验证
            function validateForm() {
                let isValid = true;

                // 检查系统提示词
                const systemPrompt = document.getElementById('system-prompt').value.trim();
                if (!systemPrompt) {
                    document.getElementById('system-prompt').classList.add('border-red-500');
                    isValid = false;
                } else {
                    document.getElementById('system-prompt').classList.remove('border-red-500');
                }

                // 检查模型选择
                const modelSelect = document.getElementById('model-select').value;
                if (!modelSelect) {
                    document.getElementById('model-select').classList.add('border-red-500');
                    isValid = false;
                } else {
                    document.getElementById('model-select').classList.remove('border-red-500');
                }

                return isValid;
            }

            // 显示提示消息
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' :
                        type === 'error' ? 'bg-red-500' :
                            type === 'warning' ? 'bg-yellow-500' :
                                type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' :
                    type === 'error' ? 'exclamation-circle' :
                        type === 'warning' ? 'exclamation-triangle' :
                            type === 'info' ? 'info-circle' : 'bell'} mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 3000);
            }

            // 关闭模态框点击背景
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            });
        </script>

        <style>
            /* 聊天消息样式 */
            .bg-primary-color {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            }

            /* 消息动画 */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .flex.space-x-3,
            .flex.justify-end {
                animation: fadeInUp 0.3s ease-out;
            }

            /* 自定义滚动条 */
            .custom-scrollbar {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 #f1f5f9;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }

            /* 滑块样式 */
            input[type="range"] {
                -webkit-appearance: none;
                height: 6px;
                background: #e2e8f0;
                border-radius: 3px;
                outline: none;
            }

            input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 18px;
                height: 18px;
                background: var(--primary-color);
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            input[type="range"]::-webkit-slider-thumb:hover {
                transform: scale(1.2);
            }

            /* 代码块样式 */
            pre code {
                display: block;
                padding: 1rem;
                overflow-x: auto;
                line-height: 1.4;
                font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                font-size: 0.875rem;
            }

            /* 旋转动画 */
            .rotate-180 {
                transform: rotate(180deg);
            }

            /* 模态框动画 */
            .modal.show .modal-content {
                animation: modalSlideIn 0.3s ease-out;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* 响应式调整 */
            @media (max-width: 768px) {
                .grid-cols-1.lg\:grid-cols-2 {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endsection