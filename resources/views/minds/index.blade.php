@extends('layouts.app')

@section('title', '思维导图 - 蒙太奇')
@section('description', '创建和管理思维导图，可视化您的想法和思考过程')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-project-diagram text-primary-color mr-3"></i>
                        思维导图
                    </h1>
                    <p class="text-gray-600 mt-2">可视化您的思考过程，连接想法，激发创造力</p>
                </div>

                <!-- 快速操作 -->
                <div class="flex items-center space-x-3">
                    <button onclick="showMindGuide()" class="btn btn-outline">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        使用指南
                    </button>
                    <a href="{{ url('mind/export') }}" class="btn btn-outline">
                        <i class="fas fa-download mr-2"></i>
                        导出数据
                    </a>
                </div>
            </div>
        </div>

        <!-- 创建新导图卡片 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                    创建新导图
                </h2>
                <p class="text-sm text-gray-600 mt-1">快速开始一个新的思考旅程</p>
            </div>

            <div class="p-6">
                <!-- 成功消息 -->
                @include('common.success')

                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 创建导图表单 -->
                <form action="{{ url('mind') }}" method="POST" id="newMindForm" class="space-y-4">
                    {!! csrf_field() !!}

                    <div class="space-y-4">
                        <!-- 导图名称 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                导图名称
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative">
                                <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="input w-full pl-10"
                                        placeholder="请输入思维导图名称，例如：产品规划、学习总结..."
                                        required
                                        autofocus
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-heading"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">给您的思考一个明确的主题</p>
                        </div>

                        <!-- 模板选择（扩展功能） -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-th-large text-blue-500 mr-2"></i>
                                快速模板（可选）
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="template" value="brainstorm" class="sr-only peer">
                                    <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50
                                          peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm
                                          transition-all duration-200">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <div class="w-8 h-8 rounded-md bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-brain text-blue-600"></i>
                                            </div>
                                            <span class="font-medium text-gray-900">头脑风暴</span>
                                        </div>
                                        <p class="text-xs text-gray-600">发散思维，收集想法</p>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="template" value="project" class="sr-only peer">
                                    <div class="p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50
                                          peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-sm
                                          transition-all duration-200">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <div class="w-8 h-8 rounded-md bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-tasks text-green-600"></i>
                                            </div>
                                            <span class="font-medium text-gray-900">项目规划</span>
                                        </div>
                                        <p class="text-xs text-gray-600">任务分解，进度管理</p>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="template" value="study" class="sr-only peer">
                                    <div class="p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50
                                          peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:shadow-sm
                                          transition-all duration-200">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <div class="w-8 h-8 rounded-md bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-graduation-cap text-purple-600"></i>
                                            </div>
                                            <span class="font-medium text-gray-900">学习笔记</span>
                                        </div>
                                        <p class="text-xs text-gray-600">知识梳理，概念连接</p>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="template" value="decision" class="sr-only peer">
                                    <div class="p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50
                                          peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:shadow-sm
                                          transition-all duration-200">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <div class="w-8 h-8 rounded-md bg-orange-100 flex items-center justify-center">
                                                <i class="fas fa-balance-scale text-orange-600"></i>
                                            </div>
                                            <span class="font-medium text-gray-900">决策分析</span>
                                        </div>
                                        <p class="text-xs text-gray-600">权衡利弊，做出选择</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            创建思维导图
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 导图列表区域 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list text-primary-color mr-2"></i>
                            导图列表
                            <span class="ml-2 badge badge-primary">{{ $minds->total() }} 个</span>
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">管理您的所有思维导图</p>
                    </div>

                    <!-- 筛选和排序 -->
                    <div class="flex items-center space-x-3">
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
                @if(count($minds) > 0)
                    <!-- 标签筛选（如果有标签） -->
                    @php
                        $activeTagId = request('tag_id');
                    @endphp

