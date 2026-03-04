@extends('layouts.app')

@section('title', '订阅管理 - 蒙太奇')
@section('description', '管理和订阅您喜欢的RSS源，获取最新内容')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-rss text-primary-color mr-3"></i>
                        订阅管理
                    </h1>
                    <p class="text-gray-600 mt-2">订阅和管理您喜欢的RSS源，获取最新内容</p>
                </div>

                <!-- 快速操作按钮 -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-outline">
                        <i class="fas fa-compass mr-2"></i>
                        探索发现
                    </a>
                    <a href="{{ url('articles') }}" class="btn btn-primary">
                        <i class="fas fa-newspaper mr-2"></i>
                        查看文章
                    </a>
                </div>
            </div>
        </div>

        <!-- 创建新订阅卡片 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                        添加新订阅
                    </h2>

                    <!-- 快捷入口 -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ url('feeds/weiborss') }}" class="btn btn-sm btn-outline">
                            <i class="fab fa-weibo text-red-500 mr-1"></i>
                            微博订阅
                        </a>
                        <a href="{{ url('feeds/weixinrss') }}" class="btn btn-sm btn-outline">
                            <i class="fab fa-weixin text-green-500 mr-1"></i>
                            公众号订阅
                        </a>
                        <a href="{{ url('feeds/opml') }}" class="btn btn-sm btn-outline">
                            <i class="fas fa-file-import text-blue-500 mr-1"></i>
                            OPML导入
                        </a>
                        <a href="{{ url('categorys') }}" class="btn btn-sm btn-outline">
                            <i class="fas fa-folder-tree text-purple-500 mr-1"></i>
                            分类设置
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- 成功消息 -->
                @include('common.success')

                <!-- 错误消息 -->
                @include('common.errors')

                <!-- 添加订阅表单 -->
                <form action="{{ url('/api/v2/feeds') }}" method="POST" id="addFeedForm" class="space-y-6">
                    {!! csrf_field() !!}

                    <div class="space-y-6">
                        <!-- 订阅地址 -->
                        <div class="space-y-3">
                            <label for="url" class="block text-sm font-medium text-gray-700 flex items-center">
                                <i class="fas fa-link text-gray-400 mr-2 text-sm"></i>
                                订阅地址 (RSS/Atom URL)
                                <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="flex space-x-3">
                                <div class="flex-1 relative">
                                    <input
                                            type="url"
                                            name="url"
                                            id="url"
                                            value="{{ $url }}"
                                            class="input w-full pl-10"
                                            placeholder="请输入RSS或Atom订阅地址，例如：https://example.com/feed"
                                            required
                                            pattern="https?://.+"
                                    >
                                    <div class="absolute left-3 top-3 text-gray-400">
                                        <i class="fas fa-rss"></i>
                                    </div>
                                </div>
                                <button type="button" id="check_url" class="btn btn-outline whitespace-nowrap">
                                    <i class="fas fa-search mr-2"></i>
                                    检测地址
                                </button>
                            </div>
                            <div class="flex items-center text-sm text-gray-500" id="processTips">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                请输入有效的RSS或Atom订阅地址
                            </div>
                        </div>

                        <!-- 订阅信息 -->
                        <div id="feedInfo" class="hidden">
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-newspaper text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">检测到的订阅信息</h4>
                                            <p class="text-sm text-gray-600">系统已识别到订阅源信息</p>
                                        </div>
                                    </div>
                                    <span class="badge badge-success flex items-center">
                                    <i class="fas fa-check-circle mr-1 text-xs"></i>
                                    检测成功
                                </span>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label for="feed_name" class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-font text-gray-400 mr-2 text-sm"></i>
                                            订阅名称
                                            <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                    type="text"
                                                    name="feed_name"
                                                    id="feed_name"
                                                    value="{{ $title }}"
                                                    class="input w-full pl-10"
                                                    placeholder="订阅名称"
                                                    required
                                            >
                                            <div class="absolute left-3 top-3 text-gray-400">
                                                <i class="fas fa-heading"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 分类选择 -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-folder text-gray-400 mr-2 text-sm"></i>
                                            所属分类
                                            <span class="text-red-500 ml-1">*</span>
                                        </label>

                                        @if(count($categorys) == 0)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                                <div class="flex items-center">
                                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                                                    <div>
                                                        <p class="text-yellow-800 font-medium">需要先创建分类</p>
                                                        <p class="text-yellow-700 text-sm mt-1">
                                                            所有订阅必须属于一个分类，请先创建分类后再添加订阅。
                                                        </p>
                                                        <a href="{{ url('categorys') }}" target="_blank" class="btn btn-sm btn-outline mt-3">
                                                            <i class="fas fa-plus mr-1"></i>
                                                            前往创建分类
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                                @foreach ($categorys as $category)
                                                    <label class="relative cursor-pointer">
                                                        <input
                                                                type="radio"
                                                                name="category_id"
                                                                value="{{ $category->id }}"
                                                                class="sr-only peer"
                                                                {{ $loop->first ? 'checked' : '' }}
                                                        >
                                                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50
                                                              peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm
                                                              transition-all duration-200">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="w-8 h-8 rounded-md bg-blue-100 flex items-center justify-center">
                                                                    <i class="fas fa-folder text-blue-600"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                                                    <div class="text-xs text-gray-500">
                                                                        订阅数：{{ $category->feeds_count ?? 0 }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>

                                            <!-- 新建分类快捷入口 -->
                                            <div class="mt-3 text-center">
                                                <a href="{{ url('categorys') }}" target="_blank" class="text-sm text-primary-color hover:underline inline-flex items-center">
                                                    <i class="fas fa-plus-circle mr-1"></i>
                                                    创建新分类
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 其他设置（可选） -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700 flex items-center">
                                            <i class="fas fa-cog text-gray-400 mr-2 text-sm"></i>
                                            高级设置（可选）
                                        </label>
                                        <div class="space-y-3">
                                            <div class="flex items-center space-x-3">
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="auto_refresh" value="1" checked class="rounded text-blue-600">
                                                    <span class="ml-2 text-sm text-gray-700">自动更新</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="notify" value="1" class="rounded text-blue-600">
                                                    <span class="ml-2 text-sm text-gray-700">新文章通知</span>
                                                </label>
                                            </div>
                                            <div>
                                                <label class="text-sm text-gray-600">更新频率</label>
                                                <select name="refresh_interval" class="input w-48 mt-1">
                                                    <option value="30">每30分钟</option>
                                                    <option value="60" selected>每小时</option>
                                                    <option value="180">每3小时</option>
                                                    <option value="360">每6小时</option>
                                                    <option value="720">每12小时</option>
                                                    <option value="1440">每天</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">
                            <i class="fas fa-redo mr-2"></i>
                            重置
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-plus mr-2"></i>
                            添加订阅
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 订阅列表卡片 -->
        @if(count($feedSubs) > 0)
            <div class="card">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-list text-primary-color mr-2"></i>
                                我的订阅
                                <span class="ml-2 badge badge-primary">{{ $feedSubs->total() }} 个</span>
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">管理您的所有订阅源</p>
                        </div>

                        <!-- 统计信息 -->
                        <div class="text-sm text-gray-600">
                            活跃订阅：{{ $feedSubs->where('status', 'active')->count() }} 个
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 搜索和筛选 -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                            <div class="relative flex-1">
                                <input
                                        type="text"
                                        id="searchFeeds"
                                        class="input w-full pl-10"
                                        placeholder="搜索订阅名称或网址..."
                                >
                                <div class="absolute left-3 top-3 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <select id="filterCategory" class="input w-40">
                                    <option value="">所有分类</option>
                                    @foreach($categorys as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <select id="filterStatus" class="input w-32">
                                    <option value="">所有状态</option>
                                    <option value="active">活跃</option>
                                    <option value="inactive">未激活</option>
                                    <option value="error">错误</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 订阅列表 -->
                    <div class="space-y-4" id="feedList">
                        @foreach($feedSubs as $feedSub)
                            @if(!empty($feedSub->feed))
                                <div id="{{ $feedSub->id }}" class="group flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all duration-200">
                                    <!-- 订阅信息 -->
                                    <div class="flex items-start space-x-4">
                                        <!-- 订阅图标 -->
                                        <div class="relative">
                                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 border border-gray-200 flex items-center justify-center">
                                                @if($feedSub->feed->icon)
                                                    <img src="{{ $feedSub->feed->icon }}" alt="" class="w-8 h-8 rounded">
                                                @else
                                                    <i class="fas fa-rss text-blue-500 text-xl"></i>
                                                @endif
                                            </div>

                                            <!-- 状态指示器 -->
                                            @if($feedSub->status === 'active')
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border border-white"></div>
                                            @elseif($feedSub->status === 'error')
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border border-white"></div>
                                            @endif
                                        </div>

                                        <!-- 订阅详情 -->
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <a href="{{ $feedSub->feed->url }}"
                                                       target="_blank"
                                                       class="text-lg font-semibold text-gray-900 hover:text-primary-color transition-colors duration-200">
                                                        {{ $feedSub->feed->feed_name }}
                                                    </a>

                                                    @if($feedSub->category)
                                                        <a href="?category_id={{ $feedSub->category->id }}"
                                                           class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                                                            <i class="fas fa-folder mr-1 text-xs"></i>
                                                            {{ $feedSub->category->name }}
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    文章：{{ $feedSub->feed->articles_count ?? 0 }}
                                                </div>
                                            </div>

                                            <!-- 描述和网址 -->
                                            <p class="text-sm text-gray-600 mb-2 line-clamp-2" title="{{ $feedSub->feed->feed_desc }}">
                                                {{ $feedSub->feed->feed_desc ?: '暂无描述' }}
                                            </p>

                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                    <span class="truncate max-w-xs" title="{{ $feedSub->feed->url }}">
                                        <i class="fas fa-link mr-1 text-xs"></i>
                                        {{ App\Http\Utils\CommonUtil::strLimit($feedSub->feed->url, 50) }}
                                    </span>
                                                <span>
                                        <i class="far fa-clock mr-1 text-xs"></i>
                                        更新于 {{ $feedSub->updated_at->diffForHumans() }}
                                    </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 操作按钮 -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        <!-- 查看文章 -->
                                        <a href="{{ url('articles?feed_id=' . $feedSub->feed->id) }}"
                                           class="btn btn-sm btn-outline flex items-center"
                                           title="查看文章">
                                            <i class="fas fa-eye mr-1"></i>
                                            <span class="hidden sm:inline">文章</span>
                                        </a>

                                        <!-- 编辑按钮 -->
                                        <a href="{{ url('feed/'.$feedSub->id) }}"
                                           class="btn btn-sm btn-outline flex items-center"
                                           title="编辑订阅">
                                            <i class="fas fa-edit mr-1"></i>
                                            <span class="hidden sm:inline">编辑</span>
                                        </a>

                                        <!-- 删除按钮 -->
                                        <button onclick="confirmDeleteFeed('{{ $feedSub->id }}', '{{ addslashes($feedSub->feed->feed_name) }}')"
                                                class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center"
                                                title="删除订阅">
                                            <i class="fas fa-trash-alt mr-1"></i>
                                            <span class="hidden sm:inline">删除</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- 分页 -->
                    @if($feedSubs->hasPages())
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    显示第 {{ $feedSubs->firstItem() }} 到 {{ $feedSubs->lastItem() }} 条，共 {{ $feedSubs->total() }} 个订阅
                                </div>
                                <div class="flex space-x-2">
                                    {!! $feedSubs->links('vendor.pagination.tailwind') !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- 空状态 -->
            <div class="card">
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-50 to-purple-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-rss text-blue-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">开始您的第一个订阅</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        订阅您喜欢的博客、新闻和资讯源，轻松获取最新内容，不错过任何重要更新
                    </p>
                    <div class="space-y-4">
                        <button onclick="document.getElementById('url').focus()" class="btn btn-primary text-lg px-8 py-3">
                            <i class="fas fa-plus mr-3"></i>
                            添加第一个订阅
                        </button>
                        <div class="space-x-4">
                            <a href="{{ url('feeds/explorer') }}" class="text-sm text-primary-color hover:underline">
                                <i class="fas fa-compass mr-1"></i>探索推荐订阅
                            </a>
                            <a href="{{ url('feeds/opml') }}" class="text-sm text-primary-color hover:underline">
                                <i class="fas fa-file-import mr-1"></i>导入OPML文件
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };

            // 检测订阅地址
            document.getElementById('check_url').addEventListener('click', function() {
                const url = document.getElementById('url').value.trim();
                const processTips = document.getElementById('processTips');
                const submitBtn = document.getElementById('submitBtn');

                if (!url) {
                    showToast('error', '请输入订阅地址');
                    return;
                }

                // 验证URL格式
                if (!isValidUrl(url)) {
                    showToast('error', '请输入有效的URL地址');
                    return;
                }

                // 显示处理中状态
                this.disabled = true;
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>检测中...';
                processTips.innerHTML = '<i class="fas fa-spinner fa-spin mr-2 text-blue-500"></i>正在检测订阅地址...';

                // 发送检测请求
                apiRequest('GET', '/feeds/check-feed-url', {url: url}).then(function(response) {
                    if (response.code === 9999) {
                        // 显示订阅信息区域
                        document.getElementById('feedInfo').classList.remove('hidden');

                        // 填充订阅名称
                        document.getElementById('feed_name').value = response.result.title;

                        // 显示成功消息
                        processTips.innerHTML = `
                    <div class="flex items-center text-green-600">
                        <i class="fas fa-check-circle mr-2"></i>
                        检测成功！发现订阅：<strong class="ml-1">${response.result.title}</strong>
                    </div>
                `;

                        // 启用提交按钮
                        submitBtn.disabled = false;

                        // 聚焦到订阅名称
                        document.getElementById('feed_name').focus();

                        showToast('success', '订阅地址检测成功');
                    } else {
                        processTips.innerHTML = `
                    <div class="flex items-center text-red-600">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        未检测到有效订阅内容，请确认URL是否正确
                    </div>
                `;
                        showToast('error', '未检测到订阅内容，请确认地址');
                    }
                }).catch(function() {
                    processTips.innerHTML = `
                <div class="flex items-center text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    网络错误，请稍后重试
                </div>
            `;
                    showToast('error', '网络错误，请检查连接');
                }).then(function() {
                    // 恢复按钮状态
                    const checkBtn = document.getElementById('check_url');
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<i class="fas fa-search mr-2"></i>检测地址';
                });
            });

            // URL验证函数
            function isValidUrl(string) {
                try {
                    new URL(string);
                    return true;
                } catch (_) {
                    return false;
                }
            }

            // 删除订阅确认
            function confirmDeleteFeed(feedId, feedName) {
                Swal.fire({
                    title: '确认删除',
                    html: `确定要删除订阅 <strong>"${feedName}"</strong> 吗？<br><br>
                  <div class="text-left text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      此操作将同时删除该订阅的所有文章记录。
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
                        deleteFeed(feedId);
                    }
                });
            }

            // 删除订阅
            function deleteFeed(feedId) {
                apiRequest('DELETE', "/feeds/" + feedId, {}).then(function(response) {
                        if (response.code === 9999) {
                            $(`#${feedId}`).fadeOut(300, function() {
                                $(this).remove();
                                showToast('success', '订阅删除成功');

                                // 检查是否还有订阅
                                if ($('#feedList .group').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            showToast('error', response.msg || '删除失败');
                        }
                }).catch(function() {
                    showToast('error', '网络错误，请稍后重试');
                });
            }

            // 重置表单
            function resetForm() {
                document.getElementById('url').value = '';
                document.getElementById('feed_name').value = '';
                document.getElementById('feedInfo').classList.add('hidden');
                document.getElementById('processTips').innerHTML = `
            <i class="fas fa-info-circle mr-2 text-blue-500"></i>
            请输入有效的RSS或Atom订阅地址
        `;
                showToast('info', '表单已重置');
            }

            // 搜索功能
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchFeeds');
                const filterCategory = document.getElementById('filterCategory');
                const filterStatus = document.getElementById('filterStatus');

                // 搜索功能
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(function() {
                        filterFeeds();
                    }, 300));
                }

                // 筛选功能
                if (filterCategory) {
                    filterCategory.addEventListener('change', filterFeeds);
                }

                if (filterStatus) {
                    filterStatus.addEventListener('change', filterFeeds);
                }

                // 表单提交验证
                const form = document.getElementById('addFeedForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const urlInput = document.getElementById('url');
                        const feedNameInput = document.getElementById('feed_name');
                        const categoryInput = document.querySelector('input[name="category_id"]:checked');

                        if (!urlInput.value.trim()) {
                            urlInput.focus();
                            showToast('error', '请输入订阅地址');
                            return;
                        }

                        if (!feedNameInput.value.trim() && document.getElementById('feedInfo').classList.contains('hidden')) {
                            showToast('error', '请先检测订阅地址获取订阅名称');
                            return;
                        }

                        if (!categoryInput) {
                            showToast('error', '请选择分类');
                            return;
                        }

                        if (!apiRequest) {
                            showToast('error', 'API客户端未初始化');
                            return;
                        }

                        const submitBtn = document.getElementById('submitBtn');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>添加中...';

                        apiRequest('POST', '/feeds', {
                            url: urlInput.value.trim(),
                            feed_name: feedNameInput.value.trim(),
                            category_id: categoryInput.value
                        }).then(function(resp) {
                            if (resp && resp.code === 9999) {
                                showToast('success', '订阅添加成功');
                                setTimeout(function() {
                                    window.location.reload();
                                }, 300);
                                return;
                            }
                            showToast('error', (resp && resp.msg) ? resp.msg : '添加失败');
                        }).catch(function() {
                            showToast('error', '添加失败，请稍后重试');
                        }).finally(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                    });
                }
            });

            // 筛选订阅
            function filterFeeds() {
                const searchTerm = document.getElementById('searchFeeds').value.toLowerCase();
                const categoryFilter = document.getElementById('filterCategory').value;
                const statusFilter = document.getElementById('filterStatus').value;

                const items = document.querySelectorAll('#feedList .group');
                let hasVisibleItems = false;

                items.forEach(item => {
                    const feedName = item.querySelector('a.text-lg').textContent.toLowerCase();
                    const feedDesc = item.querySelector('p.text-gray-600').textContent.toLowerCase();
                    const feedUrl = item.querySelector('span.truncate').textContent.toLowerCase();
                    const categoryId = item.querySelector('a[href*="category_id"]')?.getAttribute('href')?.match(/category_id=(\d+)/)?.[1] || '';
                    const status = item.querySelector('.absolute.-top-1')?.classList.contains('bg-green-500') ? 'active' :
                        item.querySelector('.absolute.-top-1')?.classList.contains('bg-red-500') ? 'error' : 'inactive';

                    const matchesSearch = feedName.includes(searchTerm) || feedDesc.includes(searchTerm) || feedUrl.includes(searchTerm);
                    const matchesCategory = !categoryFilter || categoryId === categoryFilter;
                    const matchesStatus = !statusFilter || status === statusFilter;

                    if (matchesSearch && matchesCategory && matchesStatus) {
                        item.style.display = 'flex';
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // 可以在这里添加无结果提示
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

            // 显示提示消息
            function showToast(type, message) {
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
        </script>

        <style>
            /* 订阅卡片特效 */
            .group:hover {
                transform: translateY(-2px);
                transition: all 0.2s ease;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            /* 分类选择器样式 */
            input[name="category_id"]:checked + div {
                border-color: var(--primary-color);
                background-color: rgba(59, 130, 246, 0.05);
            }

            /* 状态指示器动画 */
            .absolute.-top-1 {
                animation: statusPulse 2s ease-in-out infinite;
            }

            @keyframes statusPulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            /* 订阅图标动画 */
            .fa-rss {
                animation: rssPulse 1.5s ease-in-out infinite;
            }

            @keyframes rssPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }

            /* 加载动画 */
            .fa-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* 文本截断 */
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .truncate {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        </style>
    @endsection
