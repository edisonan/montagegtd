<!-- 课程章节弹窗 - 按照蒙太奇设计规范优化 -->
<div class="modal hidden" id="courseItemModal" role="dialog" aria-labelledby="courseItemModalLabel" aria-hidden="true">
    <div class="modal-content w-full max-w-3xl">
        <!-- 模态框头部 -->
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900" id="courseItemModalLabel">添加章节</h3>
                <p class="text-sm text-gray-500 mt-1">为课程添加新的学习内容</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeCourseItemModal()" aria-label="关闭">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- 错误提示区域 -->
        <div id="courseItemErrors" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 hidden">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800 mb-2" id="courseItemErrorTitle">发现以下错误</p>
                    <ul id="courseItemErrorList" class="text-sm text-red-700 space-y-1"></ul>
                </div>
                <button type="button" class="text-red-400 hover:text-red-600" onclick="hideCourseItemErrors()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- 模态框内容 -->
        <div class="mb-6">
            <form id="courseItemFormModal" class="space-y-6">
                <!-- CSRF令牌和隐藏字段 -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="course_id_modal" name="course_id" value="">
                <input type="hidden" id="item_id_modal" name="item_id" value="">

                <!-- 基本信息行 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 父级章节选择 -->
                    <div>
                        <label for="parent_id_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sitemap text-gray-400 mr-2"></i>父级章节
                        </label>
                        <div class="relative">
                            <select class="input w-full pr-10" id="parent_id_modal" name="parent_id">
                                <option value="">无父级（顶级章节）</option>
                                <!-- 选项将通过JavaScript动态加载 -->
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">选择此章节所属的父级模块或章节</p>
                    </div>

                    <!-- 章节类型选择 -->
                    <div>
                        <label for="item_type_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag text-gray-400 mr-2"></i>章节类型 <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select class="input w-full pr-10" id="item_type_modal" name="item_type" required>
                                <option value="">请选择章节类型</option>
                                <option value="module">📦 模块</option>
                                <option value="chapter">📖 章节</option>
                                <option value="video">🎬 视频</option>
                                <option value="assignment">📝 作业</option>
                                <option value="quiz">🧪 测验</option>
                                <option value="reading">📚 阅读材料</option>
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div id="itemTypeDescription" class="text-sm text-gray-500 mt-2 space-y-1 hidden">
                            <div data-type="module"><span class="font-medium">模块：</span>组织章节的容器</div>
                            <div data-type="chapter"><span class="font-medium">章节：</span>包含具体学习内容</div>
                            <div data-type="video"><span class="font-medium">视频：</span>视频学习材料</div>
                            <div data-type="assignment"><span class="font-medium">作业：</span>需要完成的练习</div>
                            <div data-type="quiz"><span class="font-medium">测验：</span>测试理解程度</div>
                            <div data-type="reading"><span class="font-medium">阅读材料：</span>阅读资料</div>
                        </div>
                    </div>
                </div>

                <!-- 章节标题 -->
                <div>
                    <label for="title_modal" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading text-gray-400 mr-2"></i>章节标题 <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="input w-full"
                           id="title_modal"
                           name="title"
                           required
                           placeholder="例如：第一章：课程介绍">
                    <p class="text-sm text-gray-500 mt-2">清晰明确的标题有助于学员理解内容</p>
                </div>

                <!-- 时长和排序 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="duration_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-gray-400 mr-2"></i>预计时长（分钟）
                        </label>
                        <div class="relative">
                            <input type="number"
                                   class="input w-full"
                                   id="duration_modal"
                                   name="duration"
                                   min="0"
                                   placeholder="0">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                                <span class="text-sm">分钟</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">学员完成此章节的预计时间</p>
                    </div>

                    <div>
                        <label for="order_index_modal" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i>排序序号
                        </label>
                        <input type="number"
                               class="input w-full"
                               id="order_index_modal"
                               name="order_index"
                               value="0"
                               min="0">
                        <p class="text-sm text-gray-500 mt-2">数字越小越靠前，相同数字按创建时间排序</p>
                    </div>
                </div>

                <!-- 外部链接 -->
                <div>
                    <label for="external_url_modal" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-link text-gray-400 mr-2"></i>外部链接
                    </label>
                    <input type="url"
                           class="input w-full"
                           id="external_url_modal"
                           name="external_url"
                           placeholder="https://example.com/learning-material">
                    <p class="text-sm text-gray-500 mt-2">视频、文档或其他资源的链接</p>
                </div>

                <!-- 章节描述 -->
                <div>
                    <label for="description_modal" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left text-gray-400 mr-2"></i>章节描述
                    </label>
                    <textarea class="input w-full min-h-[100px] resize-none"
                              id="description_modal"
                              name="description"
                              rows="3"
                              placeholder="简要描述此章节的内容、学习目标和要求..."></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-sm text-gray-500">详细的描述能帮助学员了解学习重点</p>
                        <span class="text-xs text-gray-500" id="description-counter">0/500</span>
                    </div>
                </div>
            </form>
        </div>

        <!-- 模态框底部 -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
            <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                <span id="formModeIndicator">添加新章节到课程</span>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" class="btn btn-secondary" onclick="closeCourseItemModal()">
                    <i class="fas fa-times mr-2"></i>取消
                </button>
                <button type="button" class="btn btn-primary" id="submitCourseItemBtn" onclick="submitCourseItemForm()">
                    <i class="fas fa-save mr-2"></i>保存章节
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    // 全局变量
    let currentCourseId = null;
    let currentExcludeItemId = null;

    // 打开课程章节弹窗
    function openCourseItemModal(courseId, itemData = null) {
        currentCourseId = courseId;

        // 清除错误信息
        hideCourseItemErrors();

        // 设置课程ID
        document.getElementById('course_id_modal').value = courseId;

        // 显示模态框
        const modal = document.getElementById('courseItemModal');
        modal.classList.remove('hidden');
        modal.classList.add('show');

        // 阻止背景滚动
        document.body.style.overflow = 'hidden';

        if (itemData) {
            // 编辑模式
            document.getElementById('courseItemModalLabel').textContent = '编辑章节';
            document.getElementById('formModeIndicator').textContent = '正在编辑章节';

            const itemId = itemData.id || '';
            document.getElementById('item_id_modal').value = itemId;
            document.getElementById('title_modal').value = itemData.title || '';
            document.getElementById('item_type_modal').value = itemData.item_type || 'chapter';
            document.getElementById('duration_modal').value = itemData.duration || 0;
            document.getElementById('external_url_modal').value = itemData.external_url || '';
            document.getElementById('description_modal').value = itemData.description || '';
            document.getElementById('order_index_modal').value = itemData.order_index || 0;

            currentExcludeItemId = itemId;
            loadCourseStructure(courseId, itemId, itemData.parent_id || '');

            // 设置提交按钮文本
            document.getElementById('submitCourseItemBtn').innerHTML = '<i class="fas fa-save mr-2"></i>更新章节';
        } else {
            // 添加模式
            document.getElementById('courseItemModalLabel').textContent = '添加章节';
            document.getElementById('formModeIndicator').textContent = '添加新章节到课程';

            // 重置表单
            document.getElementById('courseItemFormModal').reset();
            document.getElementById('item_id_modal').value = '';
            document.getElementById('item_type_modal').value = 'chapter';
            document.getElementById('order_index_modal').value = 0;
            document.getElementById('duration_modal').value = 0;

            currentExcludeItemId = null;
            loadCourseStructure(courseId);

            // 设置提交按钮文本
            document.getElementById('submitCourseItemBtn').innerHTML = '<i class="fas fa-plus mr-2"></i>添加章节';
        }

        // 初始化字数统计
        updateDescriptionCounter();

        // 设置焦点到标题输入框
        setTimeout(() => {
            document.getElementById('title_modal').focus();
        }, 100);
    }

    // 关闭弹窗
    function closeCourseItemModal() {
        const modal = document.getElementById('courseItemModal');
        modal.classList.remove('show');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);

        // 恢复背景滚动
        document.body.style.overflow = '';
    }

    // 隐藏错误信息
    function hideCourseItemErrors() {
        const errorDiv = document.getElementById('courseItemErrors');
        errorDiv.classList.add('hidden');
        document.getElementById('courseItemErrorList').innerHTML = '';
    }

    // 显示错误信息
    function showCourseItemErrors(errors) {
        const errorDiv = document.getElementById('courseItemErrors');
        const errorList = document.getElementById('courseItemErrorList');

        errorList.innerHTML = '';

        if (Array.isArray(errors)) {
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
        } else if (typeof errors === 'object') {
            Object.values(errors).forEach(errorArray => {
                errorArray.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
            });
        } else if (typeof errors === 'string') {
            const li = document.createElement('li');
            li.textContent = errors;
            errorList.appendChild(li);
        }

        errorDiv.classList.remove('hidden');

        // 滚动到错误信息区域
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // 加载章节结构
    function loadCourseStructure(courseId, excludeItemId = null, currentParentId = null) {
        const selectElement = document.getElementById('parent_id_modal');
        const originalValue = selectElement.value;

        // 显示加载状态
        selectElement.innerHTML = '<option value="">加载中...</option>';
        selectElement.disabled = true;

        if (!apiRequest) {
            selectElement.innerHTML = '<option value="">API客户端未初始化</option>';
            return;
        }

        apiRequest('GET', '/course-items/structure/' + courseId, {}).then(data => {
                if (data.code === 9999) {
                    selectElement.innerHTML = '';
                    selectElement.appendChild(createOptionElement('', '无父级（顶级章节）', null));

                    // 递归构建选项
                    function buildOptions(items, level = 0) {
                        const prefix = level > 0 ? '│ '.repeat(level - 1) + '├─ ' : '';

                        items.forEach(item => {
                            // 排除当前项及其子项
                            if (excludeItemId && item.id == excludeItemId) {
                                return;
                            }

                            selectElement.appendChild(createOptionElement(
                                item.id,
                                prefix + getItemTypeIcon(item.item_type) + ' ' + item.title,
                                item.item_type
                            ));

                            if (item.children && item.children.length > 0) {
                                buildOptions(item.children, level + 1);
                            }
                        });
                    }

                    buildOptions(data.result || []);

                    // 恢复之前的值或设置默认值
                    if (currentParentId) {
                        selectElement.value = currentParentId;
                    } else if (originalValue) {
                        selectElement.value = originalValue;
                    }

                    selectElement.disabled = false;
                } else {
                    throw new Error(data.msg || '加载失败');
                }
            }).catch(error => {
                console.error('加载章节结构失败:', error);
                selectElement.innerHTML = '<option value="">加载失败，请刷新重试</option>';
                setTimeout(() => {
                    selectElement.innerHTML = '<option value="">无父级（顶级章节）</option>';
                    selectElement.disabled = false;
                }, 2000);
            });
    }

    // 创建选项元素
    function createOptionElement(value, text, itemType) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;

        if (itemType) {
            option.dataset.type = itemType;
            option.classList.add(`type-${itemType}`);
        }

        return option;
    }

    // 获取章节类型图标
    function getItemTypeIcon(itemType) {
        const icons = {
            'module': '📦',
            'chapter': '📖',
            'video': '🎬',
            'assignment': '📝',
            'quiz': '🧪',
            'reading': '📚'
        };
        return icons[itemType] || '📄';
    }

    // 章节类型变化时显示描述
    document.getElementById('item_type_modal').addEventListener('change', function() {
        const selectedType = this.value;
        const descriptionDiv = document.getElementById('itemTypeDescription');
        const allDescriptions = descriptionDiv.querySelectorAll('[data-type]');

        allDescriptions.forEach(desc => {
            desc.classList.add('hidden');
        });

        if (selectedType) {
            const selectedDesc = descriptionDiv.querySelector(`[data-type="${selectedType}"]`);
            if (selectedDesc) {
                selectedDesc.classList.remove('hidden');
            }
            descriptionDiv.classList.remove('hidden');
        } else {
            descriptionDiv.classList.add('hidden');
        }
    });

    // 字数统计
    function updateDescriptionCounter() {
        const textarea = document.getElementById('description_modal');
        const counter = document.getElementById('description-counter');
        const length = textarea.value.length;
        const maxLength = 500;

        counter.textContent = `${length}/${maxLength}`;

        if (length > maxLength * 0.9) {
            counter.classList.add('text-warning-color');
        } else {
            counter.classList.remove('text-warning-color');
        }
    }

    // 监听描述输入
    document.getElementById('description_modal').addEventListener('input', updateDescriptionCounter);

    // 提交课程章节表单
    async function submitCourseItemForm() {
        const form = document.getElementById('courseItemFormModal');
        const submitBtn = document.getElementById('submitCourseItemBtn');
        const itemId = document.getElementById('item_id_modal').value;

        // 验证必填字段
        const title = document.getElementById('title_modal').value.trim();
        const itemType = document.getElementById('item_type_modal').value;

        if (!title) {
            showCourseItemErrors(['请填写章节标题']);
            document.getElementById('title_modal').focus();
            return;
        }

        if (!itemType) {
            showCourseItemErrors(['请选择章节类型']);
            document.getElementById('item_type_modal').focus();
            return;
        }

        // 验证外部链接格式
        const externalUrl = document.getElementById('external_url_modal').value.trim();
        if (externalUrl && !isValidUrl(externalUrl)) {
            showCourseItemErrors(['请输入有效的外部链接URL']);
            document.getElementById('external_url_modal').focus();
            return;
        }

        // 显示加载状态
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>处理中...';
        submitBtn.disabled = true;

        // 准备表单数据
        if (!apiRequest) {
            showCourseItemErrors(['API客户端未初始化']);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }

        const payload = {
            course_id: document.getElementById('course_id_modal').value,
            parent_id: document.getElementById('parent_id_modal').value || null,
            title: document.getElementById('title_modal').value,
            item_type: document.getElementById('item_type_modal').value,
            duration: document.getElementById('duration_modal').value,
            external_url: document.getElementById('external_url_modal').value,
            description: document.getElementById('description_modal').value,
            order_index: document.getElementById('order_index_modal').value
        };

        try {
            const data = await apiRequest(itemId ? 'PUT' : 'POST', itemId ? ('/course-items/' + itemId) : '/course-items', payload);

            if (data.code === 9999) {
                // 成功
                showToast(data.msg || '操作成功', 'success');
                closeCourseItemModal();

                // 延迟刷新页面
                setTimeout(() => {
                    if (window.refreshCourseStructure) {
                        window.refreshCourseStructure();
                    } else {
                        location.reload();
                    }
                }, 800);
            } else {
                // 失败
                showCourseItemErrors(data.msg || '操作失败');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('提交表单失败:', error);
            showCourseItemErrors(['网络错误，请稍后重试']);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    // 验证URL格式
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // 全局点击关闭模态框
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('courseItemModal');
        if (modal && modal.classList.contains('show') && e.target === modal) {
            closeCourseItemModal();
        }
    });

    // ESC键关闭模态框
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCourseItemModal();
        }
    });

    // 显示Toast提示
    function showToast(message, type = 'info') {
        // 如果已存在Toast，先移除
        const existingToast = document.getElementById('global-toast');
        if (existingToast) {
            existingToast.remove();
        }

        const typeConfig = {
            success: { icon: 'fa-check-circle', bgColor: 'bg-green-100', textColor: 'text-green-800', borderColor: 'border-green-200' },
            error: { icon: 'fa-exclamation-circle', bgColor: 'bg-red-100', textColor: 'text-red-800', borderColor: 'border-red-200' },
            warning: { icon: 'fa-exclamation-triangle', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', borderColor: 'border-yellow-200' },
            info: { icon: 'fa-info-circle', bgColor: 'bg-blue-100', textColor: 'text-blue-800', borderColor: 'border-blue-200' }
        };

        const config = typeConfig[type] || typeConfig.info;

        const toast = document.createElement('div');
        toast.id = 'global-toast';
        toast.className = `fixed top-6 right-6 ${config.bgColor} ${config.textColor} ${config.borderColor} border rounded-lg px-4 py-3 shadow-lg z-[10000] flex items-center space-x-3 max-w-sm fade-in`;
        toast.innerHTML = `
        <i class="fas ${config.icon} text-lg"></i>
        <span>${message}</span>
    `;

        document.body.appendChild(toast);

        // 3秒后自动移除
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 3000);
    }