{{--                    @if($allTags->isNotEmpty())--}}
{{--                        <div class="mb-6 pb-4 border-b border-gray-200">--}}
{{--                            <div class="flex items-center mb-3">--}}
{{--                                <i class="fas fa-tags text-gray-400 mr-2 text-sm"></i>--}}
{{--                                <span class="text-sm font-medium text-gray-700">按标签筛选：</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-wrap gap-2">--}}
{{--                                <a href="{{ url('minds') }}"--}}
{{--                                   class="px-3 py-1.5 rounded-full text-sm border {{ !$activeTagId ? 'bg-primary-color text-white border-primary-color' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">--}}
{{--                                    全部--}}
{{--                                </a>--}}
{{--                                @foreach($allTags as $tag)--}}
{{--                                    <a href="{{ url('minds?tag_id=' . $tag->id) }}"--}}
{{--                                       class="px-3 py-1.5 rounded-full text-sm border {{ $activeTagId == $tag->id ? 'bg-primary-color text-white border-primary-color' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">--}}
{{--                                        <i class="fas fa-hashtag mr-1"></i>{{ $tag->name }}--}}
{{--                                    </a>--}}
{{--                                @endforeach--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endif--}}

                    <!-- 导图列表 -->
                    <div class="space-y-4" id="mindList">
                        @foreach($minds as $mind)
                            <div id="{{ $mind->id }}" class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all duration-200">
                                <!-- 左侧：导图信息 -->
                                <div class="flex-1 mb-4 sm:mb-0">
                                    <div class="flex items-start space-x-4">
                                        <!-- 导图图标 -->
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-sitemap text-blue-600 text-lg"></i>
                                        </div>

                                        <!-- 导图详情 -->
                                        <div class="flex-1">
                                            <!-- 标题和状态 -->
                                            <div class="flex items-center mb-2">
                                                <a href="{{ url('mind/' . $mind->id) }}"
                                                   class="text-lg font-semibold text-gray-900 hover:text-primary-color transition-colors duration-200">
                                                    {{ $mind->name }}
                                                </a>

                                                <!-- 大纲预览按钮 -->
                                                <a href="{{ url('mindoutlineviewv2/' . $mind->id) }}"
                                                   class="ml-3 text-sm text-gray-500 hover:text-blue-600 transition-colors duration-200"
                                                   title="大纲预览">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            </div>

                                            <!-- 标签区域 -->
                                            <div class="mb-2">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($mind->mindTagMaps as $map)
                                                        <a href="/minds?tag_id={{ $map->tag->id }}"
                                                           class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">
                                                            <i class="fas fa-hashtag mr-0.5 text-xs"></i>
                                                            {{ $map->tag->name }}
                                                        </a>
                                                    @endforeach

                                                    <!-- 添加标签按钮 -->
                                                    <button onclick="addTagToMind('{{ $mind->id }}')"
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200"
                                                            title="添加标签">
                                                        <i class="fas fa-plus mr-0.5 text-xs"></i>
                                                        标签
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- 元信息 -->
                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                        <span class="flex items-center">
                                            <i class="far fa-clock mr-1.5 text-xs"></i>
                                            更新于 {{ $mind->updated_at->format('m/d H:i') }}
                                        </span>
                                                <span class="flex items-center">
                                            <i class="far fa-calendar mr-1.5 text-xs"></i>
                                            创建于 {{ $mind->created_at->format('Y-m-d') }}
                                        </span>
                                                <span class="flex items-center">
                                            <i class="fas fa-layer-group mr-1.5 text-xs"></i>
                                            节点：{{ $mind->node_count ?? 0 }}
                                        </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 右侧：操作按钮 -->
                                <div class="flex items-center space-x-2">
                                    <!-- 编辑按钮 -->
                                    <a href="{{ url('mind/'.$mind->id) }}"
                                       class="btn btn-sm btn-outline flex items-center group-hover:opacity-100 sm:opacity-0 transition-opacity duration-200"
                                       title="编辑导图">
                                        <i class="fas fa-edit mr-1"></i>
                                        <span class="hidden sm:inline">编辑</span>
                                    </a>

                                    <!-- 删除按钮 -->
                                    <button onclick="confirmDeleteMind('{{ $mind->id }}', '{{ addslashes($mind->name) }}')"
                                            class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center group-hover:opacity-100 sm:opacity-0 transition-opacity duration-200"
                                            title="删除导图">
                                        <i class="fas fa-trash-alt mr-1"></i>
                                        <span class="hidden sm:inline">删除</span>
                                    </button>

                                    <!-- 更多操作（下拉菜单） -->
                                    <div class="relative dropdown">
                                        <button class="btn btn-sm btn-secondary flex items-center">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>

                                        <div class="dropdown-menu hidden" style="right: 0; left: auto; min-width: 160px;">
                                            <a href="{{ url('mind/clone/' . $mind->id) }}" class="dropdown-item">
                                                <i class="fas fa-clone text-blue-500"></i>
                                                复制导图
                                            </a>
                                            <a href="{{ url('mind/export/' . $mind->id) }}" class="dropdown-item">
                                                <i class="fas fa-file-export text-green-500"></i>
                                                导出为JSON
                                            </a>
                                            <a href="{{ url('mind/share/' . $mind->id) }}" class="dropdown-item">
                                                <i class="fas fa-share-alt text-purple-500"></i>
                                                分享导图
                                            </a>
                                            <div class="border-t border-gray-200 my-1"></div>
                                            <a href="{{ url('mind/archive/' . $mind->id) }}" class="dropdown-item text-gray-500">
                                                <i class="fas fa-archive"></i>
                                                归档
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    <x-pagination-container :paginator="$minds" />
                @else
                    <!-- 空状态 -->
                    <div class="text-center py-16">
                        <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-50 to-purple-50 rounded-full flex items-center justify-center mb-6">
                            <i class="fas fa-project-diagram text-blue-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-3">开始您的第一个思维导图</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            思维导图是可视化思考的绝佳工具，帮助您整理思路、激发创意、规划项目
                        </p>
                        <div class="space-y-4">
                            <button onclick="document.getElementById('name').focus()" class="btn btn-primary text-lg px-8 py-3">
                                <i class="fas fa-plus mr-3"></i>
                                创建第一个思维导图
                            </button>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                                提示：您也可以从模板快速开始
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


        <script>
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
                        $.ajax({
                            url: "{{ url('mindaddtag') }}/" + mindId,
                            type: 'POST',
                            data: {
                                tag_name: result.value,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.code === 9999) {
                                    // 更新标签列表
                                    const tagHtml = `
                                <a href="/minds?tag_id=${response.result.tag.id}"
                                   class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">
                                    <i class="fas fa-hashtag mr-0.5 text-xs"></i>
                                    ${result.value}
                                </a>
                            `;
                                    $(`#${mindId} .flex-wrap`).append(tagHtml);
                                    showToast('success', '标签添加成功');
                                } else {
                                    showToast('error', response.msg || '添加失败');
                                }
                            },
                            error: function() {
                                showToast('error', '网络错误，请稍后重试');
                            }
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
                $.ajax({
                    url: "{{ url('mind') }}/" + mindId,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.code === 9999) {
                            $(`#${mindId}`).fadeOut(300, function() {
                                $(this).remove();
                                showToast('success', '思维导图删除成功');

                                // 检查是否还有导图
                                if ($('#mindList .group').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            showToast('error', response.msg || '删除失败');
                        }
                    },
                    error: function() {
                        showToast('error', '网络错误，请稍后重试');
                    }
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
                        document.getElementById('name').focus();
                    }
                });
            }

            // 搜索功能
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchMinds');
                const sortSelect = document.getElementById('sortMinds');

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
                        const sortValue = this.value;
                        // 这里可以添加排序逻辑
                        console.log('排序:', sortValue);
                    });
                }

                // 表单提交验证
                const form = document.getElementById('newMindForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const nameInput = document.getElementById('name');
                        if (!nameInput.value.trim()) {
                            e.preventDefault();
                            nameInput.focus();
                            showToast('error', '请输入思维导图名称');
                        }
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
                const items = document.querySelectorAll('#mindList .group');
                let hasVisibleItems = false;

                items.forEach(item => {
                    const mindName = item.querySelector('a.text-lg').textContent.toLowerCase();
                    const tags = item.querySelectorAll('.text-blue-800');
                    let tagText = '';
                    tags.forEach(tag => tagText += tag.textContent.toLowerCase());

                    if (mindName.includes(searchTerm) || tagText.includes(searchTerm)) {
                        item.style.display = 'flex';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // 显示无结果提示（如果需要）
                // ...
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