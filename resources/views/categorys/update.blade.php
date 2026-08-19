@extends('layouts.app')

@section('title', '修改分类 - 蒙太奇')
@section('description', '修改分类信息')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">修改分类</h1>
        <a href="{{ url('categorys') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>返回</a>
    </div>

    <div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="p-6 border-b border-gray-200">
            <div class="text-sm text-gray-500">分类ID: <span id="categoryIdText">-</span></div>
            <div class="text-sm text-gray-500 mt-1">创建时间: <span id="categoryCreatedAt">-</span></div>
            <div class="text-sm text-gray-500 mt-1">更新时间: <span id="categoryUpdatedAt">-</span></div>
        </div>

        <div class="p-6">
            <form id="categoryForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="name">分类名称</label>
                    <input id="name" name="name" type="text" class="input w-full" maxlength="100" required>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <button id="deleteBtn" type="button" class="btn btn-outline text-red-600 border-red-600 hover:bg-red-50">
                        <i class="fas fa-trash-alt mr-2"></i>删除
                    </button>
                    <button id="saveBtn" type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>保存
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

<script>
(function() {
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    function getCategoryId() {
        var parts = window.location.pathname.split('/').filter(Boolean);
        return Number(parts[parts.length - 1] || 0);
    }

    function showToast(msg, type) {
        var node = document.createElement('div');
        node.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded text-white ' + (type === 'error' ? 'bg-red-500' : 'bg-green-500');
        node.textContent = msg;
        document.body.appendChild(node);
        setTimeout(function() { if (node.parentNode) node.parentNode.removeChild(node); }, 2500);
    }

    function fmtTime(v) {
        if (!v) return '-';
        return String(v).replace('T', ' ').slice(0, 19);
    }

    var id = getCategoryId();
    document.getElementById('categoryIdText').textContent = id || '-';

    if (!apiRequest || !id) {
        showToast('页面参数或API不可用', 'error');
        return;
    }

    apiRequest('GET', '/categories/' + id, {}).then(function(resp) {
        if (!resp || resp.code !== 9999 || !resp.result) throw new Error((resp && resp.msg) || '加载失败');
        var category = resp.result;
        document.getElementById('name').value = category.name || '';
        document.getElementById('categoryCreatedAt').textContent = fmtTime(category.created_at);
        document.getElementById('categoryUpdatedAt').textContent = fmtTime(category.updated_at);
    }).catch(function(err) {
        showToast(err && err.message ? err.message : '加载失败', 'error');
    });

    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('saveBtn');
        var old = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

        apiRequest('PUT', '/categories/' + id, { name: document.getElementById('name').value.trim() }).then(function(resp) {
            if (resp && resp.code === 9999) {
                showToast('保存成功', 'success');
                setTimeout(function() { window.location.href = '/categorys'; }, 700);
                return;
            }
            throw new Error((resp && resp.msg) || '保存失败');
        }).catch(function(err) {
            showToast(err && err.message ? err.message : '保存失败', 'error');
        }).finally(function() {
            btn.disabled = false;
            btn.innerHTML = old;
        });
    });

    document.getElementById('deleteBtn').addEventListener('click', function() {
        if (!confirm('确认删除该分类？')) return;
        apiRequest('DELETE', '/categories/' + id, {}).then(function(resp) {
            if (resp && resp.code === 9999) {
                showToast('删除成功', 'success');
                setTimeout(function() { window.location.href = '/categorys'; }, 700);
                return;
            }
            throw new Error((resp && resp.msg) || '删除失败');
        }).catch(function(err) {
            showToast(err && err.message ? err.message : '删除失败', 'error');
        });
    });
})();
</script>
@endsection
