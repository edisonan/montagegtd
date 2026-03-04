@extends('layouts.app')

@section('title', $mind->name . ' - 编辑思维导图 - 蒙太奇')
@section('description', '编辑思维导图：' . $mind->name)


@section('content')
    <link type="text/css" rel="stylesheet" href="{{ url('/css/jsmind.css') }}">
    <!-- Markdown Editor -->
    <link href="/css/markdown-editor.css" rel="stylesheet">
    <style>
        /* 思维导图容器 */
        #jsmind_container {
            width: 100%;
            height: 600px;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            background: white;
            position: relative;
            touch-action: none;
            transition: all 0.3s ease;
        }

        /* 加载状态 */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            border-radius: 12px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 12px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* 缩放控制 */
        .zoom-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 50;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        .zoom-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: var(--gray-100);
            color: var(--gray-700);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .zoom-btn:hover {
            background: var(--gray-200);
            transform: scale(1.05);
        }

        .zoom-btn.primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        /* 节点选中样式 */
        .jsmind-selected-node {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3) !important;
            border-color: var(--primary-color) !important;
        }

        /* 编辑面板 */
        .editor-panel {
            background: var(--gray-50);
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--gray-200);
        }

        .editor-panel-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-200);
        }

        .editor-panel-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* 响应式调整 */
        @media (max-width: 992px) {
            #jsmind_container {
                height: 500px;
            }

            .zoom-controls {
                bottom: 10px;
                right: 10px;
            }
        }

        @media (max-width: 768px) {
            #jsmind_container {
                height: 400px;
            }

            .editor-panel {
                margin-top: 16px;
            }
        }

        /* 全屏模式 */
        .fullscreen-mode {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999 !important;
            margin: 0 !important;
            padding: 0 !important;
            border-radius: 0 !important;
            border: none !important;
        }

        .fullscreen-mode #jsmind_container {
            height: calc(100vh - 60px) !important;
            border-radius: 0 !important;
            border: none !important;
        }

        /* 工具栏样式 */
        .mind-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .mind-toolbar .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 500;
        }

        /* 节点统计 */
        .node-stats {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: var(--gray-600);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* 快捷键提示 */
        .keyboard-hint {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 8px;
            color: var(--gray-500);
            font-size: 11px;
        }

        kbd {
            padding: 2px 6px;
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            background: var(--gray-100);
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 11px;
            line-height: 1;
        }

        /* 节点详情卡片 */
        .node-detail-card {
            margin-top: 16px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .node-detail-card h5 {
            margin-bottom: 8px;
            color: var(--gray-800);
        }

        /* 保存状态指示器 */
        .save-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 12px;
        }

        .save-indicator.saving {
            color: var(--warning-color);
            background: rgba(245, 158, 11, 0.1);
        }

        .save-indicator.saved {
            color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        /* 滚动条自定义 */
        .editor-panel pre,
        .editor-panel textarea {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) var(--gray-100);
        }

        .editor-panel pre::-webkit-scrollbar,
        .editor-panel textarea::-webkit-scrollbar {
            width: 6px;
        }

        .editor-panel pre::-webkit-scrollbar-track,
        .editor-panel textarea::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        .editor-panel pre::-webkit-scrollbar-thumb,
        .editor-panel textarea::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
    </style>
    <div class="fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- 页面标题和操作栏 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $mind->name }}</h1>
                        <p class="text-gray-600 mt-1">编辑思维导图</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- 统计信息 -->
                    <div class="node-stats hidden sm:flex">
                        <div class="stat-item">
                            <i class="fas fa-circle-nodes text-purple-500"></i>
                            <span>节点: <span id="node_count">0</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comment-alt text-blue-500"></i>
                            <span>备注: <span id="remark_count">0</span></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-layer-group text-green-500"></i>
                            <span>深度: <span id="depth_count">0</span></span>
                        </div>
                    </div>

                    <!-- 保存状态 -->
                    <div class="save-indicator saved" id="saveIndicator">
                        <i class="fas fa-check-circle"></i>
                        <span>已保存</span>
                    </div>

                    <!-- 视图切换 -->
                    <div class="flex items-center gap-2">
                        <a href="{{ '/mindoutlineviewv2/' . $mind->id }}"
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-list-alt mr-2"></i>
                            大纲视图
                        </a>
                        <button id="fullscreen_toggle" class="btn btn-outline btn-sm">
                            <i class="fas fa-expand mr-2"></i>
                            全屏
                        </button>
                        <a href="{{ '/minds' }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-2"></i>
                            返回列表
                        </a>
                    </div>
                </div>
            </div>

            <!-- 成功消息提示 -->
            @include('common.success')

            <!-- 思维导图编辑区域 -->
            <div class="card card-elevated">
                <!-- 工具栏 -->
                <div class="p-4 border-b border-gray-200">
                    <div class="mind-toolbar">
                        <button id="add_node_btn" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>
                            添加子节点
                            <span class="keyboard-hint"><kbd>Tab</kbd></span>
                        </button>

                        <button id="edit_node_btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-edit mr-1"></i>
                            编辑节点
                            <span class="keyboard-hint"><kbd>F2</kbd></span>
                        </button>

                        <button id="delete_node_btn" class="btn btn-outline btn-sm hover:bg-red-50 hover:text-red-600 hover:border-red-300">
                            <i class="fas fa-trash-alt mr-1"></i>
                            删除节点
                            <span class="keyboard-hint"><kbd>Del</kbd></span>
                        </button>

                        <button id="toggle_node_btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-expand-alt mr-1"></i>
                            展开/折叠
                            <span class="keyboard-hint"><kbd>Space</kbd></span>
                        </button>

                        <div class="h-6 w-px bg-gray-300"></div>

                        <button id="screenshot_btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-camera mr-1"></i>
                            截屏保存
                        </button>

                        <button id="reset_view_btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-sync-alt mr-1"></i>
                            重置视图
                        </button>

                        <button id="center_view_btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-crosshairs mr-1"></i>
                            居中显示
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- 思维导图主区域 -->
                        <div class="lg:col-span-2">
                            <div id="jsmind_container">
                                <div class="loading-overlay" id="loadingOverlay">
                                    <div class="loading-spinner"></div>
                                    <p class="text-gray-600 mt-2">正在加载思维导图...</p>
                                </div>
                            </div>

                            <!-- 缩放控制 -->
                            <div class="zoom-controls">
                                <button class="zoom-btn primary" onclick="zoomIn()" title="放大">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="zoom-btn" onclick="resetZoom()" title="重置缩放">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="zoom-btn" onclick="zoomOut()" title="缩小">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 编辑面板 -->
                        <div class="lg:col-span-1">
                            <div class="editor-panel">
                                <div class="editor-panel-header">
                                    <div class="editor-panel-title">
                                        <i class="fas fa-edit text-blue-500"></i>
                                        节点编辑
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        当前选中: <span id="current_node_name">未选择</span>
                                    </p>
                                </div>

                                <div class="flex-1">
                                    <!-- 节点标题 -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            节点标题
                                        </label>
                                        <input type="text"
                                               id="node_title_input"
                                               class="input w-full"
                                               placeholder="输入节点标题">
                                    </div>

                                    <!-- 节点备注 -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            节点备注
                                            <span class="text-gray-500 font-normal">（支持Markdown）</span>
                                        </label>
                                        <textarea id="mind_content"
                                                  class="input w-full h-48 resize-none"
                                                  placeholder="输入节点备注...">{{ $mind->content }}</textarea>

                                        <div class="mt-2 flex items-center gap-2">
                                            <button onclick="showMarkdownHelp()"
                                                    class="text-sm text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-question-circle"></i>
                                                Markdown语法帮助
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 操作按钮 -->
                                    <div class="space-y-3">
                                        <button id="save_node_btn"
                                                class="btn btn-primary w-full"
                                                onclick="saveNodeChanges()">
                                            <i class="fas fa-save mr-2"></i>
                                            保存更改
                                        </button>

                                        <button id="add_child_btn"
                                                class="btn btn-outline w-full"
                                                onclick="addChildNode()">
                                            <i class="fas fa-plus-circle mr-2"></i>
                                            添加子节点
                                        </button>

                                        <div class="pt-4 border-t border-gray-200">
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                节点ID: <code id="current_node_id">-</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 预览区域 -->
                                <div id="mind_content_show" class="mt-6 p-4 bg-white rounded-lg border border-gray-200 hidden">
                                    <div class="text-sm text-gray-500 mb-2">预览：</div>
                                    <div class="prose prose-sm max-w-none" id="markdown_preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 底部信息栏 -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
                <div>
                    <span>最后更新: {{ $mind->updated_at ? $mind->updated_at->format('Y-m-d H:i') : '未知' }}</span>
                    <span class="mx-2">•</span>
                    <span>创建者: {{ Auth::user()->name }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="exportMind()" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-download mr-1"></i>
                        导出数据
                    </button>
                    <button onclick="printMind()" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-print mr-1"></i>
                        打印
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Markdown帮助模态框 -->
    <div id="markdown_help_modal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Markdown语法帮助</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('markdown_help_modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="prose prose-sm max-w-none">
                <h4>常用语法</h4>
                <table class="table">
                    <thead>
                    <tr>
                        <th>语法</th>
                        <th>效果</th>
                        <th>示例</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td># 标题</td>
                        <td>一级标题</td>
                        <td><code># 主标题</code></td>
                    </tr>
                    <tr>
                        <td>**粗体**</td>
                        <td><strong>粗体文本</strong></td>
                        <td><code>**重要内容**</code></td>
                    </tr>
                    <tr>
                        <td>*斜体*</td>
                        <td><em>斜体文本</em></td>
                        <td><code>*强调内容*</code></td>
                    </tr>
                    <tr>
                        <td>- 列表项</td>
                        <td>无序列表</td>
                        <td><code>- 第一项</code></td>
                    </tr>
                    <tr>
                        <td>`代码`</td>
                        <td><code>行内代码</code></td>
                        <td><code>`console.log()`</code></td>
                    </tr>
                    <tr>
                        <td>[链接](url)</td>
                        <td>超链接</td>
                        <td><code>[蒙太奇](https://congcong.us)</code></td>
                    </tr>
                    </tbody>
                </table>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('markdown_help_modal')">
                        关闭
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ url('/js/jsmind.js') }}?v={{ time() }}"></script>
    <script src="{{ url('/js/jsmind.screenshot.js') }}"></script>
    <script src="{{ url('/js/jsmind.draggable.js') }}"></script>
    <script src="{{ url('/js/jsmind.zoom.js') }}"></script>
    <script src="/js/markdown-editor.js"></script>
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        // 全局变量
        let mindManager = {
            _jm: null,
            taskToken: "{{ csrf_token() }}",
            mindId: "{{ $mind->id }}",
            mindName: "{{ $mind->name }}",
            isFullscreen: false,
            saveTimeout: null,
            selectedNode: null
        };

        // 初始化
        $(document).ready(function() {
            // 初始化Markdown编辑器
            if (typeof markdownEditor !== 'undefined') {
                markdownEditor.init('mind_content', 'markdown_preview');
            }

            // 加载思维导图
            loadMindData();

            // 绑定按钮事件
            bindButtonEvents();

            // 绑定键盘快捷键
            bindKeyboardShortcuts();

            // 监听窗口大小变化
            window.addEventListener('resize', handleResize);
        });

        // 绑定按钮事件
        function bindButtonEvents() {
            // 工具栏按钮
            $('#add_node_btn').click(addChildNode);
            $('#edit_node_btn').click(editSelectedNode);
            $('#delete_node_btn').click(deleteSelectedNode);
            $('#toggle_node_btn').click(toggleSelectedNode);
            $('#screenshot_btn').click(takeScreenshot);
            $('#reset_view_btn').click(resetView);
            $('#center_view_btn').click(centerView);

            // 全屏切换
            $('#fullscreen_toggle').click(toggleFullscreen);

            // 保存按钮
            $('#save_node_btn').click(saveNodeChanges);

            // 标题输入框实时保存
            $('#node_title_input').on('input', debounce(function() {
                const selected = getCurrentSelectedNode();
                if (selected) {
                    updateNodeTitle(selected.id, $(this).val());
                }
            }, 500));

            // Markdown内容实时保存
            $('#mind_content').on('input', debounce(function() {
                const selected = getCurrentSelectedNode();
                if (selected) {
                    saveNodeContent(selected.id, $(this).val());
                }
            }, 1000));
        }

        // 绑定键盘快捷键
        function bindKeyboardShortcuts() {
            $(document).on('keydown', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    return; // 忽略输入框中的按键
                }

                switch(e.key) {
                    case 'Tab':
                        e.preventDefault();
                        addChildNode();
                        break;
                    case 'F2':
                        e.preventDefault();
                        editSelectedNode();
                        break;
                    case 'Delete':
                        e.preventDefault();
                        deleteSelectedNode();
                        break;
                    case ' ':
                        e.preventDefault();
                        toggleSelectedNode();
                        break;
                    case '+':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            zoomIn();
                        }
                        break;
                    case '-':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            zoomOut();
                        }
                        break;
                    case '0':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            resetZoom();
                        }
                        break;
                }
            });
        }

        // 加载思维导图数据
        function loadMindData() {
            if (!apiRequest) {
                showError('API客户端未初始化');
                createDefaultMind();
                return;
            }

            apiRequest('GET', '/minds/{{ $mind->id }}/jsmind', {}).then(function(response) {
                if (response.code == 9999 && response.result && response.result.jsmind_datas) {
                    try {
                        const mindData = JSON.parse(response.result.jsmind_datas);
                        initializeJsMind(mindData);
                    } catch (error) {
                        console.error('解析数据失败:', error);
                        showError('数据格式错误');
                        createDefaultMind();
                    }
                } else {
                    showError('数据加载失败');
                    createDefaultMind();
                }
            }).catch(function() {
                showError('网络错误，加载失败');
                createDefaultMind();
            });
        }

        // 初始化jsMind
        function initializeJsMind(mindData) {
            const options = {
                container: 'jsmind_container',
                editable: true,
                theme: 'primary',
                mode: 'full',
                support_html: true,
                view: {
                    hmargin: 120,
                    vmargin: 60,
                    line_width: 2,
                    line_color: '#cbd5e1'
                },
                layout: {
                    hspace: 80,
                    vspace: 40,
                    pspace: 25
                },
                shortcut: {
                    enable: true,
                    mappings: {
                        addchild: 9,
                        addbrother: 13,
                        editnode: 113,
                        delnode: 46,
                        toggle: 32
                    }
                }
            };

            // 创建jsMind实例
            mindManager._jm = new jsMind(options);

            // 应用插件
            if (typeof jsMind.draggable !== 'undefined') {
                jsMind.draggable(mindManager._jm);
            }

            if (typeof jsMind.zoom !== 'undefined') {
                jsMind.zoom(mindManager._jm);
                mindManager._jm.set_zoom(1);
            }

            // 显示思维导图
            mindManager._jm.show(mindData);

            // 隐藏加载状态
            $('#loadingOverlay').fadeOut(300);

            // 绑定事件
            mindManager._jm.add_event_listener(function(type, data) {
                if (type === jsMind.event_type.select) {
                    handleNodeSelect(data.node);
                }
            });

            // 初始选中根节点
            const rootId = "{{ $mind->id }}";
            if (mindManager._jm.get_node(rootId)) {
                mindManager._jm.select_node(rootId);
            }

            // 更新统计
            updateStatistics();
        }

        // 创建默认思维导图
        function createDefaultMind() {
            const defaultData = {
                meta: {
                    name: mindManager.mindName,
                    author: "{{ Auth::user()->name }}",
                    version: "1.0"
                },
                format: "node_tree",
                data: {
                    id: mindManager.mindId,
                    topic: mindManager.mindName,
                    expanded: true
                }
            };

            $('#loadingOverlay').html(`
        <div class="text-center">
            <div class="w-12 h-12 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-500"></i>
            </div>
            <p class="text-gray-600 mb-4">数据加载失败，创建默认导图</p>
            <button onclick="initializeJsMind(${JSON.stringify(defaultData)})"
                    class="btn btn-primary btn-sm">
                继续
            </button>
        </div>
    `);
        }

        // 处理节点选择
        function handleNodeSelect(node) {
            mindManager.selectedNode = node;

            // 更新编辑面板
            $('#current_node_name').text(node.topic);
            $('#current_node_id').text(node.id);
            $('#node_title_input').val(node.topic);

            // 更新内容编辑器
            const content = node.data?.content || '';
            $('#mind_content').val(content);

            // 更新Markdown预览
            if (typeof markdownEditor !== 'undefined' && markdownEditor.updatePreview) {
                markdownEditor.updatePreview();
            }

            // 显示预览区域
            if (content.trim()) {
                $('#mind_content_show').removeClass('hidden');
            } else {
                $('#mind_content_show').addClass('hidden');
            }
        }

        function getCurrentSelectedNode() {
            if (!mindManager._jm) {
                return null;
            }
            const current = mindManager._jm.get_selected_node();
            if (current) {
                mindManager.selectedNode = current;
                return current;
            }
            return mindManager.selectedNode || null;
        }

        // 添加子节点
        function addChildNode() {
            if (!mindManager._jm) return;

            const selected = mindManager._jm.get_selected_node();
            if (!selected) {
                showNotification('请先选择一个节点', 'warning');
                return;
            }

            const title = prompt('请输入子节点标题:', '新节点');
            if (!title) return;

            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }

            apiRequest('POST', '/minds', {
                name: title,
                parent_mind_id: selected.id
            }).then(function(response) {
                if (response.code == 9999) {
                    // 添加节点到思维导图
                    mindManager._jm.add_node(selected, response.result.id, response.result.name);

                    // 选中新节点
                    const newNode = mindManager._jm.get_node(response.result.id);
                    if (newNode) {
                        mindManager._jm.select_node(response.result.id);
                    }

                    // 更新统计
                    updateStatistics();

                    showNotification('节点添加成功', 'success');
                } else {
                    showNotification('添加失败: ' + response.message, 'error');
                }
            }).catch(function() {
                showNotification('网络错误，添加失败', 'error');
            });
        }

        // 编辑选中节点
        function editSelectedNode() {
            const selected = getCurrentSelectedNode();
            if (!mindManager._jm || !selected) {
                showNotification('请先选择一个节点', 'warning');
                return;
            }

            if ($('#node_title_input').val() !== selected.topic) {
                $('#node_title_input').val(selected.topic);
            }
            $('#node_title_input').focus().select();
        }

        // 删除选中节点
        function deleteSelectedNode() {
            const selected = getCurrentSelectedNode();
            if (!mindManager._jm || !selected) {
                showNotification('请先选择一个节点', 'warning');
                return;
            }

            if (!confirm('确定删除该节点及其所有子节点吗？')) return;

            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }

            apiRequest('DELETE', '/minds/' + selected.id, {}).then(function(response) {
                if (response.code == 9999) {
                    mindManager._jm.remove_node(selected.id);
                    mindManager.selectedNode = null;

                    $('#current_node_name').text('未选择');
                    $('#node_title_input').val('');
                    $('#mind_content').val('');
                    $('#mind_content_show').addClass('hidden');

                    updateStatistics();
                    showNotification('节点删除成功', 'success');
                } else {
                    showNotification('删除失败: ' + response.message, 'error');
                }
            }).catch(function() {
                showNotification('网络错误，删除失败', 'error');
            });
        }

        // 切换节点展开/折叠
        function toggleSelectedNode() {
            const selected = getCurrentSelectedNode();
            if (!mindManager._jm || !selected) return;
            mindManager._jm.toggle_node(selected.id);
        }

        // 更新节点标题
        function updateNodeTitle(nodeId, title) {
            if (!title.trim()) return;

            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }

            apiRequest('PUT', '/minds/' + nodeId, {
                name: title,
            }).then(function(response) {
                if (response.code == 9999) {
                    mindManager._jm.update_node(nodeId, title);
                    showSaveStatus('success');
                } else {
                    showNotification('更新失败: ' + response.message, 'error');
                }
            }).catch(function() {
                showNotification('网络错误，更新失败', 'error');
            });
        }

        // 保存节点内容
        function saveNodeContent(nodeId, content) {
            if (!apiRequest) {
                showNotification('API客户端未初始化', 'error');
                return;
            }

            apiRequest('PUT', '/minds/' + nodeId, {
                content: content,
            }).then(function(response) {
                if (response.code == 9999) {
                    // 更新节点数据
                    const node = mindManager._jm.get_node(nodeId);
                    if (node) {
                        if (!node.data) node.data = {};
                        node.data.content = content;
                    }
                    showSaveStatus('success');
                } else {
                    showNotification('保存失败: ' + response.message, 'error');
                }
            }).catch(function() {
                showNotification('网络错误，保存失败', 'error');
            });
        }

        // 保存所有更改
        function saveNodeChanges() {
            const selected = getCurrentSelectedNode();
            if (!selected) {
                showNotification('请先选择一个节点', 'warning');
                return;
            }

            const title = $('#node_title_input').val();
            const content = $('#mind_content').val();

            if (title !== selected.topic) {
                updateNodeTitle(selected.id, title);
            }

            if (content !== (selected.data?.content || '')) {
                saveNodeContent(selected.id, content);
            }
        }

        // 更新统计信息
        function updateStatistics() {
            if (!mindManager._jm) return;

            const nodes = mindManager._jm.get_data().data;
            let nodeCount = 0;
            let remarkCount = 0;
            let maxDepth = 0;

            function countNodes(node, depth) {
                nodeCount++;
                maxDepth = Math.max(maxDepth, depth);
                if (node.data?.content) remarkCount++;

                if (node.children) {
                    for (const child of Object.values(node.children)) {
                        countNodes(child, depth + 1);
                    }
                }
            }

            countNodes(nodes, 0);

            $('#node_count').text(nodeCount);
            $('#remark_count').text(remarkCount);
            $('#depth_count').text(maxDepth);
        }

        // 缩放功能
        function zoomIn() {
            if (mindManager._jm?.zoom_in) mindManager._jm.zoom_in();
        }

        function zoomOut() {
            if (mindManager._jm?.zoom_out) mindManager._jm.zoom_out();
        }

        function resetZoom() {
            if (mindManager._jm?.set_zoom) mindManager._jm.set_zoom(1);
        }

        function resetView() {
            resetZoom();
            centerView();
        }

        function centerView() {
            if (mindManager._jm) mindManager._jm.center();
        }

        // 截屏功能
        function takeScreenshot() {
            if (mindManager._jm?.screenshot?.shootDownload) {
                mindManager._jm.screenshot.shootDownload();
                showNotification('截屏已保存', 'success');
            } else {
                showNotification('截屏功能不可用', 'error');
            }
        }

        // 全屏切换
        function toggleFullscreen() {
            const container = $('.container');
            const card = $('.card');

            if (!mindManager.isFullscreen) {
                container.addClass('fullscreen-mode');
                $('#jsmind_container').css('height', 'calc(100vh - 60px)');
                $('#fullscreen_toggle').html('<i class="fas fa-compress mr-2"></i>退出全屏');
                mindManager.isFullscreen = true;
            } else {
                container.removeClass('fullscreen-mode');
                $('#jsmind_container').css('height', '600px');
                $('#fullscreen_toggle').html('<i class="fas fa-expand mr-2"></i>全屏');
                mindManager.isFullscreen = false;
            }

            // 重新布局
            if (mindManager._jm) {
                setTimeout(() => {
                    mindManager._jm.resize();
                    mindManager._jm.center();
                }, 100);
            }
        }

        // 窗口大小变化处理
        function handleResize() {
            if (mindManager._jm) {
                mindManager._jm.resize();
            }
        }

        // Markdown帮助
        function showMarkdownHelp() {
            $('#markdown_help_modal').addClass('show');
        }

        function closeModal(modalId) {
            $('#' + modalId).removeClass('show');
        }

        // 显示保存状态
        function showSaveStatus(status) {
            const indicator = $('#saveIndicator');
            indicator.removeClass('saved saving');

            if (status === 'saving') {
                indicator.addClass('saving').html('<i class="fas fa-sync-alt fa-spin"></i> 保存中...');
            } else if (status === 'success') {
                indicator.addClass('saved').html('<i class="fas fa-check-circle"></i> 已保存');
                setTimeout(() => {
                    indicator.removeClass('saved');
                }, 2000);
            }
        }

        // 防抖函数
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // 显示通知
        function showNotification(message, type = 'info') {
            const colors = {
                success: 'green',
                error: 'red',
                warning: 'yellow',
                info: 'blue'
            };

            const icon = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };

            const notification = $(`
        <div class="fixed top-4 right-4 z-50 fade-in">
            <div class="card shadow-lg border-l-4 border-${colors[type]}-500">
                <div class="p-4 flex items-start gap-3">
                    <i class="fas fa-${icon[type]} text-${colors[type]}-500 text-lg"></i>
                    <p class="text-sm text-gray-800">${message}</p>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `);

            $('body').append(notification);

            notification.find('button').click(function() {
                notification.remove();
            });

            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }

        // 显示错误
        function showError(message) {
            showNotification(message, 'error');
        }

        // 导出功能
        function exportMind() {
            if (mindManager._jm) {
                const data = mindManager._jm.get_data('node_tree');
                const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `mind-${mindManager.mindId}-${Date.now()}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                showNotification('导图数据已导出', 'success');
            }
        }

        // 打印功能
        function printMind() {
            window.print();
        }
    </script>
@endsection
