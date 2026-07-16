@extends('layouts.app')

@section('title', '分类管理 - 蒙太奇')
@section('description', '管理您的订阅分类，拖拽调整分类和订阅源顺序，高效组织阅读内容。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">分类管理</h1>
                    <p class="text-gray-600 mt-1">拖拽调整分类和订阅源的顺序</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="addCategoryBtn" class="btn btn-secondary"><i class="fas fa-folder-plus mr-2"></i>新增分类</button>
                    <a href="{{ url('/feeds') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        添加订阅
                    </a>
                    <a href="{{ url('/articles') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回阅读
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">分类展示</h2>
                    <p class="text-sm text-gray-500 mt-1">拖拽分类标题调整顺序，拖拽订阅源调整分类</p>
                </div>
                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                    <i class="fas fa-hand-pointer mr-1"></i>可拖拽
                </span>
            </div>

            <div class="p-6">
                <div id="settingLoading" class="text-center py-12 text-gray-500">加载中...</div>
                <div id="multi" class="space-y-4 hidden"></div>
                <div id="settingEmpty" class="hidden text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">暂无分类</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-6">您还没有创建任何分类，请先添加订阅源或创建分类。</p>
                    <div class="flex gap-3 justify-center">
                        <a href="{{ url('/feeds') }}" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>添加订阅</a>
                        <a href="{{ url('/articles') }}" class="btn btn-secondary"><i class="fas fa-newspaper mr-2"></i>浏览文章</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">确认删除</h3>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                </div>
                <p class="text-gray-700 text-center">确定要删除这个订阅源吗？此操作不可恢复。</p>
            </div>
            <div class="flex gap-3">
                <button type="button" class="modal-close btn btn-secondary flex-1">取消</button>
                <button type="button" id="confirmDelete" class="btn btn-danger flex-1"><i class="fas fa-trash-alt mr-2"></i>确认删除</button>
            </div>
        </div>
    </div>

    <div id="categoryModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 id="categoryModalTitle" class="text-lg font-semibold text-gray-900">新增分类</h3>
                <button type="button" class="category-modal-close p-2 text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="categoryForm">
                <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-2">分类名称</label>
                <input id="categoryName" type="text" maxlength="100" class="form-input w-full" placeholder="请输入分类名称" required>
                <div class="flex gap-3 mt-6">
                    <button type="button" class="category-modal-close btn btn-secondary flex-1">取消</button>
                    <button type="submit" id="saveCategoryBtn" class="btn btn-primary flex-1">保存</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .sortable-chosen { background-color: rgba(59, 130, 246, 0.05); border-color: var(--primary-color) !important; }
        .sortable-ghost { opacity: 0.4; background-color: var(--gray-100); }
        .category-card.sortable-drag { transform: rotate(2deg); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); }
        .feed-item.sortable-drag { transform: rotate(1deg); }
        .tile__handle:hover { background-color: var(--gray-100); }
        .modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
        .modal.show { opacity: 1; visibility: visible; }
        .modal-content { background: #fff; border-radius: 12px; padding: 24px; width: 90%; max-height: 90vh; overflow-y: auto; transform: translateY(20px); transition: transform 0.3s ease; }
        .modal.show .modal-content { transform: translateY(0); }
        .btn-danger { background: linear-gradient(135deg, var(--danger-color), #dc2626); color: #fff; }
        .btn-danger:hover { background: linear-gradient(135deg, #dc2626, var(--danger-color)); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3); }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.0/Sortable.min.js"></script>
    <script>
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        var currentFeedId = null;

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
        }

        function showNotification(type, message) {
            var node = document.createElement('div');
            var bg = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-yellow-500');
            var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
            node.className = 'fixed top-4 right-4 z-50 fade-in max-w-sm';
            node.innerHTML = '<div class="card ' + bg + ' text-white shadow-xl"><div class="p-4 flex items-center gap-3"><i class="fas ' + icon + ' text-lg"></i><div class="flex-1">' + escapeHtml(message) + '</div></div></div>';
            document.body.appendChild(node);
            setTimeout(function(){ if (node.parentNode) node.parentNode.removeChild(node); }, 3000);
        }

        function renderSetting(navInfos) {
            var multi = document.getElementById('multi');
            var html = '';
            navInfos.forEach(function(navInfo) {
                var category = navInfo.category_info || {};
                var list = Array.isArray(navInfo.list) ? navInfo.list : [];
                var categoryId = Number(category.category_id || 0);
                var categoryName = escapeHtml(category.category_name || '未命名分类');
                var feedItems = '';
                if (!list.length) {
                    feedItems = '<div class="text-center py-6"><div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-rss text-gray-300"></i></div><p class="text-gray-500 text-sm">暂无订阅源</p></div>';
                } else {
                    list.forEach(function(feed) {
                        var feedSubId = Number(feed.feed_sub_id || 0);
                        var feedName = escapeHtml(feed.feed_name || '未命名订阅');
                        feedItems += ''
                            + '<div id="' + feedSubId + '" data-category-id="' + categoryId + '" class="feed-item group cursor-move bg-white border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:shadow-sm transition-all duration-200">'
                            + '<div class="flex items-center justify-between">'
                            + '<div class="flex items-center gap-3 flex-1 min-w-0"><div class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded flex items-center justify-center"><i class="fas fa-rss text-gray-400 text-xs"></i></div><span class="font-medium text-gray-700 truncate">' + feedName + '</span></div>'
                            + '<div class="flex items-center gap-2 flex-shrink-0 ml-4"><a href="/feed/' + feedSubId + '" class="p-2 text-gray-400 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-colors" title="编辑"><i class="fas fa-edit"></i></a><button type="button" data-feed-id="' + feedSubId + '" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors delete-feed" title="删除"><i class="fas fa-trash-alt"></i></button></div>'
                            + '</div></div>';
                    });
                }

                html += ''
                    + '<div id="' + categoryId + '" class="category-card card hover:border-gray-300 transition-colors">'
                    + '<div class="tile__handle px-5 py-3 border-b border-gray-200 bg-gray-50 cursor-move flex items-center justify-between">'
                    + '<div class="flex items-center gap-3"><div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center"><i class="fas fa-folder text-white"></i></div><div><h3 class="font-semibold text-gray-800 text-lg">' + categoryName + '</h3><p class="text-xs text-gray-500 mt-1 category-count">' + list.length + ' 个订阅源</p></div></div><div class="flex items-center gap-1"><button type="button" class="edit-category p-2 text-gray-400 hover:text-blue-500" data-category-id="' + categoryId + '" data-category-name="' + categoryName + '" title="编辑分类"><i class="fas fa-edit"></i></button><button type="button" class="delete-category p-2 text-gray-400 hover:text-red-500" data-category-id="' + categoryId + '" data-category-name="' + categoryName + '" title="删除分类"><i class="fas fa-trash-alt"></i></button><i class="fas fa-arrows-alt text-gray-400 ml-2"></i></div>'
                    + '</div>'
                    + '<div class="tile__list p-4 bg-gray-50"><div class="space-y-2">' + feedItems + '</div></div>'
                    + '</div>';
            });

            multi.innerHTML = html;
        }

        function updateCategoryCounts() {
            document.querySelectorAll('.category-card').forEach(function(card) {
                var count = card.querySelectorAll('.feed-item').length;
                var countEl = card.querySelector('.category-count');
                if (countEl) countEl.textContent = count + ' 个订阅源';
            });
        }

        function initSortables() {
            var multiContainer = document.getElementById('multi');
            if (!multiContainer) return;

            Sortable.create(multiContainer, {
                animation: 150,
                handle: '.tile__handle',
                draggable: '.category-card',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    if (evt.oldIndex === evt.newIndex || !apiRequest) return;
                    var categoryIds = Array.from(document.querySelectorAll('.category-card')).map(function(card) { return card.id; }).join(',');
                    apiRequest('POST', '/categories/sort', { category_ids: categoryIds }).then(function(resp) {
                        if (!resp || resp.code !== 9999) showNotification('warning', '分类排序保存失败');
                    }).catch(function() { showNotification('warning', '分类排序保存失败'); });
                }
            });

            document.querySelectorAll('.tile__list').forEach(function(list) {
                Sortable.create(list, {
                    group: 'feeds',
                    animation: 150,
                    handle: '.feed-item',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        if (!apiRequest) return;
                        var item = evt.item;
                        var newCategoryId = item.closest('.category-card').id;
                        var originalCategoryId = item.getAttribute('data-category-id');
                        item.setAttribute('data-category-id', newCategoryId);

                        var feedIds = Array.from(item.parentNode.querySelectorAll('.feed-item')).map(function(el) { return el.id; }).join(',');
                        var categoryFeedSubIds = {};
                        document.querySelectorAll('.category-card').forEach(function(card) {
                            categoryFeedSubIds[card.id] = Array.from(card.querySelectorAll('.feed-item')).map(function(el) { return Number(el.id); });
                        });
                        var payload = { feed_sub_ids: feedIds, category_feed_sub_ids: categoryFeedSubIds };
                        if (originalCategoryId !== newCategoryId) {
                            payload.change_feed_sub_id = item.id;
                            payload.change_feed_sub_category = newCategoryId;
                        }

                        apiRequest('POST', '/feeds/sort', payload, {url: '/feeds/sort', method: 'POST'}).then(function(resp) {
                            if (!resp || resp.code !== 9999) {
                                showNotification('warning', '订阅排序保存失败');
                            } else if (originalCategoryId !== newCategoryId) {
                                showNotification('success', '已移动到新分类');
                                updateCategoryCounts();
                            }
                        }).catch(function() {
                            showNotification('warning', '订阅排序保存失败');
                        });
                    }
                });
            });
        }

        function loadSetting() {
            var loading = document.getElementById('settingLoading');
            var empty = document.getElementById('settingEmpty');
            var multi = document.getElementById('multi');
            if (!apiRequest) {
                loading.textContent = 'API客户端未初始化';
                return;
            }
            apiRequest('GET', '/feeds/navinfo', {}).then(function(resp) {
                loading.classList.add('hidden');
                if (!resp || resp.code !== 9999) {
                    loading.classList.remove('hidden');
                    loading.textContent = (resp && resp.msg) ? resp.msg : '加载失败';
                    return;
                }
                var navInfos = Array.isArray(resp.result && resp.result.nav_infos) ? resp.result.nav_infos : [];
                if (!navInfos.length) {
                    empty.classList.remove('hidden');
                    multi.classList.add('hidden');
                    return;
                }
                renderSetting(navInfos);
                multi.classList.remove('hidden');
                empty.classList.add('hidden');
                initSortables();
            }).catch(function() {
                loading.classList.remove('hidden');
                loading.textContent = '加载失败，请稍后重试';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var deleteModal = document.getElementById('deleteModal');
            var closeButtons = document.querySelectorAll('.modal-close');
            closeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deleteModal.classList.remove('show');
                    currentFeedId = null;
                });
            });

            document.querySelectorAll('.category-modal-close').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('categoryModal').classList.remove('show');
                });
            });

            document.addEventListener('click', function(e) {
                var deleteBtn = e.target.closest('.delete-feed');
                if (deleteBtn) {
                    currentFeedId = deleteBtn.getAttribute('data-feed-id');
                    deleteModal.classList.add('show');
                }

                var editCategoryBtn = e.target.closest('.edit-category');
                if (editCategoryBtn) {
                    openCategoryModal(editCategoryBtn.getAttribute('data-category-id'), editCategoryBtn.getAttribute('data-category-name'));
                }
                var deleteCategoryBtn = e.target.closest('.delete-category');
                if (deleteCategoryBtn) {
                    deleteCategory(deleteCategoryBtn.getAttribute('data-category-id'), deleteCategoryBtn.getAttribute('data-category-name'));
                }
            });

            document.getElementById('addCategoryBtn').addEventListener('click', function() { openCategoryModal('', ''); });
            document.getElementById('categoryForm').addEventListener('submit', saveCategory);

            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (!currentFeedId || !apiRequest) return;
                var btn = this;
                var text = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>删除中...';

                apiRequest('DELETE', '/feeds/' + currentFeedId, {}).then(function(resp) {
                    if (resp && resp.code === 9999) {
                        var feedEl = document.getElementById(currentFeedId);
                        if (feedEl && feedEl.parentNode) {
                            feedEl.parentNode.removeChild(feedEl);
                            updateCategoryCounts();
                        }
                        showNotification('success', '删除成功');
                    } else {
                        showNotification('error', (resp && resp.msg) ? resp.msg : '删除失败');
                    }
                    deleteModal.classList.remove('show');
                    currentFeedId = null;
                }).catch(function() {
                    showNotification('error', '删除失败，请稍后重试');
                }).finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = text;
                });
            });

            loadSetting();
        });

        function openCategoryModal(categoryId, categoryName) {
            document.getElementById('categoryModalTitle').textContent = categoryId ? '编辑分类' : '新增分类';
            document.getElementById('categoryName').value = categoryName || '';
            document.getElementById('categoryModal').setAttribute('data-category-id', categoryId || '');
            document.getElementById('categoryModal').classList.add('show');
            document.getElementById('categoryName').focus();
        }

        function saveCategory(e) {
            e.preventDefault();
            if (!apiRequest) return;
            var modal = document.getElementById('categoryModal');
            var categoryId = modal.getAttribute('data-category-id');
            var name = document.getElementById('categoryName').value.trim();
            if (!name) { showNotification('warning', '请输入分类名称'); return; }
            var button = document.getElementById('saveCategoryBtn');
            button.disabled = true;
            var method = categoryId ? 'PUT' : 'POST';
            var path = categoryId ? '/categories/' + Number(categoryId) : '/categories';
            apiRequest(method, path, {name: name}, {url: path, method: method}).then(function(resp) {
                if (!resp || resp.code !== 9999) { showNotification('error', (resp && resp.msg) || '保存失败'); return; }
                modal.classList.remove('show');
                showNotification('success', categoryId ? '分类已修改' : '分类已新增');
                loadSetting();
            }).catch(function(err) { showNotification('error', (err && err.message) || '保存失败，请稍后重试'); }).finally(function() { button.disabled = false; });
        }

        function deleteCategory(categoryId, categoryName) {
            if (!apiRequest || !categoryId || !window.confirm('确定删除分类“' + categoryName + '”吗？分类下必须没有订阅源。')) return;
            apiRequest('DELETE', '/categories/' + Number(categoryId), {}, {url: '/categories/' + Number(categoryId), method: 'DELETE'}).then(function(resp) {
                if (!resp || resp.code !== 9999) { showNotification('error', (resp && resp.msg) || '删除失败'); return; }
                showNotification('success', '分类已删除');
                loadSetting();
            }).catch(function(err) { showNotification('error', (err && err.message) || '删除失败，请稍后重试'); });
        }
    </script>
@endsection
