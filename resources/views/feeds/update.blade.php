@extends('layouts.app')

@section('title', '修改订阅 - 蒙太奇')
@section('description', '修改订阅源信息和分类设置')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">修改订阅</h1>
                <p class="text-gray-600 mt-1">更新订阅源信息和分类设置</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <a href="{{ url('feeds') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>返回订阅列表</a>
                <a href="{{ url('categorys') }}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-cog mr-2"></i>分类设置</a>
            </div>
        </div>

        <div id="feedEditLoading" class="card p-8 text-center text-gray-500">加载中...</div>

        <div id="feedEditContent" class="grid grid-cols-1 lg:grid-cols-3 gap-8 hidden">
            <div class="lg:col-span-2">
                <div class="card card-elevated">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">订阅信息</h2>
                        <p class="text-sm text-gray-500 mt-1">修改当前订阅的配置</p>
                    </div>

                    <div class="p-6">
                        <form class="space-y-6" id="feed-edit-form" method="POST" action="{{ url('feed/' . $feedSub->id) }}">
                            {{ csrf_field() }}
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">订阅源地址</h4>
                                        <p class="text-sm text-gray-500">当前订阅地址：<span id="feedUrlText" class="font-mono text-xs"></span></p>
                                    </div>
                                    <button type="button" id="checkFeedBtn" class="text-sm text-primary-600 hover:text-primary-800 font-medium"><i class="fas fa-sync-alt mr-1"></i>重新检测</button>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="feed_name" class="text-sm font-medium text-gray-700"><i class="fas fa-heading text-gray-400 mr-2"></i>订阅名称 <span class="text-red-500">*</span></label>
                                    <span class="text-xs text-gray-500" id="name-counter">0/100</span>
                                </div>
                                <input type="text" id="feed_name" class="input w-full" maxlength="100" required>
                            </div>

                            <div>
                                <label for="feed_order" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i>订阅排序</label>
                                <input type="number" id="feed_order" class="input w-48" min="0" step="1" placeholder="0">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm font-medium text-gray-700"><i class="fas fa-folder text-gray-400 mr-2"></i>所属分类 <span class="text-red-500">*</span></label>
                                    <a href="{{ url('categorys') }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-800"><i class="fas fa-plus-circle mr-1"></i>新增分类</a>
                                </div>
                                <div id="categoryList" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                            </div>

                            <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <button type="button" onclick="history.back()" class="btn btn-secondary"><i class="fas fa-times mr-2"></i>取消</button>
                                    <button type="submit" class="btn btn-primary" id="saveFeedBtn"><i class="fas fa-save mr-2"></i>更新订阅</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">订阅统计</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-600">订阅状态</span><span id="feedStatusText" class="text-sm text-gray-900">-</span></div>
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-600">最后更新</span><span id="feedUpdatedText" class="text-sm text-gray-900">-</span></div>
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-600">文章总数</span><span id="feedTotalText" class="text-sm font-medium text-gray-900">0</span></div>
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-600">未读文章</span><span id="feedUnreadText" class="text-sm font-medium text-primary-600">0</span></div>
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-600">收藏文章</span><span id="feedStarredText" class="text-sm font-medium text-yellow-600">0</span></div>
                        <div class="space-y-3 pt-4">
                            <a href="{{ url('/articles?feed_id=' . $feedSub->feed_id . '&status=all') }}" class="btn btn-primary w-full justify-center"><i class="fas fa-newspaper mr-2"></i>查看全部文章</a>
                            <button type="button" id="refreshFeedBtn" class="btn btn-secondary w-full justify-center"><i class="fas fa-sync-alt mr-2"></i>立即同步</button>
                            <button type="button" id="toggleStatusBtn" class="btn btn-outline w-full justify-center"></button>
                            <button type="button" id="clearArticlesBtn" class="btn btn-outline w-full justify-center text-red-600 border-red-600 hover:bg-red-50"><i class="fas fa-trash-alt mr-2"></i>清空文章</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.__FEED_EDIT_INITIAL__ = {!! json_encode(array(
            'feed_sub' => $feedSub,
            'categories' => $categories,
            'stats' => $stats,
        ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var feedState = { id: 0, feedSub: null, categories: [] };
        var initialFeedData = window.__FEED_EDIT_INITIAL__ || null;

        function showToast(message, type) {
            var node = document.createElement('div');
            var cls = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            node.className = 'fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ' + cls;
            node.textContent = message;
            document.body.appendChild(node);
            setTimeout(function(){ if(node.parentNode) node.parentNode.removeChild(node); }, 2500);
        }

        function getFeedIdFromPath() {
            var segs = window.location.pathname.split('/').filter(Boolean);
            return Number(segs[segs.length - 1] || 0);
        }

        function updateCounter() {
            var name = document.getElementById('feed_name').value || '';
            document.getElementById('name-counter').textContent = name.length + '/100';
        }

        function renderCategories(categories, selectedId) {
            var html = '';
            categories.forEach(function(category) {
                var id = Number(category.id || 0);
                var checked = id === Number(selectedId) ? 'checked' : '';
                html += ''
                    + '<label class="relative cursor-pointer">'
                    + '<input type="radio" name="category_id" value="' + id + '" class="sr-only peer" ' + checked + '>'
                    + '<div class="card border-2 border-gray-200 peer-checked:border-primary-500 peer-checked:bg-primary-50 transition-all hover:border-gray-300">'
                    + '<div class="p-3 text-sm font-medium text-gray-900">' + (category.name || '未命名分类') + '</div>'
                    + '</div></label>';
            });
            document.getElementById('categoryList').innerHTML = html;
        }

        function renderFeed(data) {
            var feedSub = data.feed_sub || {};
            var feed = feedSub.feed || {};
            var stats = data.stats || {};

            feedState.feedSub = feedSub;
            feedState.categories = Array.isArray(data.categories) ? data.categories : [];

            document.getElementById('feed_name').value = feedSub.feed_name || '';
            document.getElementById('feed_order').value = Number(feedSub.feed_order || 0);
            document.getElementById('feedUrlText').textContent = feed.url || '-';
            document.getElementById('feedUpdatedText').textContent = feed.updated_at ? String(feed.updated_at).replace('T', ' ').slice(0, 16) : '从未更新';
            document.getElementById('feedTotalText').textContent = Number(stats.total_articles || 0);
            document.getElementById('feedUnreadText').textContent = Number(stats.unread_articles || 0);
            document.getElementById('feedStarredText').textContent = Number(stats.starred_articles || 0);
            document.getElementById('feedStatusText').textContent = Number(feedSub.status) === 1 ? '启用中' : '已停用';

            var toggleBtn = document.getElementById('toggleStatusBtn');
            if (Number(feedSub.status) === 1) {
                toggleBtn.className = 'btn btn-outline w-full justify-center text-yellow-600 border-yellow-600 hover:bg-yellow-50';
                toggleBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>暂停订阅';
                toggleBtn.setAttribute('data-enable', '0');
            } else {
                toggleBtn.className = 'btn btn-outline w-full justify-center text-green-600 border-green-600 hover:bg-green-50';
                toggleBtn.innerHTML = '<i class="fas fa-play mr-2"></i>启用订阅';
                toggleBtn.setAttribute('data-enable', '1');
            }

            renderCategories(feedState.categories, feedSub.category_id);
            updateCounter();

            document.getElementById('feedEditLoading').classList.add('hidden');
            document.getElementById('feedEditContent').classList.remove('hidden');
        }

        function loadFeedDetail() {
            if (!apiRequest) {
                document.getElementById('feedEditLoading').textContent = 'API客户端未初始化';
                return;
            }
            apiRequest('GET', '/feeds/' + feedState.id, {}).then(function(resp) {
                if (!resp || resp.code !== 9999 || !resp.result) {
                    throw new Error((resp && resp.msg) || '加载失败');
                }
                renderFeed(resp.result);
            }).catch(function(err) {
                document.getElementById('feedEditLoading').textContent = err.message || '加载失败';
            });
        }

        function bindActions() {
            document.getElementById('feed_name').addEventListener('input', updateCounter);

            document.getElementById('feed-edit-form').addEventListener('submit', function(e) {
                if (this.dataset.nativeSubmit === '1') {
                    delete this.dataset.nativeSubmit;
                    return;
                }

                if (!apiRequest) {
                    return;
                }

                e.preventDefault();
                var checked = document.querySelector('input[name="category_id"]:checked');
                if (!checked) {
                    showToast('请选择分类', 'error');
                    return;
                }
                var btn = document.getElementById('saveFeedBtn');
                var text = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

                apiRequest('PUT', '/feeds/' + feedState.id, {
                    feed_name: (document.getElementById('feed_name').value || '').trim(),
                    feed_order: Number(document.getElementById('feed_order').value || 0),
                    category_id: Number(checked.value)
                }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('订阅更新成功', 'success');
                        setTimeout(function() { window.location.href = '/feeds'; }, 800);
                        return;
                    }
                    showToast((resp && resp.msg) || '保存失败', 'error');
                }).catch(function(err) {
                    if (err && (err.status === 401 || err.status === 403 || err.status === 0)) {
                        e.target.dataset.nativeSubmit = '1';
                        e.target.submit();
                        return;
                    }
                    showToast('保存失败，请稍后重试', 'error');
                }).finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = text;
                });
            });

            document.getElementById('checkFeedBtn').addEventListener('click', function() {
                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }
                var feedUrl = (feedState.feedSub && feedState.feedSub.feed && feedState.feedSub.feed.url) || '';
                if (!feedUrl) {
                    showToast('订阅地址为空', 'error');
                    return;
                }
                apiRequest('GET', '/feeds/check-feed-url', { url: feedUrl }).then(function(resp) {
                    if (resp && resp.code === 9999 && resp.result && resp.result.title) {
                        document.getElementById('feed_name').value = resp.result.title;
                        updateCounter();
                        showToast('检测成功', 'success');
                        return;
                    }
                    showToast('检测失败', 'error');
                }).catch(function() { showToast('检测失败', 'error'); });
            });

            document.getElementById('refreshFeedBtn').addEventListener('click', function() {
                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }
                apiRequest('POST', '/feeds/' + feedState.id + '/refresh', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('同步完成', 'success');
                        loadFeedDetail();
                        return;
                    }
                    showToast((resp && resp.msg) || '同步失败', 'error');
                }).catch(function() { showToast('同步失败', 'error'); });
            });

            document.getElementById('toggleStatusBtn').addEventListener('click', function() {
                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }
                var enable = Number(this.getAttribute('data-enable') || 0);
                apiRequest('POST', '/feeds/' + feedState.id + '/toggle-status', { enable: enable }).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('状态已更新', 'success');
                        loadFeedDetail();
                        return;
                    }
                    showToast((resp && resp.msg) || '更新失败', 'error');
                }).catch(function() { showToast('更新失败', 'error'); });
            });

            document.getElementById('clearArticlesBtn').addEventListener('click', function() {
                if (!apiRequest) {
                    showToast('API客户端未初始化', 'error');
                    return;
                }
                if (!confirm('确定要清空该订阅的所有文章吗？')) return;
                apiRequest('POST', '/feeds/' + feedState.id + '/clear-articles', {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        showToast('文章已清空', 'success');
                        loadFeedDetail();
                        return;
                    }
                    showToast((resp && resp.msg) || '操作失败', 'error');
                }).catch(function() { showToast('操作失败', 'error'); });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            bindActions();
            if (initialFeedData) {
                feedState.id = Number(initialFeedData.feed_sub && initialFeedData.feed_sub.id ? initialFeedData.feed_sub.id : getFeedIdFromPath());
                renderFeed(initialFeedData);
                return;
            }

            feedState.id = getFeedIdFromPath();
            loadFeedDetail();
        });
    </script>
@endsection
