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
                    <a href="{{ url('feeds/webpage-rss') }}" class="btn btn-primary">
                        <i class="fas fa-wand-magic-sparkles mr-2"></i>
                        网页转RSS
                    </a>
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-outline">
                        <i class="fas fa-compass mr-2"></i>
                        探索发现
                    </a>
                    <button type="button" id="refreshAllFeedsBtn" class="btn btn-outline">
                        <i class="fas fa-sync-alt mr-2"></i>
                        立即更新订阅
                    </button>
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
                        <a href="{{ url('feeds/webpage-rss') }}" class="btn btn-sm btn-outline">
                            <i class="fas fa-wand-magic-sparkles text-amber-500 mr-1"></i>
                            网页转RSS
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
                <!-- 添加订阅表单 -->
                <form action="{{ url('feed') }}" method="POST" id="addFeedForm" class="space-y-6">
                    {{ csrf_field() }}
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
                                            value=""
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
                                                    value=""
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

                                        <div id="categoryRadioList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3"></div>
                                        <div id="categoryEmptyTips" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                                                <div>
                                                    <p class="text-yellow-800 font-medium">需要先创建分类</p>
                                                    <p class="text-yellow-700 text-sm mt-1">所有订阅必须属于一个分类，请先创建分类后再添加订阅。</p>
                                                    <a href="{{ url('categorys') }}" target="_blank" class="btn btn-sm btn-outline mt-3">
                                                        <i class="fas fa-plus mr-1"></i>前往创建分类
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 text-center">
                                            <a href="{{ url('categorys') }}" target="_blank" class="text-sm text-primary-color hover:underline inline-flex items-center">
                                                <i class="fas fa-plus-circle mr-1"></i>
                                                创建新分类
                                            </a>
                                        </div>
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
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list text-primary-color mr-2"></i>
                            我的订阅
                            <span id="feedTotalBadge" class="ml-2 badge badge-primary">0 个</span>
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">管理您的所有订阅源</p>
                    </div>
                    <div id="feedActiveCountText" class="text-sm text-gray-600">活跃订阅：0 个</div>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                        <div class="relative flex-1">
                            <input type="text" id="searchFeeds" class="input w-full pl-10" placeholder="搜索订阅名称或网址...">
                            <div class="absolute left-3 top-3 text-gray-400"><i class="fas fa-search"></i></div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="filterCategory" class="input w-40">
                                <option value="">所有分类</option>
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

                <div class="space-y-4" id="feedList">
                    <div class="text-center py-12 text-gray-500">加载订阅中...</div>
                </div>

                <div id="feedPagination" class="mt-8 pt-6 border-t border-gray-200 hidden"></div>
            </div>
        </div>
    </div>

        <script>
            window.__FEED_INDEX_INITIAL__ = @json(isset($indexInfo) ? $indexInfo : array());

            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };
            var feedIndexState = {
                categorys: [],
                feedSubs: [],
                pagination: null,
                currentPage: 1
            };
            var initialFeedIndexData = window.__FEED_INDEX_INITIAL__ || {};

            function escapeHtml(text) {
                return String(text || '').replace(/[&<>"']/g, function(c) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
                });
            }

            function normalizeStatus(rawStatus) {
                if (rawStatus === 'active' || Number(rawStatus) === 1) return 'active';
                if (rawStatus === 'error' || Number(rawStatus) === 3) return 'error';
                return 'inactive';
            }

            function shortText(text, maxLen) {
                var s = String(text || '');
                return s.length > maxLen ? s.slice(0, maxLen - 3) + '...' : s;
            }

            function renderCategoryOptions(categorys) {
                var $radioList = $('#categoryRadioList');
                var $emptyTips = $('#categoryEmptyTips');
                var $filterCategory = $('#filterCategory');

                $radioList.html('');
                $filterCategory.html('<option value="">所有分类</option>');

                if (!categorys.length) {
                    $emptyTips.removeClass('hidden');
                    return;
                }

                $emptyTips.addClass('hidden');
                var radioHtml = '';
                categorys.forEach(function(category, idx) {
                    var id = Number(category.id || 0);
                    var checked = idx === 0 ? 'checked' : '';
                    var name = escapeHtml(category.name || '未命名分类');
                    radioHtml += ''
                        + '<label class="relative cursor-pointer">'
                        + '<input type="radio" name="category_id" value="' + id + '" class="sr-only peer" ' + checked + '>'
                        + '<div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm transition-all duration-200">'
                        + '<div class="flex items-center space-x-3"><div class="w-8 h-8 rounded-md bg-blue-100 flex items-center justify-center"><i class="fas fa-folder text-blue-600"></i></div>'
                        + '<div><div class="font-medium text-gray-900">' + name + '</div></div></div></div></label>';
                    $filterCategory.append('<option value="' + id + '">' + name + '</option>');
                });
                $radioList.html(radioHtml);
            }

            function renderFeedList(feedSubs) {
                var $list = $('#feedList');
                if (!feedSubs.length) {
                    $list.html(
                        '<div class="text-center py-16">'
                        + '<div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-50 to-purple-50 rounded-full flex items-center justify-center mb-6"><i class="fas fa-rss text-blue-400 text-3xl"></i></div>'
                        + '<h3 class="text-xl font-medium text-gray-900 mb-3">开始您的第一个订阅</h3>'
                        + '<p class="text-gray-600 mb-8 max-w-md mx-auto">订阅您喜欢的博客、新闻和资讯源，轻松获取最新内容，不错过任何重要更新</p>'
                        + '<button onclick="document.getElementById(\\'url\\').focus()" class="btn btn-primary text-lg px-8 py-3"><i class="fas fa-plus mr-3"></i>添加第一个订阅</button>'
                        + '</div>'
                    );
                    return;
                }

                var html = '';
                feedSubs.forEach(function(feedSub) {
                    if (!feedSub.feed) return;
                    var subId = Number(feedSub.id || 0);
                    var categoryId = feedSub.category ? Number(feedSub.category.id || 0) : 0;
                    var status = normalizeStatus(feedSub.status);
                    var statusDot = status === 'active' ? 'bg-green-500' : (status === 'error' ? 'bg-red-500' : 'bg-gray-400');
                    var feedName = escapeHtml(feedSub.feed.feed_name || feedSub.feed_name || '未命名订阅');
                    var feedUrl = escapeHtml(feedSub.feed.url || '#');
                    var feedDesc = escapeHtml(feedSub.feed.feed_desc || '暂无描述');
                    var icon = feedSub.feed.icon ? '<img src="' + escapeHtml(feedSub.feed.icon) + '" alt="" class="w-8 h-8 rounded">' : '<i class="fas fa-rss text-blue-500 text-xl"></i>';
                    var categoryTag = feedSub.category
                        ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-folder mr-1 text-xs"></i>' + escapeHtml(feedSub.category.name) + '</span>'
                        : '';
                    var articlesCount = Number(feedSub.feed.articles_count || 0);
                    var updatedAt = escapeHtml(String(feedSub.updated_at || '').replace('T', ' ').slice(0, 16));
                    html += ''
                        + '<div id="' + subId + '" class="group flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow-md transition-all duration-200" data-category-id="' + categoryId + '" data-status="' + status + '">'
                        + '<div class="flex items-start space-x-4"><div class="relative"><div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 border border-gray-200 flex items-center justify-center">' + icon + '</div><div class="absolute -top-1 -right-1 w-4 h-4 ' + statusDot + ' rounded-full border border-white"></div></div>'
                        + '<div class="flex-1"><div class="flex items-start justify-between mb-2"><div><a href="' + feedUrl + '" target="_blank" class="text-lg font-semibold text-gray-900 hover:text-primary-color transition-colors duration-200 feed-name">' + feedName + '</a>' + categoryTag + '</div><div class="text-sm text-gray-500">文章：' + articlesCount + '</div></div>'
                        + '<p class="text-sm text-gray-600 mb-2 line-clamp-2 feed-desc" title="' + feedDesc + '">' + feedDesc + '</p>'
                        + '<div class="flex items-center text-sm text-gray-500 space-x-4"><span class="truncate max-w-xs feed-url" title="' + feedUrl + '"><i class="fas fa-link mr-1 text-xs"></i>' + shortText(feedUrl, 50) + '</span><span><i class="far fa-clock mr-1 text-xs"></i>更新于 ' + updatedAt + '</span></div></div></div>'
                        + '<div class="flex items-center space-x-2 ml-4"><a href="/articles?feed_id=' + Number(feedSub.feed.id || 0) + '" class="btn btn-sm btn-outline flex items-center" title="查看文章"><i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">文章</span></a>'
                        + '<a href="/feed/' + subId + '" class="btn btn-sm btn-outline flex items-center" title="编辑订阅"><i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">编辑</span></a>'
                        + '<button onclick="confirmDeleteFeed(' + subId + ', \'' + feedName.replace(/'/g, "\\'") + '\')" class="btn btn-sm btn-outline text-red-600 border-red-600 hover:bg-red-50 flex items-center" title="删除订阅"><i class="fas fa-trash-alt mr-1"></i><span class="hidden sm:inline">删除</span></button></div></div>';
                });
                $list.html(html || '<div class="text-center py-12 text-gray-500">暂无订阅</div>');
            }

            function renderFeedStats() {
                var total = (feedIndexState.pagination && Number(feedIndexState.pagination.total)) || feedIndexState.feedSubs.length;
                var active = feedIndexState.feedSubs.filter(function(item) {
                    return normalizeStatus(item.status) === 'active';
                }).length;
                $('#feedTotalBadge').text(total + ' 个');
                $('#feedActiveCountText').text('活跃订阅：' + active + ' 个');
            }

            function renderFeedPagination() {
                var p = feedIndexState.pagination || {};
                var $wrap = $('#feedPagination');
                if (!p.total || Number(p.last_page || 1) <= 1) {
                    $wrap.addClass('hidden').html('');
                    return;
                }
                var prevDisabled = !p.prev_page_url ? 'disabled opacity-50 cursor-not-allowed' : '';
                var nextDisabled = !p.next_page_url ? 'disabled opacity-50 cursor-not-allowed' : '';
                var html = ''
                    + '<div class="flex items-center justify-between">'
                    + '<div class="text-sm text-gray-700">共 ' + Number(p.total || 0) + ' 个订阅</div>'
                    + '<div class="flex items-center space-x-2">'
                    + '<button class="btn btn-secondary ' + prevDisabled + '" id="feedPrevPage"><i class="fas fa-chevron-left mr-2"></i>上一页</button>'
                    + '<span class="text-sm text-gray-600">第 ' + Number(p.current_page || 1) + ' / ' + Number(p.last_page || 1) + ' 页</span>'
                    + '<button class="btn btn-secondary ' + nextDisabled + '" id="feedNextPage">下一页<i class="fas fa-chevron-right ml-2"></i></button>'
                    + '</div></div>';
                $wrap.removeClass('hidden').html(html);
                $('#feedPrevPage').off('click').on('click', function() {
                    if (p.prev_page_url) {
                        loadFeedIndexData(Math.max(1, Number(p.current_page || 1) - 1));
                    }
                });
                $('#feedNextPage').off('click').on('click', function() {
                    if (p.next_page_url) {
                        loadFeedIndexData(Number(p.current_page || 1) + 1);
                    }
                });
            }

            function loadFeedIndexData(page) {
                var keepOnError = arguments.length > 1 && arguments[1] && arguments[1].keepOnError === true;
                if (!apiRequest) {
                    if (!keepOnError) {
                        showToast('error', 'API客户端未初始化');
                    }
                    return;
                }
                var currentUrl = ($('#url').val() || '').trim();
                var params = { page: page || 1 };
                if (currentUrl) params.url = currentUrl;
                apiRequest('GET', '/feeds', params).then(function(resp) {
                    if (!resp || resp.code !== 9999 || !resp.result) {
                        throw new Error((resp && resp.msg) || '加载失败');
                    }
                    var data = resp.result || {};
                    feedIndexState.categorys = Array.isArray(data.categorys) ? data.categorys : [];
                    feedIndexState.feedSubs = Array.isArray(data.feed_subs) ? data.feed_subs : [];
                    feedIndexState.pagination = data.pagination || null;
                    feedIndexState.currentPage = Number((data.pagination && data.pagination.current_page) || 1);

                    if (data.url) $('#url').val(data.url);
                    if (data.title) $('#feed_name').val(data.title);
                    renderCategoryOptions(feedIndexState.categorys);
                    renderFeedList(feedIndexState.feedSubs);
                    renderFeedStats();
                    renderFeedPagination();
                    filterFeeds();
                }).catch(function() {
                    if (keepOnError) {
                        return;
                    }
                    $('#feedList').html('<div class="text-center py-12 text-gray-500">订阅加载失败，请稍后重试</div>');
                });
            }

            function renderInitialFeedIndex() {
                var data = initialFeedIndexData || {};
                feedIndexState.categorys = Array.isArray(data.categorys) ? data.categorys : [];
                var initialSubs = data.feedSubs && data.feedSubs.data ? data.feedSubs.data : data.feedSubs;
                feedIndexState.feedSubs = Array.isArray(initialSubs) ? initialSubs : [];
                feedIndexState.pagination = data.feedSubs && data.feedSubs.current_page ? {
                    total: data.feedSubs.total,
                    current_page: data.feedSubs.current_page,
                    per_page: data.feedSubs.per_page,
                    last_page: data.feedSubs.last_page,
                    next_page_url: data.feedSubs.next_page_url,
                    prev_page_url: data.feedSubs.prev_page_url,
                    has_more_pages: data.feedSubs.has_more_pages
                } : null;
                renderCategoryOptions(feedIndexState.categorys);
                renderFeedList(feedIndexState.feedSubs);
                renderFeedStats();
                renderFeedPagination();
                filterFeeds();
            }

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
                    if (window.taskApiFetch) {
                        window.taskApiFetch('/feed/checkFeedUrl?url=' + encodeURIComponent(url), {
                            method: 'GET'
                        }).then(function(resp) {
                            return resp.json();
                        }).then(function(response) {
                            if (response && response.code === 9999) {
                                document.getElementById('feedInfo').classList.remove('hidden');
                                document.getElementById('feed_name').value = response.result.title;
                                processTips.innerHTML = `
                    <div class="flex items-center text-green-600">
                        <i class="fas fa-check-circle mr-2"></i>
                        检测成功！发现订阅：<strong class="ml-1">${response.result.title}</strong>
                    </div>
                `;
                                submitBtn.disabled = false;
                                document.getElementById('feed_name').focus();
                                showToast('success', '订阅地址检测成功');
                                return;
                            }
                            throw new Error((response && response.msg) ? response.msg : '检测失败');
                        }).catch(function() {
                            processTips.innerHTML = `
                <div class="flex items-center text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    网络错误，请稍后重试
                </div>
            `;
                            showToast('error', '网络错误，请检查连接');
                        });
                        return;
                    }
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
                                loadFeedIndexData(feedIndexState.currentPage || 1);
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
                const presetUrl = new URLSearchParams(window.location.search).get('url');
                if (presetUrl) {
                    document.getElementById('url').value = presetUrl;
                }
                renderInitialFeedIndex();
                loadFeedIndexData(1, { keepOnError: true });

                var refreshAllFeedsBtn = document.getElementById('refreshAllFeedsBtn');
                if (refreshAllFeedsBtn && apiRequest) {
                    refreshAllFeedsBtn.addEventListener('click', function() {
                        var button = this;
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>更新中...';
                        apiRequest('POST', '/feeds/refresh', {}).then(function(resp) {
                            if (resp && resp.code === 9999) {
                                showToast('success', '已更新 ' + Number((resp.result || {}).success_count || 0) + ' 个订阅源');
                                loadFeedIndexData(1, { keepOnError: true });
                            } else {
                                showToast('warning', (resp && resp.msg) ? resp.msg : '更新失败');
                            }
                        }).catch(function(err) {
                            showToast('warning', (err && err.message) ? err.message : '更新失败，请稍后重试');
                        }).finally(function() {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>立即更新订阅';
                        });
                    });
                }

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
                        var formEl = this;
                        if (this.dataset.nativeSubmit === '1') {
                            delete this.dataset.nativeSubmit;
                            return;
                        }

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
                            this.submit();
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
                                resetForm();
                                loadFeedIndexData(1, { keepOnError: true });
                                return;
                            }
                            showToast('error', (resp && resp.msg) ? resp.msg : '添加失败');
                        }).catch(function(err) {
                            if (err && (err.status === 401 || err.status === 403 || err.status === 0)) {
                                formEl.dataset.nativeSubmit = '1';
                                formEl.submit();
                                return;
                            }
                            showToast('error', '添加失败，请稍后重试');
                        }).finally(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }.bind(this));
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
                    const feedName = (item.querySelector('.feed-name')?.textContent || '').toLowerCase();
                    const feedDesc = (item.querySelector('.feed-desc')?.textContent || '').toLowerCase();
                    const feedUrl = (item.querySelector('.feed-url')?.textContent || '').toLowerCase();
                    const categoryId = item.getAttribute('data-category-id') || '';
                    const status = item.getAttribute('data-status') || 'inactive';

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
