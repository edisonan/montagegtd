<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 任务编辑模态框 -->
<div id="taskUpdateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl transform transition-all">
            <!-- 模态框头部 -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900" id="taskUpdateModalLabel">修改待办</h3>
                <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                        onclick="hideTaskUpdateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- 模态框内容 -->
            <div class="p-6">
                <div id="taskUpdateErrors" class="hidden p-4 mb-6 bg-red-50 border border-red-200 rounded-lg">
                    <ul id="taskUpdateErrorList" class="text-red-600 text-sm space-y-1"></ul>
                </div>

                <form id="taskUpdateForm" method="POST" class="space-y-6">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="id" id="task_id_input" value="">

                    <!-- 待办名称 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="task_name_input" class="font-medium text-gray-700">待办名称</label>
                        <div class="md:col-span-3">
                            <input type="text" name="name" id="task_name_input"
                                   class="input w-full"
                                   placeholder="请输入待办事项名称"
                                   required>
                        </div>
                    </div>

                    <!-- 父级任务 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <label for="parent_task_id_input" class="font-medium text-gray-700">父级任务</label>
                        <div class="md:col-span-3">
                            <select name="parent_task_id" id="parent_task_id_input" class="input w-full">
                                <option value="">-- 无父级任务 --</option>
                                <!-- 父级任务选项将通过JavaScript动态加载 -->
                            </select>
                        </div>
                    </div>

                    <!-- 待办等级 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">待办等级</div>
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="priority" value="1"
                                           class="w-4 h-4 text-blue-600" id="priority1_input">
                                    <span class="text-gray-700">不重要不紧急</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 cursor-pointer">
                                    <input type="radio" name="priority" value="2"
                                           class="w-4 h-4 text-blue-600" id="priority2_input">
                                    <span class="text-blue-700 font-medium">不重要紧急</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-orange-200 rounded-lg bg-orange-50 hover:bg-orange-100 cursor-pointer">
                                    <input type="radio" name="priority" value="3"
                                           class="w-4 h-4 text-blue-600" id="priority3_input">
                                    <span class="text-orange-700 font-medium">重要不紧急</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-red-200 rounded-lg bg-red-50 hover:bg-red-100 cursor-pointer">
                                    <input type="radio" name="priority" value="4"
                                           class="w-4 h-4 text-blue-600" id="priority4_input">
                                    <span class="text-red-700 font-medium">重要紧急</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 待办状态 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">待办状态</div>
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="status" value="1"
                                           class="w-4 h-4 text-blue-600" id="status1_input">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="text-gray-700">进行中</span>
                                    </div>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 cursor-pointer">
                                    <input type="radio" name="status" value="2"
                                           class="w-4 h-4 text-blue-600" id="status2_input">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span class="text-green-700 font-medium">已完成</span>
                                    </div>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="status" value="3"
                                           class="w-4 h-4 text-blue-600" id="status3_input">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-folder text-gray-500"></i>
                                        <span class="text-gray-700">已折叠</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 时间选择器 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 提醒时间 -->
                        <div class="space-y-2">
                            <label for="remindtime_input" class="font-medium text-gray-700">
                                <i class="far fa-bell mr-2 text-blue-500"></i>提醒时间
                            </label>
                            <div class="relative">
                                <input type="text" name="remindtime" id="remindtime_input"
                                       class="input w-full pl-10"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})"
                                       placeholder="选择提醒时间">
                                <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>

                        <!-- 截止时间 -->
                        <div class="space-y-2">
                            <label for="deadline_input" class="font-medium text-gray-700">
                                <i class="far fa-clock mr-2 text-red-500"></i>截止时间
                            </label>
                            <div class="relative">
                                <input type="text" name="deadline" id="deadline_input"
                                       class="input w-full pl-10"
                                       onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})"
                                       placeholder="选择截止时间">
                                <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- 待办置顶 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">待办置顶</div>
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="is_top" value="0"
                                           class="w-4 h-4 text-blue-600" id="is_top1_input">
                                    <span class="text-gray-700">不置顶</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-yellow-200 rounded-lg bg-yellow-50 hover:bg-yellow-100 cursor-pointer">
                                    <input type="radio" name="is_top" value="1"
                                           class="w-4 h-4 text-blue-600" id="is_top2_input">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-thumbtack text-yellow-500"></i>
                                        <span class="text-yellow-700 font-medium">置顶</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 模式 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="font-medium text-gray-700">模式</div>
                        <div class="md:col-span-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center space-x-3 p-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 cursor-pointer">
                                    <input type="radio" name="mode" value="1"
                                           class="w-4 h-4 text-blue-600" id="mode1_input">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-briefcase text-blue-500"></i>
                                        <span class="text-blue-700 font-medium">工作</span>
                                    </div>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 cursor-pointer">
                                    <input type="radio" name="mode" value="2"
                                           class="w-4 h-4 text-blue-600" id="mode2_input">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-home text-green-500"></i>
                                        <span class="text-green-700 font-medium">生活</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 模态框底部 -->
            <div class="flex items-center justify-end p-6 border-t border-gray-200 space-x-3">
                <button type="button" class="btn btn-outline" onclick="hideTaskUpdateModal()">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitTaskUpdateForm()">
                    <i class="fas fa-save mr-2"></i>保存修改
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* 日期选择器输入框样式 */
    .datepicker-input {
        cursor: pointer;
    }

    /* 选项标签悬停效果 */
    .option-label {
        transition: all 0.2s ease;
    }

    .option-label:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* 选中的选项样式 */
    input[type="radio"]:checked + * {
        font-weight: 600;
    }

    /* 自定义单选按钮样式 */
    input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-color);
    }
