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
                    <h3 id="taskUpdateModalTitle" class="text-lg sm:text-xl font-semibold text-gray-900">修改待办</h3>
                    <button type="button" class="close-btn p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            onclick="hideTaskUpdateModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- AI 解析批量分页条（仅「新建待办」AI 流程显示） -->
                <div id="taskAiParsePager" class="hidden flex items-center justify-between px-4 sm:px-6 py-2 bg-indigo-50 border-b border-indigo-100">
                    <div class="flex items-center gap-2 text-sm text-indigo-700">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        <span id="taskAiParsePagerInfo"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="taskAiPagerPrev"
                                class="px-2 py-1 rounded text-xs bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                title="上一条">
                            <i class="fas fa-chevron-left mr-1"></i>上一条
                        </button>
                        <button type="button" id="taskAiPagerNext"
                                class="px-2 py-1 rounded text-xs bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                title="下一条">
                            下一条<i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
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

                        <!-- 备注（仅编辑时显示） -->
                        <div id="review_note_field" class="hidden grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 items-start">
                            <label for="review_note_input" class="font-medium text-gray-700 pt-2">备注</label>
                            <div class="md:col-span-3">
                                <textarea name="review_note" id="review_note_input" rows="3"
                                          class="input w-full text-sm sm:text-base"
                                          maxlength="2000"
                                          placeholder="请输入备注"></textarea>
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

    // 任务表单提交函数（兼容新建与编辑：id 为空走 POST 创建，否则 PUT 更新）
    function submitTaskUpdateForm() {
        var form = document.getElementById('taskUpdateForm');
        var formData = new FormData(form);
        var taskId = $('#task_id_input').val();
        var isCreate = !taskId;
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
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>' + (isCreate ? '创建中...' : '保存中...'));

        var requestPromise = isCreate
            ? apiRequest('POST', '/tasks', payload)
            : apiRequest('PUT', '/tasks/' + taskId, payload);

        requestPromise.then(function(response) {
            if(response.code == 9999) {
                if (isCreate && window.__aiTaskParseItems && window.__aiTaskParseItems.length > 0) {
                    // AI 批量创建：创建完当前条自动进入下一条，全部创建完再关闭弹窗
                    if (window.__aiTaskParseIndex < window.__aiTaskParseItems.length - 1) {
                        window.__aiTaskParseIndex++;
                        renderAiPager();
                        submitBtn.prop('disabled', false);
                        submitBtn.html('<i class="fas fa-save mr-1 sm:mr-2"></i>保存并继续');
                        if (typeof window.afterTaskCreate === 'function') {
                            window.afterTaskCreate();
                        }
                        if (typeof window.showNotification === 'function') {
                            window.showNotification('success', '待办已创建，继续编辑下一条');
                        }
                        return;
                    }
                    window.__aiTaskParseItems = null;
                    window.__aiTaskParseIndex = 0;
                    hideTaskUpdateModal();
                    if (typeof window.afterTaskCreate === 'function') {
                        window.afterTaskCreate();
                    } else {
                        location.reload();
                    }
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('success', '全部待办已创建');
                    }
                    return;
                }

                hideTaskUpdateModal();
                // 优先走页面提供的 AJAX 刷新钩子，避免整页刷新；未提供时回退为刷新页面
                if (typeof window.afterTaskCreate === 'function') {
                    window.afterTaskCreate();
                } else if (typeof window.afterTaskUpdate === 'function') {
                    window.afterTaskUpdate();
                } else {
                    location.reload();
                }
            } else {
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="fas fa-save mr-1 sm:mr-2"></i>' + (isCreate ? '创建待办' : '保存修改'));
                $('#taskUpdateErrorList').empty();
                if(response.msg) {
                    $('#taskUpdateErrorList').append('<li>' + response.msg + '</li>');
                } else {
                    $('#taskUpdateErrorList').append('<li>' + (isCreate ? '创建失败' : '更新失败') + '</li>');
                }
                $('#taskUpdateErrors').removeClass('hidden');

                const errorElement = document.getElementById('taskUpdateErrors');
                if (errorElement) {
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }).catch(function(xhr) {
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-save mr-1 sm:mr-2"></i>' + (isCreate ? '创建待办' : '保存修改'));

            $('#taskUpdateErrorList').empty();
            if(xhr && xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function(key, value) {
                    $('#taskUpdateErrorList').append('<li>' + value[0] + '</li>');
                });
            } else {
                $('#taskUpdateErrorList').append('<li>' + (isCreate ? '创建失败，请稍后重试' : '更新失败，请稍后重试') + '</li>');
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
        resetTaskModalState('修改待办');
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

        // 备注（仅编辑时显示）
        $('#review_note_input').val(taskData.review_note || '');
        $('#review_note_field').removeClass('hidden');

        // 清除错误信息
        $('#taskUpdateErrors').addClass('hidden');
        $('#taskUpdateErrorList').empty();

        // 恢复提交按钮文案
        $('#taskUpdateModal .btn-primary').html('<i class="fas fa-save mr-1 sm:mr-2"></i>保存修改');

        // 显示模态框
        showTaskUpdateModal();
    }

    // 重置新建/编辑弹窗的公共状态（标题、AI 分页条、隐藏任务 id）
    function resetTaskModalState(title) {
        window.__aiTaskParseItems = null;
        window.__aiTaskParseIndex = 0;

        var pager = document.getElementById('taskAiParsePager');
        if (pager) {
            pager.classList.add('hidden');
        }
        var titleEl = document.getElementById('taskUpdateModalTitle');
        if (titleEl) {
            titleEl.textContent = title || '新建待办';
        }
        $('#task_id_input').val('');
        $('#review_note_field').addClass('hidden');
        $('#review_note_input').val('');
        $('#taskUpdateErrors').addClass('hidden');
        $('#taskUpdateErrorList').empty();
        // 保存成功后按钮会保持 disabled，重新打开弹窗时必须恢复可点击
        $('#taskUpdateModal .btn-primary').prop('disabled', false);
    }

    // 填充待办表单数据（新建弹窗用）
    function fillTaskFormData(data) {
        data = data || {};
        $('#task_name_input').val(data.name || '');
        $('#parent_task_id_input').val('');
        $('input[name="priority"][value="' + (data.priority || 1) + '"]').prop('checked', true);
        $('input[name="status"][value="1"]').prop('checked', true);
        $('input[name="is_top"][value="' + (data.is_top || 0) + '"]').prop('checked', true);
        $('input[name="mode"][value="' + (data.mode || 1) + '"]').prop('checked', true);
        var remindtime = (data.remindtime && data.remindtime !== 'null') ? data.remindtime : '';
        var deadline = (data.deadline && data.deadline !== 'null') ? data.deadline : '';
        $('#remindtime_input').val(remindtime);
        $('#deadline_input').val(deadline);
    }

    // 渲染 AI 批量解析分页状态并回填当前条目的表单数据
    function renderAiPager() {
        var items = window.__aiTaskParseItems || [];
        var index = Math.min(Math.max(Number(window.__aiTaskParseIndex || 0), 0), Math.max(items.length - 1, 0));

        var info = document.getElementById('taskAiParsePagerInfo');
        if (info) {
            info.textContent = 'AI 解析到 ' + items.length + ' 条待办 · 当前第 ' + (index + 1) + ' / ' + items.length + ' 条';
        }

        var prevBtn = document.getElementById('taskAiPagerPrev');
        var nextBtn = document.getElementById('taskAiPagerNext');
        if (prevBtn) {
            var prevDisabled = index <= 0;
            prevBtn.disabled = prevDisabled;
            prevBtn.classList.toggle('opacity-40', prevDisabled);
            prevBtn.classList.toggle('cursor-not-allowed', prevDisabled);
        }
        if (nextBtn) {
            var nextDisabled = index >= items.length - 1;
            nextBtn.disabled = nextDisabled;
            nextBtn.classList.toggle('opacity-40', nextDisabled);
            nextBtn.classList.toggle('cursor-not-allowed', nextDisabled);
        }

        window.__aiTaskParseIndex = index;
        fillTaskFormData(items[index] || {});
    }

    // 打开「新建待办」弹窗：data 为直接回填的数据；items 为 AI 批量解析结果（支持分页逐条编辑创建）
    function openTaskCreateModal(config) {
        config = config || {};
        var items = config.items || [];
        var isAiFlow = Array.isArray(items) && items.length > 0;

        resetTaskModalState(isAiFlow ? '新建待办（AI 解析）' : '新建待办');

        loadParentTasks(null, null);

        if (isAiFlow) {
            window.__aiTaskParseItems = items;
            window.__aiTaskParseIndex = Math.min(Math.max(Number(config.index || 0), 0), items.length - 1);
            renderAiPager();
            var pager = document.getElementById('taskAiParsePager');
            if (pager) {
                pager.classList.remove('hidden');
            }
        } else {
            fillTaskFormData(config.data || {});
        }

        $('#taskUpdateModal .btn-primary').html('<i class="fas fa-plus mr-1 sm:mr-2"></i>' + (isAiFlow ? (window.__aiTaskParseIndex >= items.length - 1 ? '创建待办' : '保存并继续') : '创建待办'));

        showTaskUpdateModal();
    }

    // 上一条 / 下一条切换（供分页条按钮调用）
    window.taskAiParsePagerPrev = function() {
        if (!window.__aiTaskParseItems || window.__aiTaskParseItems.length === 0 || window.__aiTaskParseIndex <= 0) {
            return;
        }
        window.__aiTaskParseIndex--;
        renderAiPager();
    };
    window.taskAiParsePagerNext = function() {
        if (!window.__aiTaskParseItems || window.__aiTaskParseIndex >= window.__aiTaskParseItems.length - 1) {
            return;
        }
        window.__aiTaskParseIndex++;
        renderAiPager();
    };

    // 初始化事件监听
    $(document).ready(function() {
        // 点击模态框外部关闭
        $('#taskUpdateModal').on('click', function(e) {
            if (e.target === this) {
                hideTaskUpdateModal();
            }
        });

        // AI 分页条按钮
        $('#taskAiPagerPrev').on('click', function() {
            window.taskAiParsePagerPrev();
        });
        $('#taskAiPagerNext').on('click', function() {
            window.taskAiParsePagerNext();
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
