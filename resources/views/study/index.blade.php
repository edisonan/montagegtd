@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>学习中心</h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    我的课程
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    学习计划
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    学习记录
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    笔记中心
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    讨论区
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header">
                                    <h5>我的课程</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($user_courses ?? [] as $userCourse)
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
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
                                                    <a href="{{ url('/courses/' . $userCourse->course->id) }}" class="btn btn-sm btn-primary">开始学习</a>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="col-12">
                                            <div class="text-center py-5">
                                                <p>您还没有加入任何课程</p>
                                                <a href="{{ url('/courses') }}" class="btn btn-primary">浏览课程</a>
                                            </div>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection