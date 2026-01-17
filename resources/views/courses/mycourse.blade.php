@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>我的课程</h4>
                    <div>
                        <a href="{{ url('/courses') }}" class="btn btn-primary">浏览课程</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- 我的课程 -->
                    <div class="mb-5">
                        <div class="row">
                            @forelse($user_courses ?? [] as $userCourse)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $userCourse->title }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                状态: 
                                                @switch($userCourse->status)
                                                    @case('planned')
                                                        <span class="badge badge-secondary">计划中</span>
                                                        @break
                                                    @case('active')
                                                        <span class="badge badge-primary">学习中</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-success">已完成</span>
                                                        @break
                                                    @case('paused')
                                                        <span class="badge badge-warning">暂停</span>
                                                        @break
                                                    @case('dropped')
                                                        <span class="badge badge-danger">已放弃</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $userCourse->status }}</span>
                                                @endswitch
                                                <br>
                                                进度: {{ $userCourse->progress_percent }}%
                                            </small>
                                        </p>
                                        <a href="{{ url('/courses/' . $userCourse->course->id) }}" class="btn btn-sm btn-primary">学习</a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="text-center py-3">
                                    <p>您还没有加入任何课程</p>
                                    <a href="{{ url('/courses') }}" class="btn btn-primary">浏览课程</a>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- 课程管理功能 -->
{{--                    <div class="row">--}}
{{--                        <div class="col-md-4">--}}
{{--                            <div class="card text-center">--}}
{{--                                <div class="card-body">--}}
{{--                                    <i class="fa fa-graduation-cap fa-3x text-primary mb-3"></i>--}}
{{--                                    <h5>课程列表</h5>--}}
{{--                                    <p class="text-muted">浏览所有可用课程</p>--}}
{{--                                    <a href="{{ url('/courses') }}" class="btn btn-primary">查看课程</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        --}}
{{--                        <div class="col-md-4">--}}
{{--                            <div class="card text-center">--}}
{{--                                <div class="card-body">--}}
{{--                                    <i class="fa fa-plus-circle fa-3x text-success mb-3"></i>--}}
{{--                                    <h5>创建课程</h5>--}}
{{--                                    <p class="text-muted">添加新的课程</p>--}}
{{--                                    <a href="{{ url('/courses/create') }}" class="btn btn-success">创建课程</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        --}}
{{--                        <div class="col-md-4">--}}
{{--                            <div class="card text-center">--}}
{{--                                <div class="card-body">--}}
{{--                                    <i class="fa fa-book fa-3x text-info mb-3"></i>--}}
{{--                                    <h5>我的学习</h5>--}}
{{--                                    <p class="text-muted">查看我的学习进度</p>--}}
{{--                                    <a href="{{ url('/user-courses') }}" class="btn btn-info">学习中心</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection