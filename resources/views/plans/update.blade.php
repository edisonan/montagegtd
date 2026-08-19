@extends('layouts.app')

@section('title', '修改计划 - 蒙太奇')
@section('description', '编辑计划信息')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">修改计划</h1>
        <a href="{{ url('/plans') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>返回</a>
    </div>

    <div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="p-6 border-b border-gray-200">
            <div class="text-sm text-gray-500">计划ID: <span id="planIdText">-</span></div>
            <div class="text-sm text-gray-500 mt-1">创建时间: <span id="planCreatedAt">-</span></div>
            <div class="text-sm text-gray-500 mt-1">更新时间: <span id="planUpdatedAt">-</span></div>
        </div>

        <div class="p-6">
            <form id="planForm" class="space-y-4">
                <div>
                    <label for="planName" class="block text-sm font-medium text-gray-700 mb-2">计划名称</label>
                    <input id="planName" name="name" type="text" class="input w-full" maxlength="255" required>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <button id="deleteGoalBtn" type="button" class="btn btn-outline text-red-600 border-red-600 hover:bg-red-50">
                        <i class="fas fa-trash-alt mr-2"></i>删除
                    </button>
                    <button id="saveGoalBtn" type="submit" class="btn btn-primary">
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

    function getGoalId() {
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

    var id = getGoalId();
    document.getElementById('planIdText').textContent = id || '-';

    if (!apiRequest || !id) {
        showToast('页面参数或API不可用', 'error');
        return;
    }

    apiRequest('GET', '/plans/' + id, {}).then(function(resp) {
        if (!resp || resp.code !== 9999 || !resp.result || !resp.result.plan) {
            throw new Error((resp && resp.msg) || '加载失败');
        }
        var plan = resp.result.plan;
        document.getElementById('planName').value = plan.name || '';
        document.getElementById('planCreatedAt').textContent = fmtTime(plan.created_at);
        document.getElementById('planUpdatedAt').textContent = fmtTime(plan.updated_at);
    }).catch(function(err) {
        showToast(err && err.message ? err.message : '加载失败', 'error');
    });

    document.getElementById('planForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('saveGoalBtn');
        var old = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

        apiRequest('PUT', '/plans/' + id, { name: document.getElementById('planName').value.trim() }).then(function(resp) {
            if (resp && resp.code === 9999) {
                showToast('保存成功', 'success');
                setTimeout(function() { window.location.href = '/plans'; }, 700);
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

    document.getElementById('deleteGoalBtn').addEventListener('click', function() {
        if (!confirm('确认删除该计划？')) return;
        apiRequest('DELETE', '/plans/' + id, {}).then(function(resp) {
            if (resp && resp.code === 9999) {
                showToast('删除成功', 'success');
                setTimeout(function() { window.location.href = '/plans'; }, 700);
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
