@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Personal Access Tokens</h4>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#createTokenModal">创建令牌</button>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>名称</th>
                                    <th>权限范围</th>
                                    <th>创建时间</th>
                                    <th>过期时间</th>
                                    <th>最后使用</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="tokensTableBody">
                                @forelse($tokens as $token)
                                <tr>
                                    <td>{{ $token->id }}</td>
                                    <td>{{ $token->name }}</td>
                                    <td>
                                        @if($token->scopes && count($token->scopes) > 0)
                                            @foreach($token->scopes as $scope)
                                                <span class="badge badge-primary">{{ $scope }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">无权限</span>
                                        @endif
                                    </td>
                                    <td>{{ $token->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($token->expires_at)
                                            {{ $token->expires_at->format('Y-m-d H:i:s') }}
                                            @if($token->isExpired())
                                                <span class="badge badge-danger">已过期</span>
                                            @else
                                                <span class="badge badge-success">有效</span>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">无限制</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($token->last_used_at)
                                            {{ $token->last_used_at->format('Y-m-d H:i:s') }}
                                        @else
                                            <span class="text-muted">从未使用</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="deleteToken({{ $token->id }})">删除</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">暂无令牌</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 创建令牌模态框 -->
<div class="modal fade" id="createTokenModal" tabindex="-1" aria-labelledby="createTokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTokenModalLabel">创建 Personal Access Token</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="tokenErrors" class="alert alert-danger" style="display: none;">
                    <ul id="tokenErrorList"></ul>
                </div>

                <form id="tokenForm" method="POST" class="form-horizontal">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="tokenName">令牌名称 *</label>
                        <input type="text" class="form-control" id="tokenName" name="name" required>
                        <small class="form-text text-muted">用于标识此令牌的用途</small>
                    </div>

                    <div class="form-group">
                        <label>权限范围</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scopeRead" name="scopes[]" value="read">
                            <label class="form-check-label" for="scopeRead">读取 (read)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scopeWrite" name="scopes[]" value="write">
                            <label class="form-check-label" for="scopeWrite">写入 (write)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scopeDelete" name="scopes[]" value="delete">
                            <label class="form-check-label" for="scopeDelete">删除 (delete)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scopeAdmin" name="scopes[]" value="admin">
                            <label class="form-check-label" for="scopeAdmin">管理 (admin)</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expiresAt">过期时间</label>
                        <input type="datetime-local" class="form-control" id="expiresAt" name="expires_at">
                        <small class="form-text text-muted">留空表示永不过期</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="createToken()">创建令牌</button>
            </div>
        </div>
    </div>
</div>

<!-- 显示新创建令牌的模态框 -->
<div class="modal fade" id="showTokenModal" tabindex="-1" aria-labelledby="showTokenModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showTokenModalLabel">令牌创建成功</h5>
            </div>
            <div class="modal-body">
                <p>请立即复制以下令牌，此令牌仅显示一次：</p>
                <div class="form-group">
                    <input type="text" class="form-control" id="newTokenValue" readonly>
                </div>
                <p class="text-danger">警告：请安全保存此令牌，丢失后将无法找回。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="copyToken()">复制令牌</button>
            </div>
        </div>
    </div>
</div>

<script>
// 创建令牌
function createToken() {
    var form = document.getElementById('tokenForm');
    var formData = new FormData(form);
    
    $.ajax({
        url: '/personal-access-tokens',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if(response.code == 9999) {
                $('#createTokenModal').modal('hide');
                
                // 显示新令牌
                $('#newTokenValue').val(response.result.token);
                $('#showTokenModal').modal('show');
                
                // 刷新页面
                setTimeout(function() {
                    location.reload();
                }, 3000);
            } else {
                // 显示错误信息
                $('#tokenErrorList').empty();
                if(response.msg) {
                    $('#tokenErrorList').append('<li>' + response.msg + '</li>');
                } else {
                    $('#tokenErrorList').append('<li>创建失败</li>');
                }
                $('#tokenErrors').show();
            }
        },
        error: function(xhr) {
            // 显示验证错误
            $('#tokenErrorList').empty();
            if(xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function(key, value) {
                    $.each(value, function(index, msg) {
                        $('#tokenErrorList').append('<li>' + msg + '</li>');
                    });
                });
            } else {
                $('#tokenErrorList').append('<li>创建失败，请稍后重试</li>');
            }
            $('#tokenErrors').show();
        }
    });
}

// 删除令牌
function deleteToken(id) {
    if (confirm('确定要删除这个令牌吗？此操作不可恢复！')) {
        $.ajax({
            url: `/personal-access-tokens/${id}`,
            type: 'DELETE',
            data: {
                _token: $('input[name="_token"]').val()
            },
            success: function(response) {
                if(response.code == 9999) {
                    alert(response.msg || '删除成功');
                    location.reload();
                } else {
                    alert('删除失败: ' + (response.msg || '未知错误'));
                }
            },
            error: function(xhr) {
                alert('删除失败: ' + (xhr.responseJSON && xhr.responseJSON.msg ? xhr.responseJSON.msg : '未知错误'));
            }
        });
    }
}

// 复制令牌到剪贴板
function copyToken() {
    var tokenInput = document.getElementById('newTokenValue');
    tokenInput.select();
    tokenInput.setSelectionRange(0, 99999); // 移动端兼容
    
    document.execCommand('copy');
    alert('令牌已复制到剪贴板');
}
</script>
@endsection