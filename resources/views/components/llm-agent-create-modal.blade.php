<!-- 创建智能体模态框 -->
<div class="modal fade" id="createAgentModal" tabindex="-1" role="dialog" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAgentModalLabel">创建智能体</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="create-agent-form">
                    <div class="form-group">
                        <label for="agent-name">名称 *</label>
                        <input type="text" class="form-control" id="agent-name" name="name" required placeholder="请输入智能体名称">
                    </div>
                    
                    <div class="form-group">
                        <label for="agent-description">描述</label>
                        <textarea class="form-control" id="agent-description" name="description" rows="3" placeholder="请输入智能体描述"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="agent-avatar">头像URL</label>
                        <input type="text" class="form-control" id="agent-avatar" name="avatar" placeholder="请输入头像图片URL">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 其他配置信息可在编辑页面中完善
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitAgentForm()">创建并编辑</button>
            </div>
        </div>
    </div>
</div>

<script>
// 提交智能体创建表单
function submitAgentForm() {
    const formData = {
        name: $('#agent-name').val(),
        description: $('#agent-description').val(),
        avatar: $('#agent-avatar').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    // 验证必填字段
    if (!formData.name) {
        alert('请填写智能体名称');
        return;
    }
    
    $.ajax({
        url: '/llm/api/llm-agents/create-draft',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#createAgentModal').modal('hide');
                
                // 成功后跳转到草稿编辑页面
                window.location.href = `/llm/agents/${response.agent.id}/draft`;
            } else {
                alert('创建失败: ' + response.message);
            }
        },
        error: function(xhr) {
            let errorMsg = '创建失败';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            alert(errorMsg);
        }
    });
}

// 显示创建模态框
function showCreateAgentModal() {
    // 清空表单
    $('#create-agent-form')[0].reset();
    
    // 显示模态框
    $('#createAgentModal').modal('show');
}
</script>