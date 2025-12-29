@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>创建课程</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ url('/courses') }}" method="POST">
                        {{ csrf_field() }}
                        
                        <div class="form-group">
                            <label for="title">课程标题 *</label>
                            <input type="text" class="form-control" id="title" name="title" required value="{{ old('title') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="instructor">讲师</label>
                            <input type="text" class="form-control" id="instructor" name="instructor" value="{{ old('instructor') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="platform">平台</label>
                            <select class="form-control" id="platform" name="platform">
                                <option value="">选择平台</option>
                                <option value="Coursera" {{ old('platform') == 'Coursera' ? 'selected' : '' }}>Coursera</option>
                                <option value="Udemy" {{ old('platform') == 'Udemy' ? 'selected' : '' }}>Udemy</option>
                                <option value="B站" {{ old('platform') == 'B站' ? 'selected' : '' }}>B站</option>
                                <option value="YouTube" {{ old('platform') == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                <option value="其他" {{ old('platform') == '其他' ? 'selected' : '' }}>其他</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="public_url">课程链接</label>
                            <input type="url" class="form-control" id="public_url" name="public_url" value="{{ old('public_url') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="cover_image_url">封面图片URL</label>
                            <input type="url" class="form-control" id="cover_image_url" name="cover_image_url" value="{{ old('cover_image_url') }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">课程描述</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="difficulty">难度</label>
                                <select class="form-control" id="difficulty" name="difficulty">
                                    <option value="beginner" {{ old('difficulty') == 'beginner' ? 'selected' : '' }}>初级</option>
                                    <option value="intermediate" {{ old('difficulty') == 'intermediate' ? 'selected' : '' }}>中级</option>
                                    <option value="advanced" {{ old('difficulty') == 'advanced' ? 'selected' : '' }}>高级</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="estimated_hours">预计学习时长（小时）</label>
                                <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" value="{{ old('estimated_hours') }}" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tags">标签（用逗号分隔）</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="{{ old('tags') }}" placeholder="例如：编程, JavaScript, React">
                            <small class="form-text text-muted">多个标签请用逗号分隔</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" checked>
                                <label class="form-check-label" for="is_public">公开可见</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">创建课程</button>
                        <a href="{{ url('/courses') }}" class="btn btn-secondary">取消</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection