@extends('layouts.app')

@section('title', '思维导图 - 蒙太奇')
@section('description', '创建和管理思维导图，可视化您的想法和思考过程')

@section('content')
    <div class="max-w-7xl mx-auto">

        <!-- 导图列表区域 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list text-primary-color mr-2"></i>
                            导图列表
                            <span class="ml-2 badge badge-primary" id="mind-count-badge">0 个</span>
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">管理您的所有思维导图</p>
                    </div>

                    <!-- 筛选和排序 -->
                    <div class="flex items-center space-x-3">
                        <button type="button" class="btn btn-primary" id="openCreateMindModalBtn">
                            <i class="fas fa-plus mr-2"></i>创建导图
                        </button>
                        <div class="relative">
                            <input
                                    type="text"
                                    id="searchMinds"
                                    class="input w-48 pl-9"
                                    placeholder="搜索导图..."
                            >
                            <div class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>

                        <select id="sortMinds" class="input w-32">
                            <option value="updated_at_desc">最近更新</option>
                            <option value="created_at_desc">最近创建</option>
                            <option value="name_asc">名称 A-Z</option>
                            <option value="name_desc">名称 Z-A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-4" id="mindList"></div>

                <div class="text-center py-16" id="mind-empty-state" style="display:none;">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-50 to-purple-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-project-diagram text-blue-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">开始您的第一个思维导图</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        思维导图是可视化思考的绝佳工具，帮助您整理思路、激发创意、规划项目
                    </p>
                    <div class="space-y-4">
                        <button onclick="openCreateMindModal()" class="btn btn-primary text-lg px-8 py-3">
                            <i class="fas fa-plus mr-3"></i>
                            创建第一个思维导图
                        </button>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                            提示：您也可以从模板快速开始
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-center gap-3" id="mind-pagination" style="display:none;">
                    <button class="btn btn-outline btn-sm" id="mind-prev-btn">
                        <i class="fas fa-chevron-left mr-1"></i>上一页
                    </button>
                    <span class="text-sm text-gray-600" id="mind-page-text">第 1 / 1 页</span>
                    <button class="btn btn-outline btn-sm" id="mind-next-btn">
                        下一页<i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="createMindModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-40" id="createMindModalMask"></div>
        <div class="relative h-full w-full flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-project-diagram text-blue-500 mr-2"></i>创建思维导图
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" id="closeCreateMindModalBtn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="createMindModalForm" class="px-6 py-5 space-y-4">
                    <div>
                        <label for="createMindNameInput" class="block text-sm font-medium text-gray-700 mb-2">
                            导图名称 <span class="text-red-500">*</span>
                        </label>
                        <input id="createMindNameInput" type="text" class="input w-full" placeholder="请输入思维导图名称" required>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" class="btn btn-secondary" id="cancelCreateMindModalBtn">取消</button>
                        <button type="submit" class="btn btn-primary" id="submitCreateMindModalBtn">
                            <i class="fas fa-plus mr-2"></i>创建并编辑
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;
            var mindsState = {
                items: [],
                pagination: { current_page: 1, last_page: 1, total: 0 },
                filteredItems: []
            };

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function formatDateTime(value) {
                if (!value) return '-';
                var d = new Date(String(value).replace(' ', 'T'));
                if (Number.isNaN(d.getTime())) return value;
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var dd = String(d.getDate()).padStart(2, '0');
                var hh = String(d.getHours()).padStart(2, '0');
                var mi = String(d.getMinutes()).padStart(2, '0');
                return mm + '/' + dd + ' ' + hh + ':' + mi;
            }

            function formatDateOnly(value) {
                if (!value) return '-';
                var d = new Date(String(value).replace(' ', 'T'));
                if (Number.isNaN(d.getTime())) return value;
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }

            function buildMindCardHtml(mind) {
                var tags = Array.isArray(mind.mind_tag_maps) ? mind.mind_tag_maps : [];
                var tagsHtml = tags.map(function(map) {
                    var tag = map && map.tag ? map.tag : {};
                    return '<a href="/minds?tag_id=' + Number(tag.id || 0) + '" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200"><i class="fas fa-hashtag mr-0.5 text-xs"></i>' + escapeHtml(tag.name || '') + '</a>';
                }).join('');
                tagsHtml += '<button onclick="addTagToMind(\'' + Number(mind.id) + '\')" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200" title="添加标签"><i class="fas fa-plus mr-0.5 text-xs"></i>标签</button>';

                return (
                    '<div id="' + Number(mind.id) + '" class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all duration-200">' +
                        '<div class="flex-1 mb-4 sm:mb-0">' +
                            '<div class="flex items-start space-x-4">' +
                                '<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center shadow-sm"><i class="fas fa-sitemap text-blue-600 text-lg"></i></div>' +
                                '<div class="flex-1">' +
                                    '<div class="flex items-center mb-2">' +
                                        '<a href="/mind/' + Number(mind.id) + '" class="text-lg font-semibold text-gray-900 hover:text-primary-color transition-colors duration-200">' + escapeHtml(mind.name || '') + '</a>' +
                                        '<a href="/mindoutlineviewv2/' + Number(mind.id) + '" class="ml-3 text-sm text-gray-500 hover:text-blue-600 transition-colors duration-200" title="大纲预览"><i class="fas fa-file-alt"></i></a>' +
                                    '</div>' +
                                    '<div class="mb-2"><div class="flex flex-wrap gap-1">' + tagsHtml + '</div></div>' +
                                    '<div class="flex items-center text-sm text-gray-500 space-x-4">' +
                                        '<span class="flex items-center"><i class="far fa-clock mr-1.5 text-xs"></i>更新于 ' + escapeHtml(formatDateTime(mind.updated_at)) + '</span>' +
                                        '<span class="flex items-center"><i class="far fa-calendar mr-1.5 text-xs"></i>创建于 ' + escapeHtml(formatDateOnly(mind.created_at)) + '</span>' +
                                        '<span class="flex items-center"><i class="fas fa-layer-group mr-1.5 text-xs"></i>节点：' + Number(mind.node_count || 0) + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="flex items-center space-x-2">' +
                            '<a href="/mind/' + Number(mind.id) + '" class="btn btn-sm btn-outline flex items-center group-hover:opacity-100 sm:opacity-0 transition-opacity duration-200" title="编辑导图"><i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">编辑</span></a>' +
                            '<button onclick="confirmDeleteMind(\'' + Number(mind.id) + '\', \'' + escapeHtml(mind.name || '') + '\')" class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center group-hover:opacity-100 sm:opacity-0 transition-opacity duration-200" title="删除导图"><i class="fas fa-trash-alt mr-1"></i><span class="hidden sm:inline">删除</span></button>' +
                            '<div class="relative dropdown">' +
                                '<button class="btn btn-sm btn-secondary flex items-center"><i class="fas fa-ellipsis-h"></i></button>' +
                                '<div class="dropdown-menu hidden" style="right: 0; left: auto; min-width: 160px;">' +
                                    '<a href="/mind/clone/' + Number(mind.id) + '" class="dropdown-item"><i class="fas fa-clone text-blue-500"></i>复制导图</a>' +
                                    '<a href="/mind/export/' + Number(mind.id) + '" class="dropdown-item"><i class="fas fa-file-export text-green-500"></i>导出为JSON</a>' +
                                    '<a href="/mind/share/' + Number(mind.id) + '" class="dropdown-item"><i class="fas fa-share-alt text-purple-500"></i>分享导图</a>' +
                                    '<div class="border-t border-gray-200 my-1"></div>' +
                                    '<a href="/mind/archive/' + Number(mind.id) + '" class="dropdown-item text-gray-500"><i class="fas fa-archive"></i>归档</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            }

            function updateMindListUI(itemsOverride) {
                var list = document.getElementById('mindList');
                var empty = document.getElementById('mind-empty-state');
                var badge = document.getElementById('mind-count-badge');
                var pageWrap = document.getElementById('mind-pagination');
                var pageText = document.getElementById('mind-page-text');
                var prevBtn = document.getElementById('mind-prev-btn');
                var nextBtn = document.getElementById('mind-next-btn');

                var items = Array.isArray(itemsOverride) ? itemsOverride : mindsState.items;
                if (badge) badge.textContent = String(Number((mindsState.pagination || {}).total || items.length)) + ' 个';

                if (!items.length) {
                    if (list) list.innerHTML = '';
                    if (empty) empty.style.display = '';
                } else {
                    if (empty) empty.style.display = 'none';
                    if (list) list.innerHTML = items.map(buildMindCardHtml).join('');
                }

                var cp = Number((mindsState.pagination || {}).current_page || 1);
                var lp = Number((mindsState.pagination || {}).last_page || 1);
                if (pageText) pageText.textContent = '第 ' + cp + ' / ' + lp + ' 页';
                if (prevBtn) prevBtn.disabled = cp <= 1;
                if (nextBtn) nextBtn.disabled = cp >= lp;
                if (pageWrap) pageWrap.style.display = lp > 1 ? '' : 'none';
            }

            function loadMinds(page) {
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return Promise.resolve();
                }
                var query = new URLSearchParams(window.location.search || '');
                var tagId = query.get('tag_id') || '';
                var name = query.get('name') || '';
                var currentPage = page || parseInt(query.get('page') || '1', 10) || 1;
                return apiRequest('GET', '/minds', {
                    tag_id: tagId,
                    name: name,
                    page: currentPage
                }).then(function(response) {
                    var resultData = response ? (response.result || response.data || null) : null;
                    if (!response || response.code !== 9999 || !resultData) {
                        throw new Error((response && response.msg) ? response.msg : '加载失败');
                    }
                    var payload = resultData.minds || {};
                    mindsState.items = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : []);
                    mindsState.filteredItems = mindsState.items.slice();
                    mindsState.pagination = {
                        current_page: Number(payload.current_page || 1),
                        last_page: Number(payload.last_page || 1),
                        total: Number(payload.total || mindsState.items.length)
                    };
                    var searchInput = document.getElementById('searchMinds');
                    searchMinds((searchInput && searchInput.value ? searchInput.value : '').toLowerCase());
                }).catch(function(error) {
                    showToast('error', '导图列表加载失败: ' + (error && error.message ? error.message : '网络错误'));
                });
            }

            function openCreateMindModal() {
                var modal = document.getElementById('createMindModal');
                var input = document.getElementById('createMindNameInput');
                if (!modal) return;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (input) {
                    input.value = '';
                    setTimeout(function() { input.focus(); }, 50);
                }
            }

            function closeCreateMindModal() {
                var modal = document.getElementById('createMindModal');
                if (!modal) return;
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            function createMindAndRedirect(name, submitBtn) {
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return Promise.resolve();
                }
                var original = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';
                }
                return apiRequest('POST', '/minds', { name: name }).then(function(response) {
                    if (response && response.code === 9999) {
                        var createdId = response.result && response.result.id ? response.result.id : null;
                        if (createdId) {
                            window.location.href = "{{ url('mind') }}/" + createdId;
                            return;
                        }
                        window.location.reload();
                        return;
                    }
                    showToast('error', (response && response.msg) ? response.msg : '创建失败');
                }).catch(function() {
                    showToast('error', '网络错误，请稍后重试');
                }).finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = original;
                    }
                });
            }

            // 添加标签到思维导图
            function addTagToMind(mindId) {
                Swal.fire({
                    title: '添加标签',
                    input: 'text',
                    inputLabel: '请输入标签名称',
                    inputPlaceholder: '例如：重要、创意、工作...',
                    showCancelButton: true,
                    confirmButtonText: '添加',
                    cancelButtonText: '取消',
                    inputValidator: (value) => {
                        if (!value) {
                            return '请输入标签名称';
                        }
                        if (value.length > 20) {
                            return '标签名称不能超过20个字符';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (!apiRequest) {
                            showToast('error', 'API客户端未初始化');
                            return;
                        }
                        apiRequest('POST', '/minds/' + mindId + '/tags', {
                            tag_name: result.value
                        }).then(function(response) {
                            if (response.code === 9999) {
                                loadMinds(Number((mindsState.pagination || {}).current_page || 1));
                                showToast('success', '标签添加成功');
                            } else {
                                showToast('error', response.msg || '添加失败');
                            }
                        }).catch(function() {
                            showToast('error', '网络错误，请稍后重试');
                        });
                    }
                });
            }

            // 删除思维导图确认
            function confirmDeleteMind(mindId, mindName) {
                Swal.fire({
                    title: '确认删除',
                    html: `确定要删除思维导图 <strong>"${mindName}"</strong> 吗？<br><br>
                  <div class="text-left text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      此操作不可恢复，导图内的所有节点和内容都将被删除。
                  </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '确认删除',
                    cancelButtonText: '取消',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteMind(mindId);
                    }
                });
            }

            // 删除思维导图
            function deleteMind(mindId) {
                if (!apiRequest) {
                    showToast('error', 'API客户端未初始化');
                    return;
                }
                apiRequest('DELETE', '/minds/' + mindId, {}).then(function(response) {
                    if (response.code === 9999) {
                        showToast('success', '思维导图删除成功');
                        loadMinds(Number((mindsState.pagination || {}).current_page || 1));
                    } else {
                        showToast('error', response.msg || '删除失败');
                    }
                }).catch(function() {
                    showToast('error', '网络错误，请稍后重试');
                });
            }

            // 思维导图使用指南
            function showMindGuide() {
                Swal.fire({
                    title: '思维导图使用指南',
                    html: `
                <div class="text-left space-y-4">
                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">什么是思维导图？</h4>
                            <p class="text-sm text-gray-600 mt-1">思维导图是一种可视化思考工具，帮助您组织和连接想法，激发创造力。</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">主要功能</h4>
                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                <li>• 创建多层级思维结构</li>
                                <li>• 添加标签进行分类管理</li>
                                <li>• 大纲预览和导出功能</li>
                                <li>• 从模板快速开始</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-rocket text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">使用场景</h4>
                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                <li>• 头脑风暴和创意收集</li>
                                <li>• 项目规划和管理</li>
                                <li>• 学习笔记和知识整理</li>
                                <li>• 会议记录和决策分析</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `,
                    icon: 'info',
                    confirmButtonText: '开始创建',
                    showCancelButton: true,
                    cancelButtonText: '关闭'
                }).then((result) => {
                    if (result.isConfirmed) {
                        openCreateMindModal();
                    }
                });
            }

            // 搜索功能
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchMinds');
                const sortSelect = document.getElementById('sortMinds');
                const prevBtn = document.getElementById('mind-prev-btn');
                const nextBtn = document.getElementById('mind-next-btn');
                const openCreateModalBtn = document.getElementById('openCreateMindModalBtn');
                const closeCreateModalBtn = document.getElementById('closeCreateMindModalBtn');
                const cancelCreateModalBtn = document.getElementById('cancelCreateMindModalBtn');
                const createModalMask = document.getElementById('createMindModalMask');
                const createModalForm = document.getElementById('createMindModalForm');
                const createNameInput = document.getElementById('createMindNameInput');
                const submitCreateModalBtn = document.getElementById('submitCreateMindModalBtn');

                loadMinds();

                if (openCreateModalBtn) {
                    openCreateModalBtn.addEventListener('click', openCreateMindModal);
                }
                if (closeCreateModalBtn) {
                    closeCreateModalBtn.addEventListener('click', closeCreateMindModal);
                }
                if (cancelCreateModalBtn) {
                    cancelCreateModalBtn.addEventListener('click', closeCreateMindModal);
                }
                if (createModalMask) {
                    createModalMask.addEventListener('click', closeCreateMindModal);
                }
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeCreateMindModal();
                    }
                });
                if (createModalForm) {
                    createModalForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        var name = createNameInput && createNameInput.value ? createNameInput.value.trim() : '';
                        if (!name) {
                            showToast('error', '请输入思维导图名称');
                            if (createNameInput) createNameInput.focus();
                            return;
                        }
                        createMindAndRedirect(name, submitCreateModalBtn);
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function() {
                        var cp = Number((mindsState.pagination || {}).current_page || 1);
                        if (cp > 1) {
                            loadMinds(cp - 1);
                        }
                    });
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', function() {
                        var cp = Number((mindsState.pagination || {}).current_page || 1);
                        var lp = Number((mindsState.pagination || {}).last_page || 1);
                        if (cp < lp) {
                            loadMinds(cp + 1);
                        }
                    });
                }

                // 搜索功能
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(function() {
                        const searchTerm = this.value.toLowerCase();
                        searchMinds(searchTerm);
                    }, 300));
                }

                // 排序功能
                if (sortSelect) {
                    sortSelect.addEventListener('change', function() {
                        searchMinds((searchInput && searchInput.value ? searchInput.value : '').toLowerCase());
                    });
                }

            });

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

            // 搜索思维导图
            function searchMinds(searchTerm) {
                const sortSelect = document.getElementById('sortMinds');
                const sortValue = sortSelect ? sortSelect.value : 'updated_at_desc';
                const src = Array.isArray(mindsState.items) ? mindsState.items.slice() : [];
                const keyword = (searchTerm || '').trim();
                const filtered = src.filter(function(mind) {
                    const name = String(mind.name || '').toLowerCase();
                    const tags = Array.isArray(mind.mind_tag_maps) ? mind.mind_tag_maps : [];
                    const tagText = tags.map(function(map) {
                        return map && map.tag && map.tag.name ? String(map.tag.name).toLowerCase() : '';
                    }).join(' ');
                    return !keyword || name.indexOf(keyword) >= 0 || tagText.indexOf(keyword) >= 0;
                });

                filtered.sort(function(a, b) {
                    if (sortValue === 'name_asc') {
                        return String(a.name || '').localeCompare(String(b.name || ''));
                    }
                    if (sortValue === 'name_desc') {
                        return String(b.name || '').localeCompare(String(a.name || ''));
                    }
                    if (sortValue === 'created_at_desc') {
                        return new Date(String(b.created_at || '').replace(' ', 'T')).getTime() - new Date(String(a.created_at || '').replace(' ', 'T')).getTime();
                    }
                    return new Date(String(b.updated_at || '').replace(' ', 'T')).getTime() - new Date(String(a.updated_at || '').replace(' ', 'T')).getTime();
                });

                mindsState.filteredItems = filtered;
                updateMindListUI(filtered);
            }

            // 显示提示消息
            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
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
        </script>

        <style>
            /* 思维导图卡片特效 */
            .group:hover {
                transform: translateY(-2px);
                transition: all 0.2s ease;
            }

            /* 标签动画 */
            a[href*="tag_id"] {
                transition: all 0.2s ease;
            }

            a[href*="tag_id"]:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            /* 按钮悬停效果 */
            .btn-outline:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
            }

            /* 模板选择动画 */
            input[name="template"]:checked + div {
                animation: templateSelect 0.3s ease-out;
            }

            @keyframes templateSelect {
                0% { transform: scale(1); }
                50% { transform: scale(1.02); }
                100% { transform: scale(1); }
            }

            /* 渐变背景动画 */
            .bg-gradient-to-br {
                background-size: 200% 200%;
                animation: gradientShift 15s ease infinite;
            }

            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
        </style>
    @endsection
