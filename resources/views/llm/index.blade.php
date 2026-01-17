@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>供应商管理</h4>
                        <button class="btn btn-primary" onclick="openProviderModal()">添加供应商</button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- 供应商管理主视图 -->
                    <div class="mb-5">
                        
                        <div class="row row-cols-1 row-cols-md-2 g-4" id="provider-container">
                            <!-- 供应商卡片将通过AJAX加载 -->
                        </div>
                        
                        
                    </div>
                    
                    <!-- 模型管理视图 (初始隐藏) -->
                    <div class="mb-5 d-none" id="models-view">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>模型管理</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-secondary" onclick="showProvidersView()">返回供应商</button>
                                <button class="btn btn-primary" onclick="openModelModal()">添加模型</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>供应商</th>
                                        <th>模型名称</th>
                                        <th>显示名称</th>
                                        <th>模型类型</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="model-tbody">
                                    <!-- 模型数据将通过AJAX加载 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- 凭据管理视图 (初始隐藏) -->
                    <div class="mb-5 d-none" id="credentials-view">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>凭据管理</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-secondary" onclick="showProvidersView()">返回供应商</button>
                                <button class="btn btn-primary" onclick="openCredentialModal()">添加凭据</button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>供应商</th>
                                        <th>凭据名称</th>
                                        <th>是否默认</th>
                                        <th>状态</th>
                                        <th>使用次数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="credential-tbody">
                                    <!-- 凭据数据将通过AJAX加载 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 供应商模态框 -->
<div class="modal fade" id="providerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerModalTitle">添加供应商</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="providerForm">
                {{ csrf_field() }}
                <input type="hidden" id="provider_id" name="provider_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="provider_name">名称 *</label>
                        <input type="text" class="form-control" id="provider_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="provider_slug">标识符 *</label>
                        <input type="text" class="form-control" id="provider_slug" name="slug" required>
                    </div>
                    <div class="form-group">
                        <label for="provider_description">描述</label>
                        <textarea class="form-control" id="provider_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="provider_base_url">API基础URL</label>
                        <input type="text" class="form-control" id="provider_base_url" name="base_url">
                    </div>
                    <div class="form-group">
                        <label for="provider_api_type">API类型 *</label>
                        <select class="form-control" id="provider_api_type" name="api_type" required>
                            <option value="openai">OpenAI</option>
                            <option value="anthropic">Anthropic</option>
                            <option value="custom">自定义</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="provider_priority">优先级</label>
                        <input type="number" class="form-control" id="provider_priority" name="priority" value="0">
                    </div>
                    <div class="form-group">
                        <label for="provider_rate_limit">每分钟请求限制</label>
                        <input type="number" class="form-control" id="provider_rate_limit" name="rate_limit_per_minute">
                    </div>
                    <div class="form-group">
                        <label for="provider_concurrent_limit">并发限制</label>
                        <input type="number" class="form-control" id="provider_concurrent_limit" name="concurrent_limit" value="10">
                    </div>
                    <div class="form-group">
                        <label for="provider_config_schema">配置项JSON Schema</label>
                        <textarea class="form-control" id="provider_config_schema" name="config_schema" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="provider_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="provider_is_active">是否启用</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 模型模态框 -->
