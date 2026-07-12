@extends('layouts.app')
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

@section('content')
<div class="container">
    <div class="col-md-offset-2 col-md-8">
        <div class="card">
            <div class="card-header">修改待办</div>
            <div class="card-body">
                <form class="form-horizontal" id="taskUpdateForm">
                    <div class="form-group row">
                        <label for="name" class="col-md-3 control-label">待办名称</label>
                        <div class="col-md-8"><input type="text" name="name" id="name" class="form-control"></div>
                    </div>

                    <div class="form-group row">
                        <label for="parent_task_id" class="col-md-3 control-label">父级任务</label>
                        <div class="col-md-8">
                            <select name="parent_task_id" id="parent_task_id" class="form-control">
                                <option value="">-- 无父级任务 --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label">待办等级</label>
                        <div class="col-md-8">
                            <label class="radio-inline"><input type="radio" name="priority" value="1"><span>不重要不紧急事项</span></label>
                            <label class="radio-inline"><input type="radio" name="priority" value="2"><span>不重要紧急事项</span></label>
                            <label class="radio-inline"><input type="radio" name="priority" value="3"><span>重要不紧急事项</span></label>
                            <label class="radio-inline"><input type="radio" name="priority" value="4"><span>重要紧急事项</span></label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="remindtime" class="col-md-3 control-label">待办提醒时间</label>
                        <div class="col-md-8">
                            <input type="text" name="remindtime" id="remindtime" class="form-control" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="deadline" class="col-md-3 control-label">待办截止时间</label>
                        <div class="col-md-8">
                            <input type="text" name="deadline" id="deadline" class="form-control" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label">待办状态</label>
                        <div class="col-md-8">
                            <label class="radio-inline"><input type="radio" name="status" value="1"><span>进行中</span></label>
                            <label class="radio-inline"><input type="radio" name="status" value="2"><span>已完成</span></label>
                            <label class="radio-inline"><input type="radio" name="status" value="3"><span>已删除</span></label>
                            <label class="radio-inline"><input type="radio" name="status" value="4"><span>已折叠</span></label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label">待办置顶</label>
                        <div class="col-md-8">
                            <label class="radio-inline"><input type="radio" name="is_top" value="0"><span>不置顶</span></label>
                            <label class="radio-inline"><input type="radio" name="is_top" value="1"><span>置顶</span></label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 control-label">模式</label>
                        <div class="col-md-8">
                            <label class="radio-inline"><input type="radio" name="mode" value="1"><span>工作</span></label>
                            <label class="radio-inline"><input type="radio" name="mode" value="2"><span>生活</span></label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-offset-3 col-md-6">
                            <button type="submit" class="btn btn-primary" id="taskUpdateSubmitBtn"><i class="fa fa-btn fa-plus"></i>提交！</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    var form = document.getElementById('taskUpdateForm');
    var submitBtn = document.getElementById('taskUpdateSubmitBtn');

    function getTaskId() {
        var parts = window.location.pathname.split('/').filter(Boolean);
        return Number(parts[parts.length - 1] || 0);
    }

    function setRadio(name, value) {
        var el = form.querySelector('input[name="' + name + '"][value="' + value + '"]');
        if (el) el.checked = true;
    }

    function loadParentTasks(excludeTaskId, selectedId) {
        if (!apiRequest) return Promise.resolve();
        return apiRequest('GET', '/tasks/parent-tasks', { exclude_task_id: excludeTaskId }).then(function(resp) {
            if (!resp || resp.code !== 9999) return;
            var list = Array.isArray(resp.result) ? resp.result : [];
            var select = document.getElementById('parent_task_id');
            select.innerHTML = '<option value="">-- 无父级任务 --</option>';
            list.forEach(function(task) {
                var opt = document.createElement('option');
                opt.value = task.id;
                opt.textContent = task.name;
                if (String(selectedId || '') === String(task.id)) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        });
    }

    function loadTask() {
        var taskId = getTaskId();
        if (!apiRequest || !taskId) {
            alert('API客户端未初始化或任务ID无效');
            return;
        }

        apiRequest('GET', '/tasks/' + taskId, {}).then(function(resp) {
            if (!resp || resp.code !== 9999 || !resp.result) {
                throw new Error((resp && resp.msg) || '加载失败');
            }
            var task = resp.result;
            document.getElementById('name').value = task.name || '';
            document.getElementById('remindtime').value = task.remindtime || '';
            document.getElementById('deadline').value = task.deadline || '';
            setRadio('priority', task.priority || 1);
            setRadio('status', task.status || 1);
            setRadio('is_top', task.is_top || 0);
            setRadio('mode', task.mode || 1);
            return loadParentTasks(taskId, task.parent_task_id);
        }).catch(function(err) {
            alert(err && err.message ? err.message : '加载失败');
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var taskId = getTaskId();
        if (!apiRequest || !taskId) {
            alert('API客户端未初始化或任务ID无效');
            return;
        }

        var original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 提交中...';

        apiRequest('PUT', '/tasks/' + taskId, {
            name: (document.getElementById('name') || {}).value || '',
            parent_task_id: (document.getElementById('parent_task_id') || {}).value || '',
            priority: (form.querySelector('input[name="priority"]:checked') || {}).value || '',
            remindtime: (document.getElementById('remindtime') || {}).value || '',
            deadline: (document.getElementById('deadline') || {}).value || '',
            status: (form.querySelector('input[name="status"]:checked') || {}).value || '',
            is_top: (form.querySelector('input[name="is_top"]:checked') || {}).value || '',
            mode: (form.querySelector('input[name="mode"]:checked') || {}).value || ''
        }).then(function(resp) {
            if (resp && resp.code === 9999) {
                window.location.href = '/tasks';
                return;
            }
            alert((resp && resp.msg) ? resp.msg : '更新失败');
        }).catch(function() {
            alert('网络错误，请稍后重试');
        }).finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        });
    });

    loadTask();
});
</script>
@endsection
