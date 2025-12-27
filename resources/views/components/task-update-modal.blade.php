<div class="modal fade" id="taskUpdateModal" tabindex="-1" aria-labelledby="taskUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskUpdateModalLabel">修改待办</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="taskUpdateErrors" class="alert alert-danger" style="display: none;">
                    <ul id="taskUpdateErrorList"></ul>
                </div>

                <form id="taskUpdateForm" method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="PUT">

                    <!-- 待办名称 -->
                    <div class="form-group row mb-3">
                        <label for="task-name" class="col-md-2 col-form-label">待办名称</label>
                        <div class="col-md-10">
                            <input type="text" name="name" id="task_name_input" class="form-control" value="" placeholder="请输入待办事项名称" required>
                        </div>
                    </div>

                    <!-- 待办等级 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">待办等级</label>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" id="priority1_input" value="1">
                                        <label class="form-check-label" for="priority1_input">不重要不紧急</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" id="priority2_input" value="2">
                                        <label class="form-check-label" for="priority2_input">不重要紧急</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" id="priority3_input" value="3">
                                        <label class="form-check-label" for="priority3_input">重要不紧急</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" id="priority4_input" value="4">
                                        <label class="form-check-label" for="priority4_input">重要紧急</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 待办状态 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">待办状态</label>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status1_input" value="1">
                                        <label class="form-check-label" for="status1_input">进行中</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status2_input" value="2">
                                        <label class="form-check-label" for="status2_input">已完成</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status3_input" value="3">
                                        <label class="form-check-label" for="status3_input">已折叠</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 待办提醒时间 -->
                    <div class="form-group row mb-3">
                        <label for="remindtime" class="col-md-2 col-form-label">提醒时间</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <input type="text" name="remindtime" id="remindtime_input" class="form-control" 
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})" placeholder="选择提醒时间">
                            </div>
                        </div>
                    </div>

                    <!-- 待办截止时间 -->
                    <div class="form-group row mb-3">
                        <label for="deadline" class="col-md-2 col-form-label">截止时间</label>
                        <div class="col-md-10">
                            <div class="input-group">
                                <input type="text" name="deadline" id="deadline_input" class="form-control" 
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})" placeholder="选择截止时间">
                            </div>
                        </div>
                    </div>

                    <!-- 待办置顶 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">待办置顶</label>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_top" id="is_top1_input" value="0">
                                        <label class="form-check-label" for="is_top1_input">不置顶</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_top" id="is_top2_input" value="1">
                                        <label class="form-check-label" for="is_top2_input">置顶</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 模式 -->
                    <div class="form-group row mb-3">
                        <label class="col-md-2 col-form-label">模式</label>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="mode1_input" value="1">
                                        <label class="form-check-label" for="mode1_input">工作</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="mode2_input" value="2">
                                        <label class="form-check-label" for="mode2_input">生活</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitTaskUpdateForm()">保存修改</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 任务编辑表单提交函数
    function submitTaskUpdateForm() {
        var form = document.getElementById('taskUpdateForm');
        var formData = $(form).serialize();
        var taskId = $('#taskUpdateForm input[name=id]').val();
        
        $.ajax({
            url: '/task/' + taskId,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.code == 9999) {
                    $('#taskUpdateModal').modal('hide');
                    // 刷新页面或更新列表
                    location.reload();
                } else {
                    // 显示错误信息
                    $('#taskUpdateErrorList').empty();
                    if(response.msg) {
                        $('#taskUpdateErrorList').append('<li>' + response.msg + '</li>');
                    } else {
                        $('#taskUpdateErrorList').append('<li>更新失败</li>');
                    }
                    $('#taskUpdateErrors').show();
                }
            },
            error: function(xhr) {
                // 显示验证错误
                $('#taskUpdateErrorList').empty();
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#taskUpdateErrorList').append('<li>' + value[0] + '</li>');
                    });
                } else {
                    $('#taskUpdateErrorList').append('<li>更新失败，请稍后重试</li>');
                }
                $('#taskUpdateErrors').show();
            }
        });
    }
    
    // 打开任务编辑模态框的函数
    function openTaskUpdateModal(taskData) {
        // 填充表单数据
        $('#task_name_input').val(taskData.name);
        $('input[name="priority"][value="' + (taskData.priority || 1) + '"]').prop('checked', true);
        $('#remindtime_input').val(taskData.remindtime || '');
        $('#deadline_input').val(taskData.deadline || '');
        $('input[name="status"][value="' + (taskData.status || 1) + '"]').prop('checked', true);
        $('input[name="is_top"][value="' + (taskData.is_top || 0) + '"]').prop('checked', true);
        $('input[name="mode"][value="' + (taskData.mode || 1) + '"]').prop('checked', true);
        
        // 添加隐藏字段存储任务ID
        if ($('#taskUpdateForm input[name=id]').length === 0) {
            $('#taskUpdateForm').append('<input type="hidden" name="id" value="">');
        }
        $('#taskUpdateForm input[name=id]').val(taskData.id);
        
        // 设置表单提交URL
        $('#taskUpdateForm').attr('action', '/task/' + taskData.id);
        
        // 清除错误信息
        $('#taskUpdateErrors').hide();
        $('#taskUpdateErrorList').empty();
        
        // 显示模态框
        $('#taskUpdateModal').modal('show');
    }
</script>