<div class="modal fade" id="modelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelModalTitle">添加模型</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="modelForm">
                {{ csrf_field() }}
                <input type="hidden" id="model_id" name="model_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="model_provider_id">供应商 *</label>
                        <select class="form-control" id="model_provider_id" name="provider_id" required>
                            <!-- 选项将通过AJAX加载 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="model_name">模型名称 *</label>
                        <input type="text" class="form-control" id="model_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="model_display_name">显示名称</label>
                        <input type="text" class="form-control" id="model_display_name" name="display_name">
                    </div>
                    <div class="form-group">
                        <label for="model_type">模型类型 *</label>
                        <select class="form-control" id="model_type" name="model_type" required>
                            <option value="chat">对话</option>
                            <option value="completion">补全</option>
                            <option value="embedding">嵌入</option>
                            <option value="image">图像</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="model_context_length">上下文长度</label>
                        <input type="number" class="form-control" id="model_context_length" name="context_length">
                    </div>
                    <div class="form-group">
                        <label for="model_max_tokens">最大输出tokens</label>
                        <input type="number" class="form-control" id="model_max_tokens" name="max_tokens">
                    </div>
                    <div class="form-group">
                        <label for="model_input_price">输入价格/1K tokens</label>
                        <input type="number" step="0.000001" class="form-control" id="model_input_price" name="input_price_per_1k">
                    </div>
                    <div class="form-group">
                        <label for="model_output_price">输出价格/1K tokens</label>
                        <input type="number" step="0.000001" class="form-control" id="model_output_price" name="output_price_per_1k">
                    </div>
                    <div class="form-group">
                        <label for="model_capabilities">能力配置</label>
                        <textarea class="form-control" id="model_capabilities" name="capabilities" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="model_sort_order">排序</label>
                        <input type="number" class="form-control" id="model_sort_order" name="sort_order" value="0">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="model_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="model_is_active">是否启用</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 凭据模态框 -->
<div class="modal fade" id="credentialModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialModalTitle">添加凭据</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="credentialForm">
                {{ csrf_field() }}
                <input type="hidden" id="credential_id" name="credential_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="credential_provider_id">供应商 *</label>
                        <select class="form-control" id="credential_provider_id" name="provider_id" required>
                            <!-- 选项将通过AJAX加载 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="credential_name">凭据名称 *</label>
                        <input type="text" class="form-control" id="credential_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="credential_api_key">API Key *</label>
                        <input type="password" class="form-control" id="credential_api_key" name="api_key" placeholder="输入API Key" required>
                        <small class="form-text text-muted">更新凭据时请输入新API Key，留空则保持原值</small>
                    </div>
                    <div class="form-group">
                        <label for="credential_config">额外配置</label>
                        <textarea class="form-control" id="credential_config" name="config" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="credential_quota_limit">配额限制</label>
                        <input type="number" class="form-control" id="credential_quota_limit" name="quota_limit">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="credential_is_default" name="is_default" value="1">
                            <label class="form-check-label" for="credential_is_default">是否为默认凭据</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="credential_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="credential_is_active">是否启用</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 页面加载完成后初始化数据
$(document).ready(function() {
    loadProviders();
    loadProviderOptions();
});