</script>

<style>
    /* 模态框动画 */
    #courseItemModal {
        animation: fadeIn 0.3s ease-out;
    }

    #courseItemModal .modal-content {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* 章节类型选项样式 */
    #item_type_modal option {
        padding: 8px 12px;
    }

    /* 父级章节选择器样式 */
    #parent_id_modal option {
        padding: 6px 12px;
        white-space: nowrap;
    }

    #parent_id_modal option.type-module {
        font-weight: 600;
        color: #4a90e2;
    }

    #parent_id_modal option.type-chapter {
        color: #475569;
    }

    /* 表单输入框焦点效果 */
    #courseItemModal input:focus,
    #courseItemModal select:focus,
    #courseItemModal textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* 按钮状态 */
    #submitCourseItemBtn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* 章节类型描述动画 */
    #itemTypeDescription div {
        transition: all 0.2s ease;
    }

    #itemTypeDescription div.hidden {
        display: none;
    }

    .text-warning-color {
        color: var(--warning-color);
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        #courseItemModal .modal-content {
            width: 95%;
            margin: 20px auto;
            max-height: 85vh;
            overflow-y: auto;
        }

        .grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }

    /* 自定义滚动条 */
    #courseItemModal .modal-content {
        scrollbar-width: thin;
        scrollbar-color: var(--gray-300) var(--gray-100);
    }

    #courseItemModal .modal-content::-webkit-scrollbar {
        width: 6px;
    }

    #courseItemModal .modal-content::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    #courseItemModal .modal-content::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }
</style>
