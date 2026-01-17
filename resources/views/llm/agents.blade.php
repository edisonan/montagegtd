@extends('layouts.app')

@section('content')
<style>
    .agent-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .agent-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .agent-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1.5rem;
    }
    
    .agent-body {
        padding: 1.5rem;
    }
    
    .agent-status-active {
        background-color: #d4edda;
        color: #155724;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .agent-status-inactive {
        background-color: #f8d7da;
        color: #721c24;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .agent-private-badge {
        background-color: #fff3cd;
        color: #856404;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .stats-box {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .stats-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
    }
    
    .stats-label {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .btn-create-agent {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
    }
    
    .btn-create-agent:hover {
        background: linear-gradient(135deg, #5a6fd8, #6a4190);
        color: white;
        transform: translateY(-2px);
    }
    
    .form-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 0.5rem;
    }
    
    .agent-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
    }
    
    .agent-list-container {
        max-height: 600px;
        overflow-y: auto;
        padding-right: 10px;
    }
    
    .agent-list-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .agent-list-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .agent-list-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .agent-list-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .agent-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }
    
    .agent-item {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .agent-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .agent-item-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1rem;
        display: flex;
        align-items: center;
    }
    
    .agent-initial-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        border: 2px solid white;
    }
    
    .agent-item-body {
        padding: 1rem;
    }
    
    .agent-item-actions {
        padding: 0 1rem 1rem;
        text-align: right;
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .collapsed .collapse-icon {
        transform: rotate(-90deg);
    }
</style>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa fa-brain text-primary"></i> LLM 智能体管理</h2>
                <button class="btn btn-create-agent" onclick="openAgentModal()">
                    <i class="fa fa-plus"></i> 新建智能体
                </button>
            </div>
            
            <!-- 统计信息 -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#statsSection" aria-expanded="false" aria-controls="statsSection">
                    <h5 class="mb-0"><i class="fa fa-chart-bar"></i> 统计信息</h5>
                    <i class="fa fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="statsSection">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-box">
                                    <div class="stats-number" id="total-agents">0</div>
                                    <div class="stats-label">我的智能体</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-box">
                                    <div class="stats-number" id="active-agents">0</div>
                                    <div class="stats-label">活跃智能体</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-box">
                                    <div class="stats-number" id="total-usage">0</div>
                                    <div class="stats-label">总使用次数</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 智能体列表 -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-list"></i> 我的智能体</h5>
                </div>
                <div class="card-body p-0">
                    <div class="agent-grid" id="agent-list">
                        <!-- 智能体会在这里动态加载 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 智能体模态框 -->
<div class="modal fade" id="agentModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h5 class="modal-title" id="agentModalTitle">添加智能体</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="agentForm">
                <input type="hidden" id="agent_id" name="agent_id">
                <div class="modal-body">
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-user-circle"></i> 基本信息</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="agent_name">智能体名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="agent_name" name="name" required placeholder="输入智能体名称">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="agent_description">描述</label>
                            <textarea class="form-control" id="agent_description" name="description" rows="3" placeholder="简要描述智能体的功能和用途"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="agent_avatar">头像URL</label>
                            <input type="text" class="form-control" id="agent_avatar" name="avatar" placeholder="输入头像图片URL">
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-cogs"></i> 模型配置</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agent_model_id">默认模型 <span class="text-danger">*</span></label>
                                    <select class="form-control" id="agent_model_id" name="model_id" required>
                                        <!-- 选项将通过AJAX加载 -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agent_context_length">上下文长度</label>
                                    <input type="number" class="form-control" id="agent_context_length" name="context_length" value="4000" placeholder="输入上下文长度">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-sliders-h"></i> 参数配置</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agent_temperature">温度参数 (0-2)</label>
                                    <input type="range" class="form-control-range" id="agent_temperature" name="temperature" min="0" max="2" step="0.01" value="0.7">
                                    <div class="mt-1">
                                        <span class="badge badge-secondary">값: <span id="temp-value">0.7</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agent_top_p">Top P参数 (0-1)</label>
                                    <input type="range" class="form-control-range" id="agent_top_p" name="top_p" min="0" max="1" step="0.01" value="0.9">
                                    <div class="mt-1">
                                        <span class="badge badge-secondary">값: <span id="top-p-value">0.9</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agent_max_tokens">最大输出Tokens</label>
                                    <input type="number" class="form-control" id="agent_max_tokens" name="max_tokens" placeholder="输入最大输出Tokens数量">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-file-alt"></i> 系统提示词</h6>
                        <div class="form-group">
                            <label for="agent_system_prompt">系统提示词 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="agent_system_prompt" name="system_prompt" rows="6" required placeholder="输入智能体的系统提示词或角色设定"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-tools"></i> 工具配置</h6>
                        <div class="form-group">
                            <label for="agent_tools_config">工具配置 (JSON格式)</label>
                            <textarea class="form-control" id="agent_tools_config" name="tools_config" rows="4" placeholder="输入JSON格式的工具配置，如：{\"web_search\": true, \"calculator\": true}"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="section-title"><i class="fa fa-toggle-on"></i> 状态设置</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="agent_is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="agent_is_active">启用智能体</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存智能体</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 设置CSRF Token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// 页面加载完成后初始化数据
$(document).ready(function() {
    loadAgents();
    loadModels();
    
    // 绑定滑块值显示
    $('#agent_temperature').on('input', function() {
        $('#temp-value').text($(this).val());
    });
    
    $('#agent_top_p').on('input', function() {
        $('#top-p-value').text($(this).val());
    });
});

// 加载智能体数据
function loadAgents() {
    $.get('/llm/api/agents', function(data) {
        const container = $('#agent-list');
        container.empty();
        
        let totalUsage = 0;
        let activeAgents = 0;
        
        data.result.agents.forEach(function(agent) {
            totalUsage += agent.usage_count;
            if (agent.is_active) activeAgents++;
            
            // 如果avatar为空，则显示名称首字母
            let avatarElement = '';
            if(agent.avatar) {
                avatarElement = `<img src="${agent.avatar}" alt="Avatar" class="agent-avatar" onerror="this.src='/images/default-avatar.png'">`;
            } else {
                const initial = agent.name.charAt(0).toUpperCase();
                avatarElement = `<div class="agent-initial-avatar">${initial}</div>`;
            }
            
            const cardHtml = `
                <div class="agent-item">
                    <div class="agent-item-header d-flex align-items-center">
                        ${avatarElement}
                        <div class="ml-3 flex-grow-1">
                            <h5 class="mb-0 text-white">${agent.name}</h5>
                            <small>${agent.description || '暂无描述'}</small>
                        </div>
                        <div>
                            <span>${agent.is_active ? '<span class="agent-status-active">启用</span>' : '<span class="agent-status-inactive">禁用</span>'}</span>
                        </div>
                    </div>
                    <div class="agent-item-body">
                        <div class="mb-2">
                            <strong>模型:</strong> ${agent.model ? agent.model.name : '未知'}
                        </div>
                        <div class="mb-2">
                            <strong>内置标识:</strong> ${agent.builtin_slug || '—'}
                        </div>
                        <div class="mb-2">
                            <strong>使用:</strong> ${agent.usage_count}
                        </div>
                        <div class="mb-2">
                            <strong>收藏:</strong> ${agent.favorite_count}
                        </div>
                        <div class="mb-2">
                            <strong>创建:</strong> ${new Date(agent.created_at).toLocaleDateString()}
                        </div>
                    </div>
                    <div class="agent-item-actions">
                        <button class="btn btn-sm btn-outline-primary mr-2" onclick="editAgent(${agent.id})">
                            <i class="fa fa-edit"></i> 编辑
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAgent(${agent.id})">
                            <i class="fa fa-trash"></i> 删除
                        </button>
                    </div>
                </div>
            `;
            container.append(cardHtml);
        });
        
        // 更新统计数据
        $('#total-agents').text(data.result.agents.length);
        $('#active-agents').text(activeAgents);
        $('#total-usage').text(totalUsage);
    }).fail(function(xhr) {
        console.error('加载智能体数据失败:', xhr);
        alert('加载智能体数据失败: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
    });
}

// 加载模型选项
function loadModels() {
    $.get('/llm/api/models', function(data) {
        const modelSelect = $('#agent_model_id');
        modelSelect.empty();
        
        data.result.models.forEach(function(model) {
            // 只显示用户有权使用的模型
            modelSelect.append(`<option value="${model.id}">${model.name}</option>`);
        });
    }).fail(function(xhr) {
        console.error('加载模型数据失败:', xhr);
    });
}

// 打开智能体模态框
function openAgentModal() {
    $('#agentForm')[0].reset();
    $('#agent_id').val('');
    $('#agentModalTitle').text('添加智能体');
    $('#agent_temperature').val('0.7');
    $('#temp-value').text('0.7');
    $('#agent_top_p').val('0.9');
    $('#top-p-value').text('0.9');
    $('#agent_is_active').prop('checked', true);
    
    $('#agentModal').modal('show');
}

// 编辑智能体
function editAgent(id) {
    $.get(`/llm/api/agents/${id}`, function(data) {
        const agent = data.result;
        $('#agent_id').val(agent.id);
        $('#agent_name').val(agent.name);
        $('#agent_description').val(agent.description);
        $('#agent_avatar').val(agent.avatar);
        $('#agent_model_id').val(agent.model_id);
        $('#agent_system_prompt').val(agent.system_prompt);
        $('#agent_temperature').val(agent.temperature || '0.7');
        $('#temp-value').text(agent.temperature || '0.7');
        $('#agent_top_p').val(agent.top_p || '0.9');
        $('#top-p-value').text(agent.top_p || '0.9');
        $('#agent_max_tokens').val(agent.max_tokens);
        $('#agent_context_length').val(agent.context_length);
        $('#agent_tools_config').val(agent.tools_config ? JSON.stringify(agent.tools_config, null, 2) : '');
        $('#agent_is_active').prop('checked', agent.is_active);
        
        $('#agentModalTitle').text('编辑智能体');
        $('#agentModal').modal('show');
    }).fail(function(xhr) {
        console.error('加载智能体数据失败:', xhr);
        alert('加载智能体数据失败: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
    });
}

// 删除智能体
function deleteAgent(id) {
    if (confirm('确定要删除这个智能体吗？此操作不可恢复！')) {
        $.ajax({
            url: `/llm/api/agents/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadAgents();
            },
            error: function(xhr) {
                alert('删除失败: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
            }
        });
    }
}

// 提交智能体表单
$('#agentForm').submit(function(e) {
    e.preventDefault();
    
    // 从API获取当前用户ID
    const userId = {{ auth()->id() }};
    
    const id = $('#agent_id').val();
    const url = id ? `/llm/api/agents/${id}` : '/llm/api/agents';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: method,
        user_id: userId, // 设置为当前用户ID
        name: $('#agent_name').val(),
        description: $('#agent_description').val(),
        avatar: $('#agent_avatar').val(),
        model_id: $('#agent_model_id').val(),
        system_prompt: $('#agent_system_prompt').val(),
        temperature: $('#agent_temperature').val(),
        top_p: $('#agent_top_p').val(),
        max_tokens: $('#agent_max_tokens').val(),
        context_length: $('#agent_context_length').val(),
        tools_config: $('#agent_tools_config').val() ? JSON.parse($('#agent_tools_config').val()) : null,
        is_active: $('#agent_is_active').is(':checked') ? 1 : 0,
        // 移除is_public和builtin_slug，这些只在管理端设置
    };
    
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function() {
            $('#agentModal').modal('hide');
            loadAgents();
        },
        error: function(xhr) {
            alert('保存失败: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : xhr.statusText));
        }
    });
});

// 处理折叠图标旋转
$(document).ready(function() {
    // 默认折叠统计信息区域
    setTimeout(function() {
        $('#statsSection').collapse('hide');
    }, 100);
    
    // 处理折叠图标旋转
    $(document).on('show.bs.collapse', '[data-toggle="collapse"]', function () {
        $('.collapse-icon', this).removeClass('collapsed');
    });
    
    $(document).on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $('.collapse-icon', this).addClass('collapsed');
    });
});
</script>
@endsection