</style>

<script>
    let isTaskModalOpen = false;

    // 模态框控制函数
    function showTaskUpdateModal() {
        const modal = document.getElementById('taskUpdateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        isTaskModalOpen = true;

        // 添加动画效果
        setTimeout(() => {
            const dialog = modal.querySelector('.bg-white');
            dialog.classList.add('scale-100', 'opacity-100');
            dialog.classList.remove('scale-95', 'opacity-0');
        }, 10);

        // 自动聚焦到第一个输入框
        setTimeout(() => {
            document.getElementById('task_name_input').focus();
        }, 300);
    }

    function hideTaskUpdateModal() {
        const modal = document.getElementById('taskUpdateModal');
        const dialog = modal.querySelector('.bg-white');

        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            isTaskModalOpen = false;
        }, 300);
    }

    // 任务编辑表单提交函数
    function submitTaskUpdateForm() {
        var form = document.getElementById('taskUpdateForm');
        var formData = new FormData(form);
        var taskId = $('#task_id_input').val();

        $.ajax({
            url: '/task/' + taskId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                // 显示加载状态
                const submitBtn = $('#taskUpdateModal .btn-primary');
                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>保存中...');
            },
            success: function(response) {
                const submitBtn = $('#taskUpdateModal .btn-primary');
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save mr-2"></i>保存修改');

                if(response.code == 9999) {
                    hideTaskUpdateModal();
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
                    $('#taskUpdateErrors').removeClass('hidden');

                    // 滚动到错误位置
                    $('#taskUpdateErrors')[0].scrollIntoView({ behavior: 'smooth' });
                }
            },
            error: function(xhr) {
                const submitBtn = $('#taskUpdateModal .btn-primary');
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save mr-2"></i>保存修改');

                // 显示验证错误
                $('#taskUpdateErrorList').empty();
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#taskUpdateErrorList').append('<li>' + value[0] + '</li>');
                    });
                } else {
                    $('#taskUpdateErrorList').append('<li>更新失败，请稍后重试</li>');
                }
                $('#taskUpdateErrors').removeClass('hidden');

                // 滚动到错误位置
                $('#taskUpdateErrors')[0].scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // 加载父级任务下拉框数据
    function loadParentTasks(excludeTaskId = null, currentParentTaskId = null) {
        let url = '/taskparenttasks';
        if(excludeTaskId) {
            url += '?exclude_task_id=' + excludeTaskId;
        }

        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function() {
                // 显示加载状态
                const select = $('#parent_task_id_input');
                select.html('<option value="">加载中...</option>');
            },
            success: function(response) {
                if(response.code == 9999) {
                    var selectElement = $('#parent_task_id_input');
                    selectElement.empty();
                    selectElement.append('<option value="">-- 无父级任务 --</option>');

                    $.each(response.result, function(index, task) {
                        type = task.mode == 1 ? '[工作]' : '[生活]';
                        statusIcon = task.status == 2 ? '✓ ' : task.status == 3 ? '🗂 ' : '';
                        selectElement.append('<option value="' + task.id + '">' + type + ' ' + statusIcon + task.name + '</option>');
                    });

                    // 如果提供了当前父任务ID，则选中该项
                    if(currentParentTaskId) {
                        selectElement.val(currentParentTaskId);
                    }
                }
            },
            error: function(xhr) {
                console.log('加载父级任务失败');
                const select = $('#parent_task_id_input');
                select.html('<option value="">加载失败，请重试</option>');
            }
        });
    }

    // 打开任务编辑模态框的函数
    function openTaskUpdateModal(taskData) {
        console.log('openTaskUpdateModal 函数被调用，任务数据:', taskData);

        // 先加载父级任务选项，排除当前任务本身，并设置当前父任务ID
        loadParentTasks(taskData.id, taskData.parent_task_id);

        // 填充表单数据
        $('#task_name_input').val(taskData.name);
        $('#task_id_input').val(taskData.id);

        // 设置单选按钮
        $('input[name="priority"][value="' + (taskData.priority || 1) + '"]').prop('checked', true);
        $('input[name="status"][value="' + (taskData.status || 1) + '"]').prop('checked', true);
        $('input[name="is_top"][value="' + (taskData.is_top || 0) + '"]').prop('checked', true);
        $('input[name="mode"][value="' + (taskData.mode || 1) + '"]').prop('checked', true);

        // 设置日期时间
        $('#remindtime_input').val(taskData.remindtime || '');
        $('#deadline_input').val(taskData.deadline || '');

        // 清除错误信息
        $('#taskUpdateErrors').addClass('hidden');
        $('#taskUpdateErrorList').empty();

        // 显示模态框
        showTaskUpdateModal();
    }

    // 初始化事件监听
    $(document).ready(function() {
        // 点击模态框外部关闭
        $('#taskUpdateModal').on('click', function(e) {
            if (e.target === this) {
                hideTaskUpdateModal();
            }
        });

        // ESC键关闭模态框
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && isTaskModalOpen) {
                hideTaskUpdateModal();
            }
        });

        // 表单提交事件
        $('#taskUpdateForm').on('submit', function(e) {
            e.preventDefault();
            submitTaskUpdateForm();
        });

        // 为所有输入框添加回车键提交
        $('#taskUpdateForm input').on('keydown', function(e) {
            if (e.key === 'Enter' && !$(this).is('input[type="radio"], input[type="checkbox"]')) {
                e.preventDefault();
                submitTaskUpdateForm();
            }
        });
    });
</script>