// 加载供应商数据并以卡片形式展示
function loadProviders() {
    $.get('/llm/providers', function(data) {
        const container = $('#provider-container');
        container.empty();
        
        data.result.providers.forEach(function(provider) {
            const cardHtml = `
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h5 class="mb-0"><strong>${provider.name}</strong></h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-info" onclick="manageModelsForProvider(${provider.id})">模型管理</button>
                                <button class="btn btn-sm btn-warning" onclick="manageCredentialsForProvider(${provider.id})">凭据管理</button>
                                <button class="btn btn-sm btn-primary" onclick="editProvider(${provider.id})">编辑</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProvider(${provider.id})">删除</button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="card-text mb-1"><strong>标识符:</strong> <span class="text-muted">${provider.slug}</span></p>
                                    <p class="card-text mb-1"><strong>API类型:</strong> <span class="text-muted">${provider.api_type}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text mb-1"><strong>状态:</strong> <span class="badge ${provider.is_active ? 'bg-success' : 'bg-secondary'}">${provider.is_active ? '启用' : '禁用'}</span></p>
                                    <p class="card-text mb-1"><strong>优先级:</strong> <span class="text-muted">${provider.priority}</span></p>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <p class="card-text mb-0 small"><strong>描述:</strong></p>
                                    <p class="card-text text-muted small">${provider.description || '无'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(cardHtml);
        });
    });
}

// 加载模型数据
function loadModels() {
    $.get('/llm/models', function(data) {
        const tbody = $('#model-tbody');
        tbody.empty();
        
        data.result.models.forEach(function(model) {
            const providerName = model.provider ? model.provider.name : '未知';
            const row = `
                <tr>
                    <td>${model.id}</td>
                    <td>${providerName}</td>
                    <td>${model.name}</td>
                    <td>${model.display_name || ''}</td>
                    <td>${model.model_type}</td>
                    <td>${model.is_active ? '启用' : '禁用'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editModel(${model.id})">编辑</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteModel(${model.id})">删除</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    });
}

// 加载凭据数据
function loadCredentials() {
    $.get('/llm/credentials', function(data) {
        const tbody = $('#credential-tbody');
        tbody.empty();
        
        data.result.credentials.forEach(function(credential) {
            const providerName = credential.provider ? credential.provider.name : '未知';
            const row = `
                <tr>
                    <td>${credential.id}</td>
                    <td>${providerName}</td>
                    <td>${credential.name}</td>
                    <td>${credential.is_default ? '是' : '否'}</td>
                    <td>${credential.is_active ? '启用' : '禁用'}</td>
                    <td>${credential.usage_count}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editCredential(${credential.id})">编辑</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCredential(${credential.id})">删除</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    });
}

// 加载供应商选项（用于模型和凭据的下拉框）
function loadProviderOptions() {
    $.get('/llm/providers', function(data) {
        const providerSelect = $('#model_provider_id, #credential_provider_id');
        providerSelect.empty();
        
        data.result.providers.forEach(function(provider) {
            providerSelect.append(`<option value="${provider.id}">${provider.name}</option>`);
        });
    });
}

// 视图切换函数
function showProvidersView() {
    $('#provider-container').closest('.mb-5').removeClass('d-none');
    $('#models-view').addClass('d-none');
    $('#credentials-view').addClass('d-none');
    // 重新加载供应商数据
    loadProviders();
    // 重置下拉框选择
    $('#model_provider_id').val('');
    $('#credential_provider_id').val('');
}

function showModelsView() {
    $('#provider-container').closest('.mb-5').addClass('d-none');
    $('#models-view').removeClass('d-none');
    $('#credentials-view').addClass('d-none');
    // 加载模型数据
    loadModels();
}

function showCredentialsView() {
    $('#provider-container').closest('.mb-5').addClass('d-none');
    $('#models-view').addClass('d-none');
    $('#credentials-view').removeClass('d-none');
    // 加载凭据数据
    loadCredentials();
}

// 管理特定供应商的模型
function manageModelsForProvider(providerId) {
    // 设置模型表单中的供应商ID
    $('#model_provider_id').val(providerId);
    // 切换到模型视图
    $('#provider-container').closest('.mb-5').addClass('d-none');
    $('#models-view').removeClass('d-none');
    $('#credentials-view').addClass('d-none');
    // 加载该供应商的模型数据
    loadModelsByProvider(providerId);
}

// 管理特定供应商的凭据
function manageCredentialsForProvider(providerId) {
    // 设置凭据表单中的供应商ID
    $('#credential_provider_id').val(providerId);
    // 切换到凭据视图
    $('#provider-container').closest('.mb-5').addClass('d-none');
    $('#models-view').addClass('d-none');
    $('#credentials-view').removeClass('d-none');
    // 加载该供应商的凭据数据
    loadCredentialsByProvider(providerId);
}

// 根据供应商ID加载模型数据
function loadModelsByProvider(providerId) {
    $.get('/llm/models', function(data) {
        const tbody = $('#model-tbody');
        tbody.empty();
        
        data.result.models.forEach(function(model) {
            // 只显示指定供应商的模型
            if (model.provider_id == providerId) {
                const providerName = model.provider ? model.provider.name : '未知';
                const row = `
                    <tr>
                        <td>${model.id}</td>
                        <td>${providerName}</td>
                        <td>${model.name}</td>
                        <td>${model.display_name || ''}</td>
                        <td>${model.model_type}</td>
                        <td>${model.is_active ? '启用' : '禁用'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editModel(${model.id})">编辑</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteModel(${model.id})">删除</button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            }
        });
    });
}

// 根据供应商ID加载凭据数据
function loadCredentialsByProvider(providerId) {
    $.get('/llm/credentials', function(data) {
        const tbody = $('#credential-tbody');
        tbody.empty();
        
        data.result.credentials.forEach(function(credential) {
            // 只显示指定供应商的凭据
            if (credential.provider_id == providerId) {
                const providerName = credential.provider ? credential.provider.name : '未知';
                const row = `
                    <tr>
                        <td>${credential.id}</td>
                        <td>${providerName}</td>
                        <td>${credential.name}</td>
                        <td>${credential.is_default ? '是' : '否'}</td>
                        <td>${credential.is_active ? '启用' : '禁用'}</td>
                        <td>${credential.usage_count}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editCredential(${credential.id})">编辑</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCredential(${credential.id})">删除</button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            }
        });
    });
}

// 打开供应商模态框
function openProviderModal() {
    $('#providerForm')[0].reset();
    $('#provider_id').val('');
    $('#providerModalTitle').text('添加供应商');
    $('#providerModal').modal('show');
}

// 编辑供应商
function editProvider(id) {
    $.get(`/llm/providers/${id}`, function(data) {
        const provider = data.result;
        $('#provider_id').val(provider.id);
        $('#provider_name').val(provider.name);
        $('#provider_slug').val(provider.slug);
        $('#provider_description').val(provider.description);
        $('#provider_base_url').val(provider.base_url);
        $('#provider_api_type').val(provider.api_type);
        $('#provider_priority').val(provider.priority);
        $('#provider_rate_limit').val(provider.rate_limit_per_minute);
        $('#provider_concurrent_limit').val(provider.concurrent_limit);
        $('#provider_config_schema').val(provider.config_schema ? JSON.stringify(provider.config_schema) : '');
        $('#provider_is_active').prop('checked', provider.is_active);
        $('#providerModalTitle').text('编辑供应商');
        $('#providerModal').modal('show');
    });
}

// 删除供应商
function deleteProvider(id) {
    if (confirm('确定要删除这个供应商吗？')) {
        $.ajax({
            url: `/llm/providers/${id}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadProviders();
                loadProviderOptions();
            },
            error: function(xhr) {
                alert('删除失败: ' + xhr.responseJSON.message);
            }
        });
    }
}

// 提交供应商表单
$('#providerForm').submit(function(e) {
    e.preventDefault();
    
    const id = $('#provider_id').val();
    const url = id ? `/llm/providers/${id}` : '/llm/providers';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: method,
        name: $('#provider_name').val(),
        slug: $('#provider_slug').val(),
        description: $('#provider_description').val(),
        base_url: $('#provider_base_url').val(),
        api_type: $('#provider_api_type').val(),
        priority: $('#provider_priority').val(),
        rate_limit_per_minute: $('#provider_rate_limit').val(),
        concurrent_limit: $('#provider_concurrent_limit').val(),
        config_schema: $('#provider_config_schema').val() ? JSON.parse($('#provider_config_schema').val()) : null,
        is_active: $('#provider_is_active').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function() {
            $('#providerModal').modal('hide');
            loadProviders();
            loadProviderOptions();
        },
        error: function(xhr) {
            alert('保存失败: ' + xhr.responseJSON.message);
        }
    });
});

// 打开模型模态框
function openModelModal() {
    $('#modelForm')[0].reset();
    $('#model_id').val('');
    $('#modelModalTitle').text('添加模型');
    $('#modelModal').modal('show');
}

// 编辑模型
function editModel(id) {
    $.get(`/llm/models/${id}`, function(data) {
        const model = data.result;
        $('#model_id').val(model.id);
        $('#model_provider_id').val(model.provider_id);
        $('#model_name').val(model.name);
        $('#model_display_name').val(model.display_name);
        $('#model_type').val(model.model_type);
        $('#model_context_length').val(model.context_length);
        $('#model_max_tokens').val(model.max_tokens);
        $('#model_input_price').val(model.input_price_per_1k);
        $('#model_output_price').val(model.output_price_per_1k);
        $('#model_capabilities').val(model.capabilities ? JSON.stringify(model.capabilities) : '');
        $('#model_sort_order').val(model.sort_order);
        $('#model_is_active').prop('checked', model.is_active);
        $('#modelModalTitle').text('编辑模型');
        $('#modelModal').modal('show');
        
        // 如果当前在特定供应商的模型管理页面，保持下拉框选择不变
        if ($('#model_provider_id').val() != model.provider_id) {
            $('#model_provider_id').val(model.provider_id);
        }
    });
}

// 删除模型
function deleteModel(id) {
    if (confirm('确定要删除这个模型吗？')) {
        $.ajax({
            url: `/llm/models/${id}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadModels();
            },
            error: function(xhr) {
                alert('删除失败: ' + xhr.responseJSON.message);
            }
        });
    }
}

// 提交模型表单
$('#modelForm').submit(function(e) {
    e.preventDefault();
    
    const id = $('#model_id').val();
    const url = id ? `/llm/models/${id}` : '/llm/models';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: method,
        provider_id: $('#model_provider_id').val(),
        name: $('#model_name').val(),
        display_name: $('#model_display_name').val(),
        model_type: $('#model_type').val(),
        context_length: $('#model_context_length').val(),
        max_tokens: $('#model_max_tokens').val(),
        input_price_per_1k: $('#model_input_price').val(),
        output_price_per_1k: $('#model_output_price').val(),
        capabilities: $('#model_capabilities').val() ? JSON.parse($('#model_capabilities').val()) : null,
        sort_order: $('#model_sort_order').val(),
        is_active: $('#model_is_active').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function() {
            $('#modelModal').modal('hide');
            loadModels();
        },
        error: function(xhr) {
            alert('保存失败: ' + xhr.responseJSON.message);
        }
    });
});

// 打开凭据模态框
function openCredentialModal() {
    $('#credentialForm')[0].reset();
    $('#credential_id').val('');
    $('#credentialModalTitle').text('添加凭据');
    $('#credentialModal').modal('show');
}

// 编辑凭据
function editCredential(id) {
    $.get(`/llm/credentials/${id}`, function(data) {
        const credential = data.result;
        $('#credential_id').val(credential.id);
        $('#credential_provider_id').val(credential.provider_id);
        $('#credential_name').val(credential.name);
        // 不设置API Key字段，让用户重新输入
        if (credential.config) {
            $('#credential_config').val(JSON.stringify(credential.config));
        }
        $('#credential_quota_limit').val(credential.quota_limit);
        $('#credential_is_default').prop('checked', credential.is_default);
        $('#credential_is_active').prop('checked', credential.is_active);
        $('#credentialModalTitle').text('编辑凭据');
        $('#credentialModal').modal('show');
        
        // 如果当前在特定供应商的凭据管理页面，保持下拉框选择不变
        if ($('#credential_provider_id').val() != credential.provider_id) {
            $('#credential_provider_id').val(credential.provider_id);
        }
    });
}

// 删除凭据
function deleteCredential(id) {
    if (confirm('确定要删除这个凭据吗？')) {
        $.ajax({
            url: `/llm/credentials/${id}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadCredentials();
            },
            error: function(xhr) {
                alert('删除失败: ' + xhr.responseJSON.message);
            }
        });
    }
}

// 提交凭据表单
$('#credentialForm').submit(function(e) {
    e.preventDefault();
    
    const id = $('#credential_id').val();
    const url = id ? `/llm/credentials/${id}` : '/llm/credentials';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: method,
        provider_id: $('#credential_provider_id').val(),
        name: $('#credential_name').val(),
        config: $('#credential_config').val() ? JSON.parse($('#credential_config').val()) : null,
        quota_limit: $('#credential_quota_limit').val(),
        is_default: $('#credential_is_default').is(':checked') ? 1 : 0,
        is_active: $('#credential_is_active').is(':checked') ? 1 : 0
    };
    
    // 只有在API Key字段有值时才添加到数据中
    const apiKey = $('#credential_api_key').val();
    if (apiKey) {
        data.api_key = apiKey;
    }
    
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function() {
            $('#credentialModal').modal('hide');
            loadCredentials();
        },
        error: function(xhr) {
            alert('保存失败: ' + xhr.responseJSON.message);
        }
    });
});
</script>
@endsection