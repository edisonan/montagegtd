@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>创建 Personal Access Token</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('personal-access-tokens.store') }}" method="POST">
                        {!! csrf_field() !!}
                        
                        <div class="form-group">
                            <label for="name">令牌名称 *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
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
                            <label for="expires_at">过期时间</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            <small class="form-text text-muted">留空表示永不过期</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">创建令牌</button>
                        <a href="{{ route('personal-access-tokens.index') }}" class="btn btn-secondary">取消</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection