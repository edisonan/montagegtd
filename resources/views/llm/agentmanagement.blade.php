@extends('layouts.app')

@section('title', '智能体管理 - 蒙太奇')
@section('description', '管理您的AI智能体，创建和配置个性化助手')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-user-robot text-primary-color mr-3"></i>
                        LLM智能体管理
                    </h1>
                    <p class="text-gray-600 mt-2">创建和管理您的AI智能体，定制个性化助手</p>
                </div>

                <!-- 快速操作按钮 -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/llm/management') }}" class="btn btn-outline">
                        <i class="fas fa-cogs mr-2"></i>
                        LLM管理
                    </a>
                    <a href="{{ url('/llm/index') }}" class="btn btn-primary">
                        <i class="fas fa-comments mr-2"></i>
                        开始对话
                    </a>
                </div>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-robot text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">我的智能体</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-agents">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                            <i class="fas fa-play-circle text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">活跃智能体</p>
                            <p class="text-2xl font-bold text-gray-900" id="active-agents">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mr-4">
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">总使用次数</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-usage">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要功能区域 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list text-primary-color mr-2"></i>
                            智能体列表
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">管理您的所有智能体配置</p>
                    </div>

                    <!-- 筛选和搜索 -->
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <input
                                    type="text"
                                    id="search-agents"
                                    class="input w-48 pl-9"
                                    placeholder="搜索智能体..."
                            >
                            <div class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>

                        <select id="filter-status" class="input w-32">
                            <option value="">全部状态</option>
                            <option value="active">活跃</option>
                            <option value="inactive">禁用</option>
                        </select>

                        <button onclick="showCreateAgentModal()" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            新建智能体
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- 加载状态 -->
                <div id="loading-state" class="flex flex-col items-center justify-center py-12">
                    <div class="w-16 h-16 mb-4">
                        <i class="fas fa-spinner fa-spin text-primary-color text-2xl"></i>
                    </div>
                    <p class="text-gray-600">正在加载智能体...</p>
                </div>

                <!-- 智能体网格 -->
                <div id="agent-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                    <!-- 智能体卡片将在这里动态加载 -->
                </div>

                <!-- 空状态 -->
                <div id="empty-state" class="text-center py-16 hidden">
                    <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-robot text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">您还没有创建智能体</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        智能体可以帮助您创建个性化的AI助手，针对不同的场景和需求进行优化配置
                    </p>
                    <button onclick="showCreateAgentModal()" class="btn btn-primary text-lg px-8 py-3">
                        <i class="fas fa-plus mr-3"></i>
                        创建第一个智能体
                    </button>
                </div>
            </div>
        </div>

        <!-- 使用指南卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-primary-color mr-2"></i>
                    智能体使用指南
                </h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">专业助手</h4>
                        <p class="text-sm text-gray-600">为不同专业领域创建专属助手，如编程、写作、设计等</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-cog text-green-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">参数调优</h4>
                        <p class="text-sm text-gray-600">调整温度、top_p等参数，控制AI的创造性和确定性</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-book text-purple-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">知识库</h4>
                        <p class="text-sm text-gray-600">为智能体添加专属知识库，提供更精准的回答</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-share-alt text-orange-600"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">分享协作</h4>
                        <p class="text-sm text-gray-600">与团队成员共享智能体，提升协作效率</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建智能体模态框 -->
    <div class="modal" id="createAgentModal">
        <div class="modal-content max-w-2xl">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-plus-circle text-primary-color mr-2"></i>
                    创建新智能体
                </h2>
            </div>

            <div class="p-6">
                <form id="createAgentForm" class="space-y-6">
                    {!! csrf_field() !!}

                    <div class="space-y-6">
                        <!-- 基本信息 -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wider">基本信息</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="agent_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        智能体名称
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                            type="text"
                                            name="name"
                                            id="agent_name"
                                            class="input w-full"
                                            placeholder="例如：编程助手、写作助手"
                                            required
                                    >
                                </div>

                                <div>
                                    <label for="agent_builtin_slug" class="block text-sm font-medium text-gray-700 mb-2">
                                        内置标识（可选）
                                    </label>
                                    <input
                                            type="text"
                                            name="builtin_slug"
                                            id="agent_builtin_slug"
                                            class="input w-full"
                                            placeholder="用于系统调用的标识符"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="agent_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    描述说明
                                </label>
                                <textarea
                                        name="description"
                                        id="agent_description"
                                        rows="3"
                                        class="input w-full resize-none"
                                        placeholder="描述这个智能体的用途和特点..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- 模型配置 -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wider">模型配置</h3>

                            <div>
                                <label for="agent_model_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    选择模型
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                        name="model_id"
                                        id="agent_model_id"
                                        class="input w-full"
                                        required
                                >
                                    <option value="">请选择模型...</option>
                                    <!-- 模型选项将动态加载 -->
                                </select>
                            </div>

                            <!-- 参数配置 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        温度 (Temperature)
                                        <span class="ml-2 text-sm text-gray-500" id="temp-value">0.7</span>
                                    </label>
                                    <div class="space-y-2">
                                        <input
                                                type="range"
                                                name="temperature"
                                                id="agent_temperature"
                                                min="0"
                                                max="2"
                                                step="0.1"
                                                value="0.7"
                                                class="w-full"
                                        >
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>确定性</span>
                                            <span>创造性</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Top P
                                        <span class="ml-2 text-sm text-gray-500" id="top-p-value">1.0</span>
                                    </label>
                                    <div class="space-y-2">
                                        <input
                                                type="range"
                                                name="top_p"
                                                id="agent_top_p"
                                                min="0"
                                                max="1"
                                                step="0.1"
                                                value="1.0"
                                                class="w-full"
                                        >
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span>聚焦</span>
                                            <span>多样</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 系统提示词 -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wider">系统提示词</h3>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="insertTemplate('assistant')" class="text-sm text-primary-color hover:underline">
                                        <i class="fas fa-magic mr-1"></i>助手模板
                                    </button>
                                    <button type="button" onclick="insertTemplate('expert')" class="text-sm text-primary-color hover:underline">
                                        <i class="fas fa-user-tie mr-1"></i>专家模板
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                <textarea
                                        name="system_prompt"
                                        id="agent_system_prompt"
                                        rows="6"
                                        class="input w-full resize-none"
                                        placeholder="定义智能体的角色、能力和行为规范..."
                                ></textarea>
                                    <div class="absolute bottom-3 right-3">
                                        <span id="prompt-char-count" class="text-xs text-gray-400">0/5000</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                    提示词决定了智能体的角色定位和回答风格
                                </p>
                            </div>
                        </div>

                        <!-- 高级设置 -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between cursor-pointer" onclick="toggleAdvancedSettings()">
                                <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wider">高级设置</h3>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="advanced-arrow"></i>
                            </div>

                            <div id="advanced-settings" class="space-y-4 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="agent_max_tokens" class="block text-sm font-medium text-gray-700 mb-2">
                                            最大生成长度
                                        </label>
                                        <input
                                                type="number"
                                                name="max_tokens"
                                                id="agent_max_tokens"
                                                class="input w-full"
                                                placeholder="默认：2048"
                                                min="100"
                                                max="8192"
                                        >
                                    </div>

                                    <div>
                                        <label for="agent_frequency_penalty" class="block text-sm font-medium text-gray-700 mb-2">
                                            频率惩罚
                                        </label>
                                        <input
                                                type="number"
                                                name="frequency_penalty"
                                                id="agent_frequency_penalty"
                                                class="input w-full"
                                                placeholder="默认：0"
                                                min="-2"
                                                max="2"
                                                step="0.1"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="agent_presence_penalty" class="block text-sm font-medium text-gray-700 mb-2">
                                            存在惩罚
                                        </label>
                                        <input
                                                type="number"
                                                name="presence_penalty"
                                                id="agent_presence_penalty"
                                                class="input w-full"
                                                placeholder="默认：0"
                                                min="-2"
                                                max="2"
                                                step="0.1"
                                        >
                                    </div>

                                    <div>
                                        <label for="agent_stop_sequences" class="block text-sm font-medium text-gray-700 mb-2">
                                            停止序列
                                        </label>
                                        <input
                                                type="text"
                                                name="stop_sequences"
                                                id="agent_stop_sequences"
                                                class="input w-full"
                                                placeholder="用逗号分隔，例如：\n,###,END"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center">
                                        <input
                                                type="checkbox"
                                                name="is_active"
                                                id="agent_is_active"
                                                class="rounded text-blue-600"
                                                checked
                                        >
                                        <label for="agent_is_active" class="ml-2 text-sm text-gray-700">
                                            启用智能体
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input
                                                type="checkbox"
                                                name="is_private"
                                                id="agent_is_private"
                                                class="rounded text-blue-600"
                                        >
                                        <label for="agent_is_private" class="ml-2 text-sm text-gray-700">
                                            设为私有
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 表单操作按钮 -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()" class="btn btn-secondary">
                            取消
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            创建智能体
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 确认删除模态框 -->
    <div class="modal" id="confirmDeleteModal">
        <div class="modal-content max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">确认删除</h3>
                <p class="text-gray-600 mb-6" id="delete-confirm-message">确定要删除这个智能体吗？此操作不可恢复。</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">取消</button>
                    <button type="button" id="confirm-delete-btn" class="btn btn-danger">确认删除</button>
                </div>
            </div>
        </div>
    </div>

        <script>
            // 全局变量
            let agentsData = [];
            let deleteAgentId = null;

            // 初始化
            document.addEventListener('DOMContentLoaded', function() {
                console.log('智能体管理页面初始化...');

                // 加载数据
                loadAgents();
                loadModels();

                // 初始化事件监听器
                initEventListeners();

                // 初始化提示词字符计数
                initPromptCharCount();

                // 初始化滑块值显示
                initSliders();
            });

            // 初始化事件监听器
            function initEventListeners() {
                // 搜索智能体
                document.getElementById('search-agents').addEventListener('input', searchAgents);

                // 筛选状态
                document.getElementById('filter-status').addEventListener('change', filterAgents);

                // 创建智能体表单提交
                document.getElementById('createAgentForm').addEventListener('submit', createAgent);

                // 提示词输入字符计数
                document.getElementById('agent_system_prompt').addEventListener('input', updatePromptCharCount);

                // 关闭模态框点击背景
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            this.classList.remove('show');
                        }
                    });
                });
            }

            // 初始化滑块值显示
            function initSliders() {
                const tempSlider = document.getElementById('agent_temperature');
                const topPSlider = document.getElementById('agent_top_p');

                tempSlider.addEventListener('input', function() {
                    document.getElementById('temp-value').textContent = this.value;
                });

                topPSlider.addEventListener('input', function() {
                    document.getElementById('top-p-value').textContent = this.value;
                });
            }

            // 初始化提示词字符计数
            function initPromptCharCount() {
                updatePromptCharCount();
            }

            // 更新提示词字符计数
            function updatePromptCharCount() {
                const textarea = document.getElementById('agent_system_prompt');
                const counter = document.getElementById('prompt-char-count');
                const length = textarea.value.length;
                const maxLength = 5000;

                counter.textContent = `${length}/${maxLength}`;

                if (length > maxLength * 0.9) {
                    counter.classList.remove('text-gray-400', 'text-red-600');
                    counter.classList.add('text-yellow-600');
                } else if (length > maxLength) {
                    counter.classList.remove('text-gray-400', 'text-yellow-600');
                    counter.classList.add('text-red-600');
                } else {
                    counter.classList.remove('text-yellow-600', 'text-red-600');
                    counter.classList.add('text-gray-400');
                }
            }

            // 插入提示词模板
            function insertTemplate(type) {
                const textarea = document.getElementById('agent_system_prompt');
                let template = '';

                if (type === 'assistant') {
                    template = `你是一个乐于助人的AI助手。你的目标是：
1. 准确理解用户的问题
2. 提供清晰、有用的回答
3. 如果不知道，诚实地告知
4. 保持友好和专业的语气

请根据用户的请求提供最好的帮助。`;
                } else if (type === 'expert') {
                    template = `你是一个专业的[领域]专家。请遵循以下原则：
1. 使用专业术语和准确的概念
2. 提供详细的解释和分析
3. 给出具体的建议和步骤
4. 保持客观和中立的立场

请确保你的回答具有专业性和深度。`;
                }

                if (textarea.value.trim()) {
                    textarea.value += '\n\n' + template;
                } else {
                    textarea.value = template;
                }

                updatePromptCharCount();
                showToast('success', '模板已插入');
            }

            // 切换高级设置
            function toggleAdvancedSettings() {
                const settingsDiv = document.getElementById('advanced-settings');
                const arrowIcon = document.getElementById('advanced-arrow');

                settingsDiv.classList.toggle('hidden');

                if (settingsDiv.classList.contains('hidden')) {
                    arrowIcon.classList.remove('rotate-180');
                } else {
                    arrowIcon.classList.add('rotate-180');
                }
            }

            // 加载智能体数据
            async function loadAgents() {
                try {
                    const response = await window.taskApiFetch('/api/v2/llm/agents', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        agentsData = result.result.agents;
                        displayAgents(agentsData);
                        updateStats(agentsData);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('加载智能体数据失败:', error);
                    showError('加载智能体数据失败');
                }
            }

            // 显示智能体列表
            function displayAgents(agents) {
                const grid = document.getElementById('agent-grid');
                const loading = document.getElementById('loading-state');
                const empty = document.getElementById('empty-state');

                // 隐藏加载状态
                loading.classList.add('hidden');

                if (!agents || agents.length === 0) {
                    grid.classList.add('hidden');
                    empty.classList.remove('hidden');
                    return;
                }

                empty.classList.add('hidden');
                grid.classList.remove('hidden');

                let html = '';

                agents.forEach(agent => {
                    const firstLetter = agent.name.charAt(0).toUpperCase();
                    const isActive = agent.is_active;
                    const modelName = agent.model ? agent.model.name : '未知模型';
                    const createdDate = new Date(agent.created_at).toLocaleDateString('zh-CN');

                    html += `
                <div class="card hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1" data-agent-id="${agent.id}" data-agent-status="${isActive ? 'active' : 'inactive'}">
                    <div class="p-5 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl ${isActive ? 'bg-blue-100' : 'bg-gray-100'} flex items-center justify-center">
                                    <span class="font-bold ${isActive ? 'text-blue-600' : 'text-gray-600'}">${firstLetter}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">${agent.name}</h3>
                                    <span class="text-xs ${isActive ? 'text-green-600 bg-green-50 px-2 py-0.5 rounded-full' : 'text-red-600 bg-red-50 px-2 py-0.5 rounded-full'}">
                                        ${isActive ? '启用中' : '已禁用'}
                                    </span>
                                </div>
                            </div>
                            <div class="dropdown relative">
                                <button class="btn btn-sm btn-outline">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu hidden" style="right: 0; left: auto;">
                                    <a href="/llm/agents/${agent.id}/draft" class="dropdown-item">
                                        <i class="fas fa-edit text-blue-500"></i>
                                        编辑
                                    </a>
                                    <button onclick="testAgent('${agent.id}')" class="dropdown-item">
                                        <i class="fas fa-play text-green-500"></i>
                                        测试
                                    </button>
                                    <button onclick="toggleAgentStatus('${agent.id}', ${isActive})" class="dropdown-item">
                                        <i class="fas fa-power-off ${isActive ? 'text-yellow-500' : 'text-green-500'}"></i>
                                        ${isActive ? '禁用' : '启用'}
                                    </button>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <button onclick="confirmDeleteAgent('${agent.id}', '${agent.name.replace(/'/g, "\\'")}')" class="dropdown-item text-red-600">
                                        <i class="fas fa-trash-alt"></i>
                                        删除
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-2" title="${agent.description || '暂无描述'}">
                            ${agent.description || '暂无描述'}
                        </p>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="space-y-1">
                                <div class="text-gray-500">模型</div>
                                <div class="font-medium text-gray-900">${modelName}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-gray-500">使用次数</div>
                                <div class="font-medium text-gray-900">${agent.usage_count || 0}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-gray-500">收藏数</div>
                                <div class="font-medium text-gray-900">${agent.favorite_count || 0}</div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-gray-500">创建时间</div>
                                <div class="font-medium text-gray-900">${createdDate}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                ${agent.builtin_slug ? `<span class="px-2 py-0.5 bg-gray-100 rounded text-xs">${agent.builtin_slug}</span>` : ''}
                            </div>
                            <div class="flex space-x-2">
                                <a href="/llm/index?agent_id=${agent.id}" class="btn btn-sm btn-outline">
                                    <i class="fas fa-comment mr-1"></i>
                                    对话
                                </a>
                                <a href="/llm/agents/${agent.id}/draft" class="btn btn-sm btn-primary">
                                    <i class="fas fa-cog mr-1"></i>
                                    配置
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                });

                grid.innerHTML = html;

                // 添加下拉菜单交互
                initDropdownMenus();
            }

            // 初始化下拉菜单
            function initDropdownMenus() {
                const dropdowns = document.querySelectorAll('.dropdown');

                dropdowns.forEach(dropdown => {
                    const button = dropdown.querySelector('button');
                    const menu = dropdown.querySelector('.dropdown-menu');

                    if (button && menu) {
                        dropdown.addEventListener('mouseenter', () => {
                            menu.classList.remove('hidden');
                            setTimeout(() => {
                                menu.classList.add('show');
                            }, 10);
                        });

                        dropdown.addEventListener('mouseleave', () => {
                            menu.classList.remove('show');
                            setTimeout(() => {
                                menu.classList.add('hidden');
                            }, 200);
                        });
                    }
                });
            }

            // 更新统计数据
            function updateStats(agents) {
                const totalAgents = agents.length;
                const activeAgents = agents.filter(agent => agent.is_active).length;
                const totalUsage = agents.reduce((sum, agent) => sum + (agent.usage_count || 0), 0);

                document.getElementById('total-agents').textContent = totalAgents;
                document.getElementById('active-agents').textContent = activeAgents;
                document.getElementById('total-usage').textContent = totalUsage;
            }

            // 加载模型选项
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
                        const select = document.getElementById('agent_model_id');
                        let options = '<option value="">请选择模型...</option>';

                        result.result.models.forEach(model => {
                            options += `<option value="${model.id}">${model.name}</option>`;
                        });

                        select.innerHTML = options;
                    }
                } catch (error) {
                    console.error('加载模型数据失败:', error);
                }
            }

            // 搜索智能体
            function searchAgents() {
                const searchTerm = document.getElementById('search-agents').value.toLowerCase();
                const statusFilter = document.getElementById('filter-status').value;

                const filteredAgents = agentsData.filter(agent => {
                    const matchesSearch = agent.name.toLowerCase().includes(searchTerm) ||
                        (agent.description && agent.description.toLowerCase().includes(searchTerm));
                    const matchesStatus = !statusFilter ||
                        (statusFilter === 'active' && agent.is_active) ||
                        (statusFilter === 'inactive' && !agent.is_active);

                    return matchesSearch && matchesStatus;
                });

                displayAgents(filteredAgents);
            }

            // 筛选智能体
            function filterAgents() {
                searchAgents();
            }

            // 显示创建智能体模态框
            function showCreateAgentModal() {
                document.getElementById('createAgentModal').classList.add('show');
                document.getElementById('agent_name').focus();
            }

            // 关闭创建模态框
            function closeCreateModal() {
                document.getElementById('createAgentModal').classList.remove('show');
                document.getElementById('createAgentForm').reset();
                updatePromptCharCount();
            }

            // 创建智能体
            async function createAgent(e) {
                e.preventDefault();

                const form = document.getElementById('createAgentForm');
                const formData = new FormData(form);

                // 验证必填字段
                if (!formData.get('name') || !formData.get('model_id')) {
                    showToast('请填写智能体名称并选择模型', 'error');
                    return;
                }

                try {
                    const response = await window.taskApiFetch('/api/v2/llm/agents', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('智能体创建成功', 'success');
                        closeCreateModal();
                        loadAgents();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('创建智能体失败:', error);
                    showToast('创建失败: ' + error.message, 'error');
                }
            }

            // 切换智能体状态
            async function toggleAgentStatus(agentId, currentStatus) {
                try {
                    const response = await window.taskApiFetch(`/api/v2/llm/agents/${agentId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast(result.data.is_active ? '智能体已启用' : '智能体已禁用', 'success');
                        loadAgents();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('切换智能体状态失败:', error);
                    showToast('操作失败: ' + error.message, 'error');
                }
            }

            // 测试智能体
            async function testAgent(agentId) {
                // 在新标签页中打开对话页面，指定使用该智能体
                window.open(`/llm/index?agent_id=${agentId}`, '_blank');
            }

            // 确认删除智能体
            function confirmDeleteAgent(agentId, agentName) {
                deleteAgentId = agentId;
                document.getElementById('delete-confirm-message').textContent =
                    `确定要删除智能体 "${agentName}" 吗？此操作不可恢复。`;
                document.getElementById('confirmDeleteModal').classList.add('show');
            }

            // 关闭删除确认模态框
            function closeDeleteModal() {
                deleteAgentId = null;
                document.getElementById('confirmDeleteModal').classList.remove('show');
            }

            // 删除智能体
            async function deleteAgent() {
                if (!deleteAgentId) return;

                try {
                    const response = await window.taskApiFetch(`/api/v2/llm/agents/${deleteAgentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const result = await response.json();

                    if (result.success) {
                        showToast('智能体已删除', 'success');
                        closeDeleteModal();
                        loadAgents();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('删除智能体失败:', error);
                    showToast('删除失败: ' + error.message, 'error');
                }
            }

            // 为删除确认按钮绑定事件
            document.getElementById('confirm-delete-btn').addEventListener('click', deleteAgent);

            // 显示提示消息
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' :
                        type === 'error' ? 'bg-red-500' :
                            type === 'info' ? 'bg-blue-500' : 'bg-gray-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' :
                    type === 'error' ? 'exclamation-circle' :
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

            // 显示错误
            function showError(message) {
                showToast(message, 'error');
            }
        </script>

        <style>
            /* 智能体卡片样式 */
            .card {
                transition: all 0.2s ease;
            }

            .card:hover {
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

            /* 文本截断 */
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
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

            /* 下拉菜单动画 */
            .dropdown-menu {
                animation: dropdownSlideIn 0.2s ease-out;
            }

            @keyframes dropdownSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
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
        </style>
    @endsection
