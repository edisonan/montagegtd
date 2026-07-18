@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和导航 -->

        <!-- 当前位置 -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center text-sm text-gray-500">
                <button type="button"
                        class="text-primary-600 hover:text-primary-700 font-medium"
                        onclick="showLlmView('providers')">
                    供应商
                </button>
                <i class="fas fa-chevron-right mx-2 text-gray-300" id="view-separator" style="display:none;"></i>
                <span class="font-medium text-gray-900" id="current-view-label" style="display:none;"></span>
            </div>
            <button type="button"
                    id="back-to-providers"
                    onclick="showLlmView('providers')"
                    class="btn btn-secondary btn-sm hidden">
                <i class="fas fa-arrow-left mr-2"></i>返回供应商
            </button>
        </div>

        <!-- 供应商管理视图 -->
        <div id="providers-view" class="tab-content active">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">供应商列表</h2>
                        <p class="text-sm text-gray-500">选择供应商后查看和管理模型、API Key</p>
                    </div>
                    <div class="mt-2 md:mt-0">
                        <button onclick="openProviderModal()" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-2"></i>新增供应商
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden" id="provider-container">
                    <!-- 供应商列表将通过JavaScript动态加载 -->
                    <div class="text-center py-12 col-span-3" id="providers-loading">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-color mb-4"></div>
                        <p class="text-gray-500">正在加载供应商数据...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 模型管理视图 -->
        <div id="models-view" class="tab-content hidden">
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900" id="models-view-title">模型列表</h2>
                        <p class="text-sm text-gray-500" id="models-view-subtitle">管理各个供应商的AI模型</p>
                    </div>
                    <div class="mt-2 md:mt-0 flex items-center space-x-3">
                        <select id="model-filter-provider" class="input" onchange="filterModels()">
                            <option value="">所有供应商</option>
                            <!-- 供应商选项将通过JavaScript动态加载 -->
                        </select>
                        <button onclick="openModelModal()" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-2"></i>新增模型
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="overflow-x-auto">
                        <table class="w-full table">
                            <thead>
                            <tr>
                                <th class="text-left">ID</th>
                                <th class="text-left">供应商</th>
                                <th class="text-left">模型名称</th>
                                <th class="text-left">类型</th>
                                <th class="text-left">价格</th>
                                <th class="text-left">状态</th>
                                <th class="text-left">操作</th>
                            </tr>
                            </thead>
                            <tbody id="model-tbody">
                            <tr id="models-loading">
                                <td colspan="7" class="text-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-color mb-4"></div>
                                    <p class="text-gray-500">正在加载模型数据...</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 供应商模态框 -->
    <div class="modal hidden" id="providerModal" role="dialog">
        <div class="modal-content w-full max-w-3xl">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900" id="providerModalTitle">添加供应商</h3>
                    <p class="text-sm text-gray-500 mt-1">配置新的AI服务提供商</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeProviderModal()" aria-label="关闭">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form id="providerForm" class="space-y-6">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="provider_id" name="provider_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="provider_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building text-gray-400 mr-2"></i>名称 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               class="input w-full"
                               id="provider_name"
                               name="name"
                               required
                               placeholder="例如：OpenAI、Anthropic">
                    </div>

                    <div>
                        <label for="provider_slug" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag text-gray-400 mr-2"></i>标识符 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               class="input w-full"
                               id="provider_slug"
                               name="slug"
                               required
                               placeholder="例如：openai、anthropic">
                        <p class="text-sm text-gray-500 mt-2">用于API调用的唯一标识，建议使用小写英文</p>
                    </div>
                </div>

                <div>
                    <label for="provider_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key text-gray-400 mr-2"></i>API Key
                    </label>
                    <input type="password"
                           class="input w-full"
                           id="provider_api_key"
                           name="api_key"
                           autocomplete="new-password"
                           placeholder="填写该供应商的统一 Key；编辑时留空表示保持原值">
                    <p class="text-sm text-gray-500 mt-2">同一供应商下的多个模型共用此 Key，系统会加密保存。</p>
                </div>

                <div>
                    <label for="provider_description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left text-gray-400 mr-2"></i>描述
                    </label>
                    <textarea class="input w-full min-h-[80px] resize-none"
                              id="provider_description"
                              name="description"
                              rows="2"
                              placeholder="简要描述此供应商的特点和服务..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="provider_api_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-code text-gray-400 mr-2"></i>API类型 <span class="text-red-500">*</span>
                        </label>
                        <select class="input w-full" id="provider_api_type" name="api_type" required>
                            <option value="">请选择API类型</option>
                            <option value="openai">OpenAI兼容</option>
                            <option value="anthropic">Anthropic</option>
                            <option value="google">Google Gemini</option>
                            <option value="azure">Azure OpenAI</option>
                            <option value="custom">自定义</option>
                        </select>
                    </div>

                    <div>
                        <label for="provider_base_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-link text-gray-400 mr-2"></i>API基础URL
                        </label>
                        <input type="text"
                               class="input w-full"
                               id="provider_base_url"
                               name="base_url"
                               placeholder="https://api.openai.com/v1">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="provider_priority" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sort-amount-up text-gray-400 mr-2"></i>优先级
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="provider_priority"
                               name="priority"
                               value="0"
                               min="0">
                        <p class="text-sm text-gray-500 mt-2">数字越大优先级越高</p>
                    </div>

                    <div>
                        <label for="provider_rate_limit" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tachometer-alt text-gray-400 mr-2"></i>速率限制
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="provider_rate_limit"
                               name="rate_limit_per_minute"
                               placeholder="60"
                               min="0">
                        <p class="text-sm text-gray-500 mt-2">请求数/分钟</p>
                    </div>

                    <div>
                        <label for="provider_concurrent_limit" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-users text-gray-400 mr-2"></i>并发限制
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="provider_concurrent_limit"
                               name="concurrent_limit"
                               value="10"
                               min="1">
                    </div>
                </div>

                <div>
                    <label for="provider_config_schema" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-cogs text-gray-400 mr-2"></i>配置项JSON Schema
                    </label>
                    <textarea class="input w-full min-h-[120px] resize-none font-mono text-sm"
                              id="provider_config_schema"
                              name="config_schema"
                              rows="4"
                              placeholder='{"api_key": {"type": "string", "required": true}, "organization": {"type": "string", "required": false}}'></textarea>
                    <p class="text-sm text-gray-500 mt-2">定义供应商的额外配置字段，使用JSON格式；API Key 已统一放在上方。</p>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   id="provider_is_active"
                                   name="is_active"
                                   value="1"
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                   checked>
                            <span class="ml-2 text-sm text-gray-700">启用此供应商</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="button" class="btn btn-secondary" onclick="closeProviderModal()">
                            <i class="fas fa-times mr-2"></i>取消
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitProviderBtn">
                            <i class="fas fa-save mr-2"></i>保存供应商
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 供应商模型列表模态框 -->
    <div class="modal hidden" id="providerModelsModal" role="dialog" aria-labelledby="providerModelsModalTitle">
        <div class="modal-content w-full max-w-4xl">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900" id="providerModelsModalTitle">模型列表</h3>
                    <p class="text-sm text-gray-500 mt-1" id="providerModelsModalSubtitle">查看该供应商下的模型</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeProviderModelsModal()" aria-label="关闭">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500" id="providerModelsCount">0 个模型</span>
                <button type="button" class="btn btn-primary btn-sm" id="providerModelsAddBtn">
                    <i class="fas fa-plus mr-2"></i>新增模型
                </button>
            </div>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table">
                        <thead>
                        <tr>
                            <th class="text-left">模型</th>
                            <th class="text-left">类型</th>
                            <th class="text-left">价格</th>
                            <th class="text-left">状态</th>
                            <th class="text-left">操作</th>
                        </tr>
                        </thead>
                        <tbody id="provider-models-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 模型模态框 -->
    <div class="modal hidden" id="modelModal" role="dialog">
        <div class="modal-content w-full max-w-3xl">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900" id="modelModalTitle">添加模型</h3>
                    <p class="text-sm text-gray-500 mt-1">为供应商添加新的AI模型</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeModelModal()" aria-label="关闭">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form id="modelForm" class="space-y-6">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="model_id" name="model_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="model_provider_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-server text-gray-400 mr-2"></i>供应商 <span class="text-red-500">*</span>
                        </label>
                        <select class="input w-full" id="model_provider_id" name="provider_id" required>
                            <option value="">请选择供应商</option>
                            <!-- 供应商选项将通过JavaScript动态加载 -->
                        </select>
                    </div>

                    <div>
                        <label for="model_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-brain text-gray-400 mr-2"></i>模型名称 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               class="input w-full"
                               id="model_name"
                               name="name"
                               required
                               placeholder="例如：gpt-4、claude-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="model_display_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heading text-gray-400 mr-2"></i>显示名称
                        </label>
                        <input type="text"
                               class="input w-full"
                               id="model_display_name"
                               name="display_name"
                               placeholder="例如：GPT-4 Turbo">
                    </div>

                    <div>
                        <label for="model_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag text-gray-400 mr-2"></i>模型类型 <span class="text-red-500">*</span>
                        </label>
                        <select class="input w-full" id="model_type" name="model_type" required>
                            <option value="">请选择类型</option>
                            <option value="chat">对话模型</option>
                            <option value="completion">补全模型</option>
                            <option value="embedding">嵌入模型</option>
                            <option value="image">图像模型</option>
                            <option value="audio">语音模型</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="model_context_length" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-ruler text-gray-400 mr-2"></i>上下文长度
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="model_context_length"
                               name="context_length"
                               placeholder="4096"
                               min="1">
                        <p class="text-sm text-gray-500 mt-2">最大token数</p>
                    </div>

                    <div>
                        <label for="model_max_tokens" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-signature text-gray-400 mr-2"></i>最大输出
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="model_max_tokens"
                               name="max_tokens"
                               placeholder="2048"
                               min="1">
                    </div>

                    <div>
                        <label for="model_sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i>排序
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="model_sort_order"
                               name="sort_order"
                               value="0"
                               min="0">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="model_input_price" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>输入价格
                        </label>
                        <input type="number"
                               step="0.000001"
                               class="input w-full"
                               id="model_input_price"
                               name="input_price_per_1k"
                               placeholder="0.001">
                        <p class="text-sm text-gray-500 mt-2">美元/1K tokens</p>
                    </div>

                    <div>
                        <label for="model_output_price" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>输出价格
                        </label>
                        <input type="number"
                               step="0.000001"
                               class="input w-full"
                               id="model_output_price"
                               name="output_price_per_1k"
                               placeholder="0.002">
                        <p class="text-sm text-gray-500 mt-2">美元/1K tokens</p>
                    </div>
                </div>

                <div>
                    <label for="model_capabilities" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bolt text-gray-400 mr-2"></i>能力配置
                    </label>
                    <textarea class="input w-full min-h-[80px] resize-none font-mono text-sm"
                              id="model_capabilities"
                              name="capabilities"
                              rows="3"
                              placeholder='{"vision": true, "function_calling": true, "json_mode": false}'></textarea>
                    <p class="text-sm text-gray-500 mt-2">定义模型支持的特殊能力，使用JSON格式</p>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   id="model_is_active"
                                   name="is_active"
                                   value="1"
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                   checked>
                            <span class="ml-2 text-sm text-gray-700">启用此模型</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="button" class="btn btn-secondary" onclick="closeModelModal()">
                            <i class="fas fa-times mr-2"></i>取消
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitModelBtn">
                            <i class="fas fa-save mr-2"></i>保存模型
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 全局变量
        let allProviders = [];
        let allModels = [];
        let activeProviderId = '';
        let modelsLoaded = false;

        // 页面加载完成后初始化数据
        document.addEventListener('DOMContentLoaded', function() {
            loadAllData();
        });

        // 加载所有数据
        async function loadAllData() {
            try {
                await loadProviders();
            } catch (error) {
                console.error('加载数据失败:', error);
                showToast('加载数据失败，请刷新页面重试', 'error');
            }
        }

        // 加载供应商数据
        async function loadProviders() {
            const loadingElement = document.getElementById('providers-loading');

            try {
                const response = await window.taskApiFetch('/api/v2/llm/providers');
                const data = await response.json();

                if (data.code === 9999) {
                    allProviders = data.result || [];
                    renderProviders(allProviders);
                    populateProviderOptions();

                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }
                } else {
                    throw new Error(data.msg || '加载失败');
                }
            } catch (error) {
                console.error('加载供应商失败:', error);
                if (loadingElement) {
                    loadingElement.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-4"></i>
                    <p class="text-gray-500">加载供应商数据失败</p>
                    <button onclick="loadProviders()" class="btn btn-secondary btn-sm mt-2">
                        <i class="fas fa-redo mr-2"></i>重试
                    </button>
                </div>
            `;
                }
            }
        }

        // 渲染供应商列表
        function renderProviders(providers) {
            const container = document.getElementById('provider-container');
            if (!container) return;

            if (providers.length === 0) {
                container.innerHTML = `
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-server text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 font-medium">暂无供应商</p>
                <p class="text-sm text-gray-500 mt-1">点击"添加供应商"按钮创建新的供应商</p>
                <button onclick="openProviderModal()" class="btn btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i>添加供应商
                </button>
            </div>
        `;
                return;
            }

            container.innerHTML = providers.map(provider => `
        <div class="flex flex-col md:flex-row md:items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition">
            <div class="flex items-center min-w-0 flex-1">
                <div class="w-9 h-9 ${provider.is_active ? 'bg-blue-100' : 'bg-gray-100'} rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="fas fa-server ${provider.is_active ? 'text-blue-600' : 'text-gray-400'}"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h4 class="font-semibold text-gray-900 truncate">${provider.name}</h4>
                        <span class="badge ${provider.is_active ? 'badge-success' : 'badge-secondary'}">${provider.is_active ? '启用' : '禁用'}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mt-1">
                        <span>${provider.slug}</span>
                        <span>${getApiTypeLabel(provider.api_type)}</span>
                        <span>优先级 ${provider.priority}</span>
                        ${provider.base_url ? `<span class="font-mono truncate max-w-md" title="${provider.base_url}">${provider.base_url}</span>` : ''}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 md:flex-shrink-0">
                <button onclick="manageModelsForProvider(${provider.id})" class="btn btn-outline btn-sm" title="查看模型">
                    <i class="fas fa-brain mr-1"></i>查看模型
                </button>
                <button onclick="editProvider(${provider.id})" class="btn btn-outline btn-sm" title="编辑">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteProvider(${provider.id})" class="btn btn-outline btn-sm text-red-600 hover:bg-red-50" title="删除">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
        }

        // 加载模型数据
        async function loadModels() {
            const loadingElement = document.getElementById('models-loading');

            try {
                const response = await window.taskApiFetch('/api/v2/llm/models');
                const data = await response.json();

                if (data.code === 9999) {
                    allModels = data.result || [];
                    modelsLoaded = true;
                    filterModels();

                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }
                } else {
                    throw new Error(data.msg || '加载失败');
                }
            } catch (error) {
                console.error('加载模型失败:', error);
                const tbody = document.getElementById('model-tbody');
                if (tbody) {
                    tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-4"></i>
                        <p class="text-gray-500">加载模型数据失败</p>
                        <button onclick="loadModels()" class="btn btn-secondary btn-sm mt-2">
                            <i class="fas fa-redo mr-2"></i>重试
                        </button>
                    </td>
                </tr>
            `;
                }
            }
        }

        // 填充供应商选项
        function populateProviderOptions() {
            const formSelects = [
                document.getElementById('model_provider_id')
            ];

            formSelects.forEach(select => {
                if (select) {
                    select.innerHTML = '<option value="">请选择供应商</option>' +
                        allProviders.map(provider =>
                            `<option value="${provider.id}">${provider.name}</option>`
                        ).join('');
                }
            });

            const filterSelects = [
                document.getElementById('model-filter-provider')
            ];

            filterSelects.forEach(select => {
                if (select) {
                    select.innerHTML = '<option value="">所有供应商</option>' +
                        allProviders.map(provider =>
                            `<option value="${provider.id}">${provider.name}</option>`
                        ).join('');
                }
            });
        }

        function buildApiErrorMessage(data, fallback) {
            let errorMessage = (data && data.msg) ? data.msg : fallback;
            if (data && data.result && typeof data.result === 'object') {
                const fieldErrors = Object.values(data.result).flat().filter(Boolean);
                if (fieldErrors.length > 0) {
                    errorMessage += ': ' + fieldErrors.join('；');
                }
            }
            return errorMessage;
        }

        function getProviderName(providerId) {
            const provider = providerId ? allProviders.find(p => p.id == providerId) : null;
            return provider ? provider.name : '全部供应商';
        }

        function updateManagedViewHeader(viewName, providerId) {
            const providerName = getProviderName(providerId);
            const currentLabel = document.getElementById('current-view-label');

            if (viewName === 'models') {
                currentLabel.textContent = providerName + ' / 模型';
                document.getElementById('models-view-title').textContent = providerName + '模型列表';
                document.getElementById('models-view-subtitle').textContent = providerId ? '管理该供应商下的AI模型' : '管理各个供应商的AI模型';
            }

        }

        // 视图切换
        function showLlmView(viewName, providerId = '') {
            activeProviderId = providerId || '';
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });

            const activeContent = document.getElementById(`${viewName}-view`);
            activeContent.classList.remove('hidden');
            activeContent.classList.add('active');

            const isProviderView = viewName === 'providers';
            document.getElementById('back-to-providers').classList.toggle('hidden', isProviderView);
            document.getElementById('view-separator').style.display = isProviderView ? 'none' : '';
            const currentLabel = document.getElementById('current-view-label');
            currentLabel.style.display = isProviderView ? 'none' : '';

            if (viewName === 'models') {
                updateManagedViewHeader('models', activeProviderId);
                document.getElementById('model-filter-provider').value = activeProviderId;
                if (!modelsLoaded) {
                    loadModels();
                } else {
                    filterModels();
                }
            }

        }

        // 过滤模型
        function filterModels() {
            const providerId = document.getElementById('model-filter-provider').value;
            activeProviderId = providerId;
            updateManagedViewHeader('models', providerId);
            const filteredModels = providerId ?
                allModels.filter(model => model.provider_id == providerId) :
                allModels;

            renderModels(filteredModels);
        }


        // 渲染模型表格
        function renderModels(models) {
            const tbody = document.getElementById('model-tbody');
            if (!tbody) return;

            if (models.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-brain text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">暂无模型</p>
                    <p class="text-sm text-gray-500 mt-1">点击"添加模型"按钮创建新的AI模型</p>
                    <button onclick="openModelModal()" class="btn btn-primary mt-4">
                        <i class="fas fa-plus mr-2"></i>添加模型
                    </button>
                </td>
            </tr>
        `;
                return;
            }

            tbody.innerHTML = models.map(model => {
                const provider = allProviders.find(p => p.id == model.provider_id) || {};

                return `
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 text-sm text-gray-500">${model.id}</td>
                <td class="py-3 px-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center mr-2">
                            <i class="fas fa-server text-gray-500 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-900">${provider.name || '未知'}</span>
                    </div>
                </td>
                <td class="py-3 px-4">
                    <div class="font-medium text-gray-900">${model.name}</div>
                    ${model.display_name ? `<div class="text-xs text-gray-500">${model.display_name}</div>` : ''}
                </td>
                <td class="py-3 px-4">
                    <span class="badge ${getModelTypeColor(model.model_type)}">
                        ${getModelTypeLabel(model.model_type)}
                    </span>
                </td>
                <td class="py-3 px-4">
                    ${model.input_price_per_1k || model.output_price_per_1k ? `
                    <div class="text-sm">
                        <span class="text-gray-900">$${model.input_price_per_1k || '0'}</span>
                        <span class="text-gray-500"> / </span>
                        <span class="text-gray-900">$${model.output_price_per_1k || '0'}</span>
                        <div class="text-xs text-gray-500">输入/输出</div>
                    </div>
                    ` : '<span class="text-gray-500">-</span>'}
                </td>
                <td class="py-3 px-4">
                    <span class="badge ${model.is_active ? 'badge-success' : 'badge-secondary'}">
                        ${model.is_active ? '启用' : '禁用'}
                    </span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center space-x-2">
                        <button onclick="testModel(${model.id})"
                                class="btn btn-outline btn-sm"
                                title="测试模型可用性">
                            <i class="fas fa-plug"></i>
                        </button>
                        <button onclick="editModel(${model.id})"
                                class="btn btn-outline btn-sm">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteModel(${model.id})"
                                class="btn btn-outline btn-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
            }).join('');
        }

        // 辅助函数：获取API类型标签
        function getApiTypeLabel(apiType) {
            const types = {
                'openai': 'OpenAI',
                'anthropic': 'Anthropic',
                'google': 'Google',
                'azure': 'Azure',
                'custom': '自定义'
            };
            return types[apiType] || apiType;
        }

        // 辅助函数：获取模型类型标签和颜色
        function getModelTypeLabel(modelType) {
            const types = {
                'chat': '对话',
                'completion': '补全',
                'embedding': '嵌入',
                'image': '图像',
                'audio': '语音'
            };
            return types[modelType] || modelType;
        }

        function getModelTypeColor(modelType) {
            const colors = {
                'chat': 'badge-primary',
                'completion': 'badge-success',
                'embedding': 'badge-warning',
                'image': 'badge-info',
                'audio': 'badge-purple'
            };
            return colors[modelType] || 'badge-secondary';
        }

        // 管理特定供应商的模型
        function manageModelsForProvider(providerId) {
            showLlmView('models', providerId);
        }

        // 打开供应商模态框
        function openProviderModal(resetForm = true) {
            const modal = document.getElementById('providerModal');
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            if (resetForm) {
                document.getElementById('providerForm').reset();
                document.getElementById('provider_id').value = '';
                document.getElementById('providerModalTitle').textContent = '添加供应商';
                document.getElementById('submitProviderBtn').innerHTML = '<i class="fas fa-save mr-2"></i>保存供应商';
            }
            document.getElementById('submitProviderBtn').disabled = false;
        }

        // 关闭供应商模态框
        function closeProviderModal() {
            const modal = document.getElementById('providerModal');
            modal.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }

        // 编辑供应商
        async function editProvider(id) {
            try {
                const response = await window.taskApiFetch(`/api/v2/llm/providers/${id}`);
                const data = await response.json();

                if (data.code === 9999) {
                    const provider = data.result;

                    document.getElementById('provider_id').value = provider.id;
                    document.getElementById('provider_name').value = provider.name || '';
                    document.getElementById('provider_slug').value = provider.slug || '';
                    document.getElementById('provider_description').value = provider.description || '';
                    document.getElementById('provider_base_url').value = provider.base_url || '';
                    document.getElementById('provider_api_key').value = '';
                    document.getElementById('provider_api_type').value = provider.api_type || '';
                    document.getElementById('provider_priority').value = provider.priority || 0;
                    document.getElementById('provider_rate_limit').value = provider.rate_limit_per_minute || '';
                    document.getElementById('provider_concurrent_limit').value = provider.concurrent_limit || 10;
                    document.getElementById('provider_config_schema').value = provider.config_schema ?
                        JSON.stringify(provider.config_schema, null, 2) : '';
                    document.getElementById('provider_is_active').checked = !!provider.is_active;

                    document.getElementById('providerModalTitle').textContent = '编辑供应商';
                    document.getElementById('submitProviderBtn').innerHTML = '<i class="fas fa-save mr-2"></i>更新供应商';

                    openProviderModal(false);
                }
            } catch (error) {
                console.error('编辑供应商失败:', error);
                showToast('加载供应商信息失败', 'error');
            }
        }

        // 删除供应商
        async function deleteProvider(id) {
            if (!confirm('确定要删除这个供应商吗？此操作将同时删除关联的模型！')) {
                return;
            }

            try {
                const response = await window.taskApiFetch(`/api/v2/llm/providers/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.code === 9999) {
                    showToast('供应商删除成功', 'success');
                    await loadAllData();
                    showLlmView('providers');
                } else {
                    throw new Error(data.msg || '删除失败');
                }
            } catch (error) {
                console.error('删除供应商失败:', error);
                showToast('删除失败: ' + (error.message || '未知错误'), 'error');
            }
        }

        // 供应商表单提交
        document.getElementById('providerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitProviderBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>处理中...';
            submitBtn.disabled = true;

            const id = document.getElementById('provider_id').value;
            const url = id ? `/api/v2/llm/providers/${id}` : '/api/v2/llm/providers';
            const method = id ? 'PUT' : 'POST';

            const formData = {
                _token: document.querySelector('input[name="_token"]').value,
                _method: method,
                name: document.getElementById('provider_name').value.trim(),
                slug: document.getElementById('provider_slug').value.trim(),
                description: document.getElementById('provider_description').value.trim(),
                base_url: document.getElementById('provider_base_url').value.trim(),
                api_type: document.getElementById('provider_api_type').value,
                priority: parseInt(document.getElementById('provider_priority').value) || 0,
                rate_limit_per_minute: document.getElementById('provider_rate_limit').value ?
                    parseInt(document.getElementById('provider_rate_limit').value) : null,
                concurrent_limit: parseInt(document.getElementById('provider_concurrent_limit').value) || 10,
                is_active: document.getElementById('provider_is_active').checked ? 1 : 0
            };

            const providerApiKey = document.getElementById('provider_api_key').value.trim();
            if (providerApiKey) {
                formData.api_key = providerApiKey;
            }

            // 解析JSON Schema
            const configSchema = document.getElementById('provider_config_schema').value.trim();
            if (configSchema) {
                try {
                    formData.config_schema = JSON.parse(configSchema);
                } catch (error) {
                    showToast('JSON Schema格式错误', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
            }

            try {
                const response = await window.taskApiFetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': formData._token
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.code === 9999) {
                    showToast(id ? '供应商更新成功' : '供应商创建成功', 'success');
                    closeProviderModal();
                    await loadAllData();
                    showLlmView('providers');
                } else {
                    throw new Error(buildApiErrorMessage(data, '保存失败'));
                }
            } catch (error) {
                console.error('保存供应商失败:', error);
                showToast('保存失败: ' + error.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // 打开模型模态框
        function openModelModal(resetForm = true) {
            const modal = document.getElementById('modelModal');
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            if (resetForm) {
                document.getElementById('modelForm').reset();
                document.getElementById('model_id').value = '';
                if (activeProviderId) {
                    document.getElementById('model_provider_id').value = activeProviderId;
                }
                document.getElementById('modelModalTitle').textContent = '添加模型';
                document.getElementById('submitModelBtn').innerHTML = '<i class="fas fa-save mr-2"></i>保存模型';
            }
            document.getElementById('submitModelBtn').disabled = false;
        }

        // 关闭模型模态框
        function closeModelModal() {
            const modal = document.getElementById('modelModal');
            modal.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 300);
            document.body.style.overflow = '';
        }

        // 编辑模型
        async function editModel(id) {
            try {
                const response = await window.taskApiFetch(`/api/v2/llm/models/${id}`);
                const data = await response.json();

                if (data.code === 9999) {
                    const model = data.result;

                    document.getElementById('model_id').value = model.id;
                    document.getElementById('model_provider_id').value = model.provider_id || '';
                    document.getElementById('model_name').value = model.name || '';
                    document.getElementById('model_display_name').value = model.display_name || '';
                    document.getElementById('model_type').value = model.model_type || '';
                    document.getElementById('model_context_length').value = model.context_length || '';
                    document.getElementById('model_max_tokens').value = model.max_tokens || '';
                    document.getElementById('model_input_price').value = model.input_price_per_1k || '';
                    document.getElementById('model_output_price').value = model.output_price_per_1k || '';
                    document.getElementById('model_sort_order').value = model.sort_order || 0;
                    document.getElementById('model_capabilities').value = model.capabilities ?
                        JSON.stringify(model.capabilities, null, 2) : '';
                    document.getElementById('model_is_active').checked = !!model.is_active;

                    document.getElementById('modelModalTitle').textContent = '编辑模型';
                    document.getElementById('submitModelBtn').innerHTML = '<i class="fas fa-save mr-2"></i>更新模型';

                    openModelModal(false);
                }
            } catch (error) {
                console.error('编辑模型失败:', error);
                showToast('加载模型信息失败', 'error');
            }
        }

        // 删除模型
        async function deleteModel(id) {
            if (!confirm('确定要删除这个模型吗？')) {
                return;
            }

            try {
                const response = await window.taskApiFetch(`/api/v2/llm/models/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.code === 9999) {
                    showToast('模型删除成功', 'success');
                    await loadModels();
                } else {
                    throw new Error(data.msg || '删除失败');
                }
            } catch (error) {
                console.error('删除模型失败:', error);
                showToast('删除失败: ' + (error.message || '未知错误'), 'error');
            }
        }

        // 模型表单提交
        document.getElementById('modelForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitModelBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>处理中...';
            submitBtn.disabled = true;

            const id = document.getElementById('model_id').value;
            const url = id ? `/api/v2/llm/models/${id}` : '/api/v2/llm/models';
            const method = id ? 'PUT' : 'POST';

            const formData = {
                _token: document.querySelector('input[name="_token"]').value,
                _method: method,
                provider_id: parseInt(document.getElementById('model_provider_id').value),
                name: document.getElementById('model_name').value.trim(),
                display_name: document.getElementById('model_display_name').value.trim() || null,
                model_type: document.getElementById('model_type').value,
                context_length: document.getElementById('model_context_length').value ?
                    parseInt(document.getElementById('model_context_length').value) : null,
                max_tokens: document.getElementById('model_max_tokens').value ?
                    parseInt(document.getElementById('model_max_tokens').value) : null,
                input_price_per_1k: document.getElementById('model_input_price').value ?
                    parseFloat(document.getElementById('model_input_price').value) : null,
                output_price_per_1k: document.getElementById('model_output_price').value ?
                    parseFloat(document.getElementById('model_output_price').value) : null,
                sort_order: parseInt(document.getElementById('model_sort_order').value) || 0,
                is_active: document.getElementById('model_is_active').checked ? 1 : 0
            };

            // 解析能力配置
            const capabilities = document.getElementById('model_capabilities').value.trim();
            if (capabilities) {
                try {
                    formData.capabilities = JSON.parse(capabilities);
                } catch (error) {
                    showToast('能力配置格式错误', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
            }

            try {
                const response = await window.taskApiFetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': formData._token
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.code === 9999) {
                    showToast(id ? '模型更新成功' : '模型创建成功', 'success');
                    closeModelModal();
                    await loadModels();
                } else {
                    throw new Error(buildApiErrorMessage(data, '保存失败'));
                }
            } catch (error) {
                console.error('保存模型失败:', error);
                showToast('保存失败: ' + error.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // 测试模型可用性
        async function testModel(id) {
            showToast('正在测试模型...', 'info');

            try {
                const response = await window.taskApiFetch(`/api/v2/llm/models/${id}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.code === 9999) {
                    const result = data.result || {};
                    const msg = `模型可用：${result.model_name || id}`;
                    showToast(msg, 'success');
                } else {
                    throw new Error(data.msg || '测试失败');
                }
            } catch (error) {
                console.error('测试模型失败:', error);
                showToast('模型测试失败: ' + error.message, 'error');
            }
        }

        // 全局点击关闭模态框
        document.addEventListener('click', (e) => {
            ['providerModal', 'modelModal'].forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && modal.classList.contains('show') && e.target === modal) {
                    const closeFunc = {
                        'providerModal': closeProviderModal,
                        'modelModal': closeModelModal
                    }[modalId];
                    closeFunc();
                }
            });
        });

        // ESC键关闭模态框
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeProviderModal();
                closeModelModal();
            }
        });

        // 显示Toast提示
        function showToast(message, type = 'info') {
            const existingToast = document.getElementById('global-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const typeConfig = {
                success: { icon: 'fa-check-circle', bgColor: 'bg-green-100', textColor: 'text-green-800', borderColor: 'border-green-200' },
                error: { icon: 'fa-exclamation-circle', bgColor: 'bg-red-100', textColor: 'text-red-800', borderColor: 'border-red-200' },
                warning: { icon: 'fa-exclamation-triangle', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', borderColor: 'border-yellow-200' },
                info: { icon: 'fa-info-circle', bgColor: 'bg-blue-100', textColor: 'text-blue-800', borderColor: 'border-blue-200' }
            };

            const config = typeConfig[type] || typeConfig.info;

            const toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = `fixed top-6 right-6 ${config.bgColor} ${config.textColor} ${config.borderColor} border rounded-lg px-4 py-3 shadow-lg z-[10000] flex items-center space-x-3 max-w-sm fade-in`;
            toast.innerHTML = `
        <i class="fas ${config.icon} text-lg"></i>
        <span>${message}</span>
    `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 3000);
        }
    </script>

    <style>
        /* 选项卡样式 */
        .tab-button {
            border-bottom-width: 2px;
            padding-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .tab-button:hover {
            color: var(--gray-700);
            border-color: var(--gray-300);
        }

        .tab-button.active {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* 内容区域切换 */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* 徽章颜色 */
        .badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #8a6cff;
        }

        /* 卡片悬停效果 */
        .card {
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* 文字截断 */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* 动画 */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* 模态框动画 */
        .modal {
            animation: fadeIn 0.3s ease-out;
        }

        .modal .modal-content {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .grid-cols-1, .grid-cols-2, .grid-cols-3, .grid-cols-4 {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 20px auto;
                max-height: 90vh;
                overflow-y: auto;
            }
        }

        /* 自定义滚动条 */
        .modal-content {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) var(--gray-100);
        }

        .modal-content::-webkit-scrollbar {
            width: 6px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
    </style>
@endsection
