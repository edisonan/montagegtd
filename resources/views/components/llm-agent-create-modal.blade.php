<!-- 创建智能体模态框 - 按照蒙太奇设计规范优化 -->
<div class="modal hidden" id="createAgentModal" role="dialog" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-content w-full max-w-2xl">
        <!-- 模态框头部 -->
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900" id="createAgentModalLabel">创建智能体</h3>
                <p class="text-sm text-gray-500 mt-1">创建一个新的智能体助手</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeCreateAgentModal()" aria-label="关闭">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- 模态框内容 -->
        <div class="mb-6">
            <form id="create-agent-form" class="space-y-6">
                <!-- 名称输入 -->
                <div>
                    <label for="agent-name" class="block text-sm font-medium text-gray-700 mb-2">
                        名称 <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="input w-full"
                           id="agent-name"
                           name="name"
                           required
                           placeholder="例如：编程助手、写作助手、数据分析专家">
                    <p class="text-sm text-gray-500 mt-2">智能体的名称，建议简洁明了</p>
                </div>

                <!-- 描述输入 -->
                <div>
                    <label for="agent-description" class="block text-sm font-medium text-gray-700 mb-2">
                        描述
                    </label>
                    <textarea class="input w-full min-h-[100px] resize-none"
                              id="agent-description"
                              name="description"
                              rows="3"
                              placeholder="请描述智能体的功能、特性和使用场景..."></textarea>
                    <p class="text-sm text-gray-500 mt-2">详细的描述有助于其他用户了解这个智能体的用途</p>
                </div>

                <!-- 头像URL输入 -->
                <div>
                    <label for="agent-avatar" class="block text-sm font-medium text-gray-700 mb-2">
                        头像URL
                    </label>
                    <div class="flex items-center space-x-4">
                        <input type="text"
                               class="input flex-1"
                               id="agent-avatar"
                               name="avatar"
                               placeholder="https://example.com/avatar.png">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showAvatarPreview()">
                            <i class="fas fa-eye mr-2"></i>预览
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">建议尺寸：200×200像素，支持 PNG、JPG、SVG 格式</p>
                </div>

                <!-- 提示信息 -->
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-800 mb-1">温馨提示</p>
                            <p class="text-sm text-blue-700">
                                当前只需填写基本信息即可创建草稿。创建完成后，您可以在编辑页面中完善：
                                <span class="block mt-1">
                                    • 系统提示词 • 知识库配置 • 对话示例 • 高级参数
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- 模态框底部 -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
            <div class="text-sm text-gray-500">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                创建后即可开始配置智能体
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" class="btn btn-secondary" onclick="closeCreateAgentModal()">
                    <i class="fas fa-times mr-2"></i>取消
                </button>
                <button type="button" class="btn btn-primary" onclick="submitAgentForm()">
                    <i class="fas fa-plus mr-2"></i>创建并编辑
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // 显示创建模态框
    function showCreateAgentModal() {
        // 清空表单
        document.getElementById('create-agent-form').reset();

        // 显示模态框
        const modal = document.getElementById('createAgentModal');
        modal.classList.remove('hidden');
        modal.classList.add('show');

        // 设置焦点到名称输入框
        setTimeout(() => {
            document.getElementById('agent-name').focus();
        }, 100);

        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
    }

    // 关闭创建模态框
    function closeCreateAgentModal() {
        const modal = document.getElementById('createAgentModal');
        modal.classList.remove('show');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);

        // 恢复背景滚动
        document.body.style.overflow = '';
    }

    // 显示头像预览
    function showAvatarPreview() {
        const avatarUrl = document.getElementById('agent-avatar').value;

        if (!avatarUrl) {
            // 显示提示信息
            const previewDiv = document.createElement('div');
            previewDiv.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            previewDiv.innerHTML = `
            <div class="modal-content max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">头像预览</h4>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="text-center py-8">
                    <div class="w-32 h-32 bg-gray-100 rounded-full mx-auto flex items-center justify-center mb-4">
                        <i class="fas fa-user text-gray-400 text-4xl"></i>
                    </div>
                    <p class="text-gray-600">请输入有效的图片URL进行预览</p>
                </div>
            </div>
        `;
            document.body.appendChild(previewDiv);
            return;
        }

        // 验证URL格式
        const urlPattern = /^https?:\/\/.+\..+/i;
        if (!urlPattern.test(avatarUrl)) {
            showToast('请输入有效的URL地址', 'warning');
            return;
        }

        // 显示预览模态框
        const previewDiv = document.createElement('div');
        previewDiv.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
        previewDiv.innerHTML = `
        <div class="modal-content max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold text-gray-900">头像预览</h4>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-100">
                    <img src="${avatarUrl}" alt="头像预览"
                         class="w-full h-full object-cover"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><rect width=\"100\" height=\"100\" fill=\"%23f1f5f9\"/><text x=\"50\" y=\"50\" text-anchor=\"middle\" dy=\".3em\" fill=\"%2394a3b8\" font-size=\"14\">加载失败</text></svg>'">
                </div>
                <p class="text-sm text-gray-600 break-all px-4">${avatarUrl}</p>
                <div class="mt-4 flex justify-center space-x-3">
                    <button onclick="document.getElementById('agent-avatar').value=''; this.parentElement.parentElement.parentElement.parentElement.remove()"
                            class="btn btn-outline btn-sm">
                        <i class="fas fa-trash mr-2"></i>清空
                    </button>
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                            class="btn btn-primary btn-sm">
                        <i class="fas fa-check mr-2"></i>确认使用
                    </button>
                </div>
            </div>
        </div>
    `;
        document.body.appendChild(previewDiv);
    }

    // 提交智能体创建表单
    function submitAgentForm() {
        const name = document.getElementById('agent-name').value.trim();
        const description = document.getElementById('agent-description').value.trim();
        const avatar = document.getElementById('agent-avatar').value.trim();

        // 验证必填字段
        if (!name) {
            showToast('请填写智能体名称', 'warning');
            document.getElementById('agent-name').focus();
            return;
        }

        // 显示加载状态
        const submitBtn = document.querySelector('#createAgentModal .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';
        submitBtn.disabled = true;

        // 准备表单数据
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('avatar', avatar);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // 发送请求
        fetch('/llm/api/llm-agents/create-draft', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('智能体创建成功！正在跳转...', 'success');
                    closeCreateAgentModal();

                    // 延迟跳转，让用户看到成功提示
                    setTimeout(() => {
                        window.location.href = `/llm/agents/${data.agent.id}/draft`;
                    }, 1000);
                } else {
                    showToast('创建失败: ' + (data.message || '未知错误'), 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('创建智能体失败:', error);
                showToast('网络错误，请稍后重试', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    // 全局点击关闭模态框
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('createAgentModal');
        if (modal && modal.classList.contains('show') && e.target === modal) {
            closeCreateAgentModal();
        }
    });

    // ESC键关闭模态框
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateAgentModal();
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
    /* 模态框动画优化 */
    #createAgentModal {
        animation: fadeIn 0.3s ease-out;
    }

    #createAgentModal .modal-content {
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

    /* 头像预览样式优化 */
    #createAgentModal .modal-content {
        max-height: calc(100vh - 80px);
    }

    @media (max-width: 768px) {
        #createAgentModal .modal-content {
            width: 95%;
            margin: 20px auto;
            max-height: calc(100vh - 40px);
        }
    }

    /* 表单输入框优化 */
    #createAgentModal input:focus,
    #createAgentModal textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* 按钮状态优化 */
    #createAgentModal .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>