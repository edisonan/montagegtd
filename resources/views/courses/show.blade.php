@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $course->title }}</h4>
                    <div>
                        <a href="{{ url('/courses') }}" class="btn btn-secondary">返回课程列表</a>
                        @if(auth()->id() == $course->created_by)
                            <a href="{{ url('/admin/courses/' . $course->id . '/edit') }}" class="btn btn-primary ml-2">编辑课程</a>
                            <span class="badge ml-2" style="line-height: 1.5;
                                @if($course->public_status == 1)
                                    background-color: #6c757d; /* 私有 */
                                @elseif($course->public_status == 2)
                                    background-color: #ffc107; /* 待审核 */
                                @elseif($course->public_status == 3)
                                    background-color: #28a745; /* 已审核 */
                                @else
                                    background-color: #6c757d;
                                @endif">
                                @if($course->public_status == 1)
                                    私有
                                @elseif($course->public_status == 2)
                                    待审核
                                @elseif($course->public_status == 3)
                                    已审核
                                @else
                                    未知状态
                                @endif
                            </span>
                            <a href="{{ url('/courses/' . $course->id . '/items') }}" class="btn btn-info ml-2">管理章节</a>
                        @elseif(!auth()->guest() && !$is_joined)
                            <form action="{{ url('/courses/' . $course->id . '/join') }}" method="POST" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success ml-2">加入课程</button>
                            </form>
                        @elseif(!auth()->guest() && $is_joined)
                            <button class="btn btn-success ml-2 disabled" disabled>已加入课程</button>
                            <a href="{{ url('/study') }}" class="btn btn-primary ml-2">前往学习中心</a>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @if($course->cover_image_url)
                            <img src="{{ $course->cover_image_url }}" class="img-fluid mb-4" alt="{{ $course->title }}">
                            @endif
                            
                            <!-- 折叠的课程信息 -->
                            <div class="course-info mb-4">
                                <h5 class="d-flex justify-content-between align-items-center">
                                    <span>课程信息</span>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#courseInfo" aria-expanded="false" aria-controls="courseInfo">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </h5>
                                <div class="collapse" id="courseInfo">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>讲师：</strong></td>
                                            <td>{{ $course->instructor ?: '未知' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>平台：</strong></td>
                                            <td>{{ $course->platform ?: '未知' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>难度：</strong></td>
                                            <td>
                                                @switch($course->difficulty)
                                                    @case('beginner')
                                                        <span class="badge badge-primary">初级</span>
                                                        @break
                                                    @case('intermediate')
                                                        <span class="badge badge-warning">中级</span>
                                                        @break
                                                    @case('advanced')
                                                        <span class="badge badge-danger">高级</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">未知</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>预计时长：</strong></td>
                                            <td>{{ $course->estimated_hours ?: 0 }} 小时</td>
                                        </tr>
                                        @if($course->tags && is_array($course->tags))
                                        <tr>
                                            <td><strong>标签：</strong></td>
                                            <td>
                                                @foreach($course->tags as $tag)
                                                    <span class="badge badge-info">{{ $tag }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            
                            <!-- 折叠的课程描述 -->
                            <div class="course-description mb-4">
                                <h5 class="d-flex justify-content-between align-items-center">
                                    <span>课程描述</span>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#courseDescription" aria-expanded="false" aria-controls="courseDescription">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </h5>
                                <div class="collapse" id="courseDescription">
                                    <p>{{ $course->description ?: '暂无描述' }}</p>
                                </div>
                            </div>
                            
                            <!-- 突出显示的课程结构 -->
                            <div class="course-structure">
                                <h5 class="d-flex justify-content-between align-items-center">
                                    <span>课程结构</span>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#courseStructure" aria-expanded="true" aria-controls="courseStructure">
                                        <i class="fa fa-chevron-up"></i>
                                    </button>
                                </h5>
                                <div class="collapse show" id="courseStructure">
                                    @if($structure && count($structure) > 0)
                                    <div class="list-group">
                                        @foreach($structure as $item)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fa fa-{{
                                                        $item->item_type == 'video' ? 'video-camera' : 
                                                        ($item->item_type == 'quiz' ? 'question-circle' : 
                                                        ($item->item_type == 'assignment' ? 'file-text' : 
                                                        ($item->item_type == 'reading' ? 'book' : 'folder')))
                                                    }}"></i>
                                                    <strong>{{ $item->title }}</strong>
                                                    @if($item->duration)
                                                    <small class="text-muted">({{ $item->duration }} 分钟)</small>
                                                    @endif
                                                </div>
                                                <span class="badge badge-secondary">{{ ucfirst($item->item_type) }}</span>
                                            </div>
                                            @if($item->description)
                                            <p class="mb-0 mt-2 text-muted">{{ str_limit($item->description, 100) }}</p>
                                            @endif>
                                            
                                            @if($item->children && count($item->children) > 0)
                                            <div class="ml-4 mt-2">
                                                @foreach($item->children as $child)
                                                <div class="list-group-item p-2">
                                                    <i class="fa fa-{{
                                                        $child->item_type == 'video' ? 'video-camera' : 
                                                        ($child->item_type == 'quiz' ? 'question-circle' : 
                                                        ($child->item_type == 'assignment' ? 'file-text' : 
                                                        ($child->item_type == 'reading' ? 'book' : 'file')))
                                                    }}"></i>
                                                    {{ $child->title }}
                                                    @if($child->duration)
                                                    <small class="text-muted">({{ $child->duration }} 分钟)</small>
                                                    @endif
                                                    <span class="badge badge-secondary badge-pill float-right">{{ ucfirst($child->item_type) }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-muted">暂无课程内容</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>讨论区</h5>
                                </div>
                                <div class="card-body">
                                    <p>课程讨论功能即将推出...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 为所有折叠按钮添加图标切换功能
    $('.collapse').on('show.bs.collapse', function() {
        var button = $(this).parent().find('button[data-toggle="collapse"]');
        var icon = button.find('i');
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
    
    $('.collapse').on('hide.bs.collapse', function() {
        var button = $(this).parent().find('button[data-toggle="collapse"]');
        var icon = button.find('i');
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });
});
</script>
@endsection