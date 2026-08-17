<!-- 引入日期选择器 -->
<script src="{{'/js/My97DatePicker/WdatePicker.js'}}"></script>

<!-- 任务编辑模态框 -->
<div id="taskUpdateModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- 修复：使用fixed和flex居中容器 -->
    <div class="fixed inset-0 overflow-y-auto py-4 sm:py-8 px-4">
        <!-- 修复：添加flex居中容器（items-start + m-auto 防止超高内容顶部被截断） -->
        <div class="min-h-full flex items-start justify-center">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl transform transition-all m-auto">
                <!-- 模态框头部 - 固定 -->
                <div class="sticky top-0 z-10 bg-white rounded-t-lg flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">修改待办</h3>
                    <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            onclick="hideTaskUpdateModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- 模态框内容 - 可滚动区域 -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-240px)] p-4 sm:p-6">
                    <div id="taskUpdateErrors" class="hidden p-3 sm:p-4 mb-4 sm:mb-6 bg-red-50 border border-red-200 rounded-lg">
                        <ul id="taskUpdateErrorList" class="text-red-600 text-sm space-y-1"></ul>
                    </div>

                    <form id="taskUpdateForm" method="POST" class="space-y-4 sm:space-y-6">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="id" id="task_id_input" value="">

                        <!-- 待办名称 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                            <label for="task_name_input" class="font-medium text-gray-700 pt-2">待办名称</label>
                            <div class="md:col-span-3">
                                <input type="text" name="name" id="task_name_input"
                                       class="input w-full text-sm sm:text-base"
                                       placeholder="请输入待办事项名称"
                                       required>
                            </div>
                        </div>

                        <!-- 父级任务 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                            <label for="parent_task_id_input" class="font-medium text-gray-700 pt-2">父级任务</label>
                            <div class="md:col-span-3">
                                <select name="parent_task_id" id="parent_task_id_input" class="input w-full text-sm sm:text-base">
                                    <option value="">-- 无父级任务 --</option>
                                </select>
                            </div>
                        </div>

                        <!-- 待办等级 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">待办等级</div>
                            <div class="md:col-span-3">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="priority" value="1"
                                               class="w-4 h-4 text-blue-600">
                                        <span class="text-gray-700">不重要不紧急</span>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="priority" value="2"
                                               class="w-4 h-4 text-blue-600">
                                        <span class="text-blue-700 font-medium">不重要紧急</span>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-orange-200 rounded-lg bg-orange-50 hover:bg-orange-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="priority" value="3"
                                               class="w-4 h-4 text-blue-600">
                                        <span class="text-orange-700 font-medium">重要不紧急</span>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-red-200 rounded-lg bg-red-50 hover:bg-red-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="priority" value="4"
                                               class="w-4 h-4 text-blue-600">
                                        <span class="text-red-700 font-medium">重要紧急</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 待办状态 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">待办状态</div>
                            <div class="md:col-span-3">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-3">
                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="status" value="1"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                            <span class="text-gray-700">进行中</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="status" value="2"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                            <span class="text-green-700 font-medium">已完成</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="status" value="3"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-trash-alt text-red-500 text-sm"></i>
                                            <span class="text-gray-700">已删除</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="status" value="4"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-folder text-gray-500 text-sm"></i>
                                            <span class="text-gray-700">已折叠</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 时间选择器 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <!-- 提醒时间 -->
                            <div class="space-y-2">
                                <label for="remindtime_input" class="font-medium text-gray-700 text-sm sm:text-base">
                                    <i class="far fa-bell mr-2 text-blue-500"></i>提醒时间
                                </label>
                                <div class="relative">
                                    <input type="text" name="remindtime" id="remindtime_input"
                                           class="input w-full pl-10 text-sm sm:text-base"
                                           onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})"
                                           placeholder="选择提醒时间">
                                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>

                            <!-- 截止时间 -->
                            <div class="space-y-2">
                                <label for="deadline_input" class="font-medium text-gray-700 text-sm sm:text-base">
                                    <i class="far fa-clock mr-2 text-red-500"></i>截止时间
                                </label>
                                <div class="relative">
                                    <input type="text" name="deadline" id="deadline_input"
                                           class="input w-full pl-10 text-sm sm:text-base"
                                           onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',minDate:'%y-%M-%d'})"
                                           placeholder="选择截止时间">
                                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- 待办置顶 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">待办置顶</div>
                            <div class="md:col-span-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="is_top" value="0"
                                               class="w-4 h-4 text-blue-600">
                                        <span class="text-gray-700">不置顶</span>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-yellow-200 rounded-lg bg-yellow-50 hover:bg-yellow-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="is_top" value="1"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-thumbtack text-yellow-500 text-sm"></i>
                                            <span class="text-yellow-700 font-medium">置顶</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 模式 -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                            <div class="font-medium text-gray-700 pt-2">模式</div>
                            <div class="md:col-span-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="mode" value="1"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-briefcase text-blue-500 text-sm"></i>
                                            <span class="text-blue-700 font-medium">工作</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-2 p-2 sm:p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 cursor-pointer text-sm sm:text-base">
                                        <input type="radio" name="mode" value="2"
                                               class="w-4 h-4 text-blue-600">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-home text-green-500 text-sm"></i>
                                            <span class="text-green-700 font-medium">生活</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 模态框底部 - 固定 -->
                <div class="sticky bottom-0 bg-white rounded-b-lg flex items-center justify-end p-4 sm:p-6 border-t border-gray-200 space-x-2 sm:space-x-3">
                    <button type="button" class="btn btn-outline px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base" onclick="hideTaskUpdateModal()">取消</button>
                    <button type="button" class="btn btn-primary px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base" onclick="submitTaskUpdateForm()">
                        <i class="fas fa-save mr-1 sm:mr-2"></i>保存修改
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

            // 滚动到顶部
            const contentArea = modal.querySelector('.overflow-y-auto');
            if (contentArea) {
                contentArea.scrollTop = 0;
            }
        }, 300);
    }

    // 任务编辑表单提交函数
    function submitTaskUpdateForm() {
        var form = document.getElementById('taskUpdateForm');
        var formData = new FormData(form);
        var taskId = $('#task_id_input').val();
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;

        if (!apiRequest) {
            $('#taskUpdateErrorList').empty().append('<li>API客户端未初始化</li>');
            $('#taskUpdateErrors').removeClass('hidden');
            return;
        }

        var payload = {};
        formData.forEach(function(value, key) {
            if (key === '_token' || key === '_method') {
                return;
            }
            payload[key] = value;
        });

        const submitBtn = $('#taskUpdateModal .btn-primary');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>保存中...');

        apiRequest('PUT', '/tasks/' + taskId, payload).then(function(response) {
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-save mr-2"></i>保存修改');

            if(response.code == 9999) {
                hideTaskUpdateModal();
                location.reload();
            } else {
                $('#taskUpdateErrorList').empty();
                if(response.msg) {
                    $('#taskUpdateErrorList').append('<li>' + response.msg + '</li>');
                } else {
                    $('#taskUpdateErrorList').append('<li>更新失败</li>');
                }
                $('#taskUpdateErrors').removeClass('hidden');

                const errorElement = document.getElementById('taskUpdateErrors');
                if (errorElement) {
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }).catch(function(xhr) {
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-save mr-2"></i>保存修改');

            $('#taskUpdateErrorList').empty();
            if(xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function(key, value) {
                    $('#taskUpdateErrorList').append('<li>' + value[0] + '</li>');
                });
            } else {
                $('#taskUpdateErrorList').append('<li>更新失败，请稍后重试</li>');
            }
            $('#taskUpdateErrors').removeClass('hidden');

            const errorElement = document.getElementById('taskUpdateErrors');
            if (errorElement) {
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // 加载父级任务下拉框数据
    function loadParentTasks(excludeTaskId = null, currentParentTaskId = null) {
        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        if (!apiRequest) {
            const select = $('#parent_task_id_input');
            select.html('<option value="">API客户端未初始化</option>');
            return;
        }

        const select = $('#parent_task_id_input');
        select.html('<option value="">加载中...</option>');

        apiRequest('GET', '/tasks/parent-tasks', {
            exclude_task_id: excludeTaskId || ''
        }).then(function(response) {
            if(response.code == 9999) {
                var selectElement = $('#parent_task_id_input');
                selectElement.empty();
                selectElement.append('<option value="">-- 无父级任务 --</option>');

                $.each(response.result, function(index, task) {
                    var type = task.mode == 1 ? '[工作]' : '[生活]';
                    var statusIcon = task.status == 2 ? '✓ ' : task.status == 4 ? '[折叠] ' : '';
                    selectElement.append('<option value="' + task.id + '">' + type + ' ' + statusIcon + task.name + '</option>');
                });

                if(currentParentTaskId) {
                    selectElement.val(currentParentTaskId);
                }
            } else {
                select.html('<option value="">加载失败，请重试</option>');
            }
        }).catch(function() {
            console.log('加载父级任务失败');
            select.html('<option value="">加载失败，请重试</option>');
        });
    }

    // 打开任务编辑模态框的函数
    function openTaskUpdateModal(taskData) {
        // 先加载父级任务选项
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
