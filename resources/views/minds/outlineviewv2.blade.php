@extends('layouts.app')

@section('title', $mind->name . ' - 思维导图 - 蒙太奇')
@section('description', '查看思维导图：' . $mind->name)

@section('content')
    <div class="fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- 页面标题和操作栏 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">思维导图</h1>
                        <p class="text-gray-600 mt-1">{{ $mind->name }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <!-- 操作按钮 -->
                    <div class="flex flex-wrap gap-2">
                        <button id="toggle_all_remarks" class="btn btn-secondary btn-sm">
                            <i class="fas fa-expand-alt mr-2"></i>
                            <span class="hidden sm:inline">折叠/展开备注</span>
                        </button>

                        <button id="toggle_focus_mode" class="btn btn-outline btn-sm">
                            <i class="fas fa-expand mr-2"></i>
                            <span class="hidden sm:inline">专注模式</span>
                        </button>

                        <button id="export_mind" class="btn btn-outline btn-sm">
                            <i class="fas fa-download mr-2"></i>
                            <span class="hidden sm:inline">导出</span>
                        </button>

                        <a href="{{ '/mind/' . $mind->id }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit mr-2"></i>
                            <span class="hidden sm:inline">编辑</span>
                        </a>

                        <a href="{{ '/minds' }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="hidden sm:inline">返回</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 思维导图容器 -->
            <div class="card card-elevated">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <!-- 统计信息 -->
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-layer-group text-purple-500"></i>
                                节点数: <span id="node_count" class="font-medium">0</span>
                            </span>
                                <span class="flex items-center gap-1">
                                <i class="fas fa-comment-alt text-blue-500"></i>
                                备注数: <span id="remark_count" class="font-medium">0</span>
                            </span>
                                <span class="flex items-center gap-1">
                                <i class="fas fa-clock text-gray-500"></i>
                                更新于: {{ $mind->updated_at ? $mind->updated_at->format('Y-m-d H:i') : '未知' }}
                            </span>
                            </div>
                        </div>

                        <!-- 视图选项 -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">结构:</label>
                                <select id="layout_type" class="input text-sm py-1 px-2">
                                    <option value="vertical">垂直</option>
                                    <option value="horizontal">水平</option>
                                    <option value="radial">放射</option>
                                </select>
                            </div>

                            <button id="fullscreen_toggle" class="btn btn-sm btn-outline">
                                <i class="fas fa-expand-arrows-alt mr-1"></i>
                                全屏
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 思维导图可视化区域 -->
                    <div id="mind_visualization" class="bg-white rounded-lg border border-gray-200 p-4 mb-6 hidden">
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sync-alt text-purple-500 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">可视化视图</h3>
                            <p class="text-gray-600 mb-6">正在开发中，敬请期待</p>
                            <button id="switch_to_list" class="btn btn-primary">
                                切换到列表视图
                            </button>
                        </div>
                    </div>

                    <!-- 列表视图容器 -->
                    <div id="list_container" class="custom-scrollbar" style="max-height: 600px; overflow-y: auto;">
                        <!-- 动态加载内容 -->
                        <div class="flex items-center justify-center py-12" id="loading_indicator">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto mb-4 border-4 border-gray-200 border-t-purple-500 rounded-full animate-spin"></div>
                                <p class="text-gray-600">正在加载思维导图数据...</p>
                            </div>
                        </div>

                        <div id="mind_list_view" class="hidden">
                            <!-- 内容将由JavaScript动态生成 -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 快捷操作面板 -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card">
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-bolt text-yellow-500"></i>
                            快捷操作
                        </h3>
                        <div class="space-y-3">
                            <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-plus text-blue-600"></i>
                                    </div>
                                    <span class="text-gray-700">快速添加子节点</span>
                                </div>
                                <kbd class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Tab</kbd>
                            </button>

                            <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-indent text-green-600"></i>
                                    </div>
                                    <span class="text-gray-700">缩进为子节点</span>
                                </div>
                                <kbd class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">→</kbd>
                            </button>

                            <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-outdent text-purple-600"></i>
                                    </div>
                                    <span class="text-gray-700">提升为同级节点</span>
                                </div>
                                <kbd class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">←</kbd>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-chart-line text-green-500"></i>
                            导图分析
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600">节点深度分布</span>
                                    <span class="text-gray-900 font-medium" id="max_depth">0</span>
                                </div>
                                <div class="progress h-2">
                                    <div id="depth_progress" class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600">备注覆盖率</span>
                                    <span class="text-gray-900 font-medium" id="remark_percentage">0%</span>
                                </div>
                                <div class="progress h-2">
                                    <div id="remark_progress" class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                                    建议：添加更多备注来丰富导图内容
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-share-alt text-blue-500"></i>
                            分享与协作
                        </h3>
                        <div class="space-y-3">
                            <button id="share_mind" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-link text-blue-600"></i>
                                    </div>
                                    <span class="text-gray-700">生成分享链接</span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </button>

                            <button id="export_pdf" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-green-600"></i>
                                    </div>
                                    <span class="text-gray-700">导出为PDF</span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </button>

                            <button id="print_mind" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-print text-gray-600"></i>
                                    </div>
                                    <span class="text-gray-700">打印导图</span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 分享模态框 -->
    <div id="share_modal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">分享思维导图</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">分享链接</label>
                    <div class="flex">
                        <input type="text" readonly
                               value="{{ url('/mind/view/' . $mind->id) }}"
                               class="input flex-1 rounded-r-none">
                        <button id="copy_link" class="btn btn-primary rounded-l-none">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">访问权限</label>
                    <select class="input w-full">
                        <option value="public">公开查看</option>
                        <option value="private" selected>仅自己可见</option>
                        <option value="link">链接访问</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('share_modal')">
                        取消
                    </button>
                    <button type="button" class="btn btn-primary">
                        保存设置
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        $(document).ready(function(){
            let mindData = null;
            let allRemarksVisible = true;
            let isFocusMode = false;
            let nodeCount = 0;
            let remarkCount = 0;
            let maxDepth = 0;

            // 加载思维导图数据
            function loadMindData() {
                if (!apiRequest) {
                    showError('API客户端未初始化');
                    return;
                }
                apiRequest('GET', '/minds/{{ $mind->id }}/jsmind', {}).then(function(res) {
                    if(res.code != 9999){
                        showError('加载失败，请稍后再试');
                    } else {
                        mindData = JSON.parse(res.result.jsmind_datas);
                        hideLoading();
                        renderMindList(mindData);
                        updateStats();
                    }
                }).catch(function() {
                    showError('网络错误，请检查连接后重试');
                });
            }

            // 渲染列表视图
            function renderMindList(data, level = 0) {
                const container = $('#mind_list_view');
                container.empty();

                function processNode(node, depth) {
                    const hasRemark = node.content && node.content.trim() !== '';
                    const nodeId = 'node-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

                    const nodeHtml = `
                <div class="mind-list-item" data-depth="${depth}" id="${nodeId}">
                    <div class="flex items-start gap-3 p-4 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0 pt-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                ${depth === 0 ? 'bg-purple-100 text-purple-600' :
                        depth === 1 ? 'bg-blue-100 text-blue-600' :
                            depth === 2 ? 'bg-green-100 text-green-600' :
                                'bg-gray-100 text-gray-600'}">
                                <i class="fas ${depth === 0 ? 'fa-sitemap' : 'fa-circle'} text-sm"></i>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-medium text-gray-900 ${depth === 0 ? 'text-lg' : ''}">
                                    ${escapeHtml(node.topic)}
                                </h3>

                                <div class="flex items-center gap-2">
                                    ${hasRemark ? `
                                        <button class="toggle-remark-btn btn btn-xs btn-outline">
                                            <i class="fas fa-comment-alt mr-1"></i>
                                            <span class="hidden sm:inline">备注</span>
                                        </button>
                                    ` : ''}

                                    <button class="node-actions-btn btn btn-xs btn-outline">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </div>
                            </div>

                            ${hasRemark ? `
                                <div class="mind-remark mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg hidden">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <p class="text-gray-700 whitespace-pre-wrap">${nl2br(escapeHtml(node.content))}</p>
                                        </div>
                                        <button class="close-remark-btn text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            ` : ''}

                            ${node.children && Object.keys(node.children).length > 0 ? `
                                <div class="mt-3">
                                    ${renderChildren(node.children, depth + 1)}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

                    return nodeHtml;
                }

                function renderChildren(children, depth) {
                    let html = '<div class="pl-8 border-l-2 border-gray-200 ml-4">';
                    $.each(children, function(index, child) {
                        html += processNode(child, depth);
                    });
                    html += '</div>';
                    return html;
                }

                const html = processNode(data.data, 0);
                container.html(html);
                container.removeClass('hidden');

                // 绑定事件
                bindNodeEvents();
            }

            // 绑定节点事件
            function bindNodeEvents() {
                // 切换备注显示
                $('.toggle-remark-btn').off('click').on('click', function(e) {
                    e.stopPropagation();
                    const remarkDiv = $(this).closest('.mind-list-item').find('.mind-remark');
                    remarkDiv.slideToggle(200);
                    $(this).toggleClass('btn-primary btn-outline');
                });

                // 关闭备注
                $('.close-remark-btn').off('click').on('click', function(e) {
                    e.stopPropagation();
                    $(this).closest('.mind-remark').slideUp(200);
                    $(this).closest('.mind-list-item').find('.toggle-remark-btn').removeClass('btn-primary').addClass('btn-outline');
                });

                // 节点操作
                $('.node-actions-btn').off('click').on('click', function(e) {
                    e.stopPropagation();
                    showNodeContextMenu($(this), $(this).closest('.mind-list-item'));
                });

                // 节点点击效果
                $('.mind-list-item').off('click').on('click', function(e) {
                    if (!$(e.target).closest('.toggle-remark-btn, .node-actions-btn, .close-remark-btn').length) {
                        $(this).toggleClass('bg-blue-50 border border-blue-100');
                    }
                });
            }

            // 更新统计数据
            function updateStats() {
                if (!mindData) return;

                function countNodes(node, depth) {
                    nodeCount++;
                    maxDepth = Math.max(maxDepth, depth);
                    if (node.content && node.content.trim() !== '') remarkCount++;

                    if (node.children) {
                        $.each(node.children, function(index, child) {
                            countNodes(child, depth + 1);
                        });
                    }
                }

                nodeCount = 0;
                remarkCount = 0;
                maxDepth = 0;
                countNodes(mindData.data, 0);

                $('#node_count').text(nodeCount);
                $('#remark_count').text(remarkCount);
                $('#max_depth').text(maxDepth + ' 层');

                const remarkPercentage = nodeCount > 0 ? Math.round((remarkCount / nodeCount) * 100) : 0;
                $('#remark_percentage').text(remarkPercentage + '%');

                // 更新进度条
                $('#depth_progress').css('width', Math.min((maxDepth / 10) * 100, 100) + '%');
                $('#remark_progress').css('width', remarkPercentage + '%');
            }

            // 切换所有备注
            $('#toggle_all_remarks').click(function(){
                allRemarksVisible = !allRemarksVisible;
                const $remarks = $('.mind-remark');
                const $buttons = $('.toggle-remark-btn');

                if (allRemarksVisible) {
                    $remarks.slideDown(200);
                    $buttons.removeClass('btn-outline').addClass('btn-primary');
                    $(this).html('<i class="fas fa-compress-alt mr-2"></i><span class="hidden sm:inline">折叠所有备注</span>');
                } else {
                    $remarks.slideUp(200);
                    $buttons.removeClass('btn-primary').addClass('btn-outline');
                    $(this).html('<i class="fas fa-expand-alt mr-2"></i><span class="hidden sm:inline">展开所有备注</span>');
                }
            });

            // 切换专注模式
            $('#toggle_focus_mode').click(function(){
                isFocusMode = !isFocusMode;
                const $container = $('#list_container');
                const $actions = $('.card-header, #quick_actions, #stats_panel');

                if (isFocusMode) {
                    $container.css('max-height', '80vh');
                    $actions.fadeOut(200);
                    $(this).html('<i class="fas fa-compress mr-2"></i><span class="hidden sm:inline">退出专注</span>');
                    $(this).removeClass('btn-outline').addClass('btn-primary');
                } else {
                    $container.css('max-height', '600px');
                    $actions.fadeIn(200);
                    $(this).html('<i class="fas fa-expand mr-2"></i><span class="hidden sm:inline">专注模式</span>');
                    $(this).removeClass('btn-primary').addClass('btn-outline');
                }
            });

            // 全屏切换
            $('#fullscreen_toggle').click(function(){
                const $container = $('.container');
                if ($container.css('max-width') === '1980px') {
                    $container.css('max-width', '');
                    $(this).html('<i class="fas fa-expand-arrows-alt mr-1"></i>全屏');
                } else {
                    $container.css('max-width', '1980px');
                    $(this).html('<i class="fas fa-compress-arrows-alt mr-1"></i>退出全屏');
                }
            });

            // 视图切换
            $('#switch_to_list').click(function(){
                $('#mind_visualization').addClass('hidden');
                $('#list_container').removeClass('hidden');
            });

            // 分享功能
            $('#share_mind').click(function(){
                $('#share_modal').addClass('show');
            });

            // 复制链接
            $('#copy_link').click(function(){
                const linkInput = $(this).siblings('input')[0];
                linkInput.select();
                document.execCommand('copy');

                showNotification('success', '链接已复制到剪贴板');
            });

            // 导出功能
            $('#export_mind').click(function(){
                showNotification('info', '导出功能开发中');
            });

            // 实用函数
            function hideLoading() {
                $('#loading_indicator').fadeOut(200);
            }

            function showError(message) {
                showNotification('error', message);
            }

            function showNotification(type, message) {
                // 实现通知显示逻辑
                console.log(type, message);
            }

            function closeModal(modalId) {
                $('#' + modalId).removeClass('show');
            }

            function escapeHtml(text) {
                return $('<div/>').text(text).html();
            }

            function nl2br(str) {
                return str.replace(/\r?\n/g, '<br>');
            }

            // 初始化加载
            loadMindData();
        });
    </script>

    <style>
        /* 思维导图样式 */
        .mind-list-item {
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .mind-list-item:hover {
            transform: translateX(2px);
        }

        .mind-list-item[data-depth="0"] {
            margin-bottom: 16px;
        }

        .mind-list-item[data-depth="1"] {
            margin-left: 0;
        }

        .mind-list-item[data-depth="2"] {
            margin-left: 16px;
        }

        .mind-list-item[data-depth="3"] {
            margin-left: 32px;
        }

        /* 备注样式 */
        .mind-remark {
            border-left: 3px solid var(--primary-color);
            background: var(--gray-50);
            transition: all 0.2s ease;
        }

        .mind-remark:hover {
            background: var(--gray-100);
        }

        /* 节点深度指示器 */
        .mind-list-item[data-depth="0"] .mind-node-icon {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
        }

        /* 加载动画 */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* 上下文菜单样式 */
        .node-context-menu {
            position: absolute;
            z-index: 1000;
            min-width: 160px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            display: none;
        }

        .node-context-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-out;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .mind-list-item {
                padding: 12px;
            }

            .mind-list-item[data-depth] {
                margin-left: 0;
            }

            .pl-8 {
                padding-left: 16px;
            }
        }

        /* 打印样式 */
        @media print {
            .btn, #toggle_all_remarks, #toggle_focus_mode {
                display: none !important;
            }

            .mind-remark {
                display: block !important;
                break-inside: avoid;
            }
        }
    </style>
@endsection
