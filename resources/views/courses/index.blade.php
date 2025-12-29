@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>课程列表</h4>
                    <a href="{{ url('/courses/create') }}" class="btn btn-primary">创建课程</a>
                </div>
                
                <!-- Tab导航 -->
                <ul class="nav nav-tabs" id="courseTabs" role="tablist">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" id="my-courses-tab" data-toggle="tab" href="#my-courses" role="tab" aria-controls="my-courses" aria-selected="false">我创建的课程</a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link active" id="public-courses-tab" data-toggle="tab" href="#public-courses" role="tab" aria-controls="public-courses" aria-selected="true">公开课程</a>
                    </li>

                </ul>
                
                <div class="tab-content" id="courseTabContent">
                    <!-- 公开课程标签页 -->
                    <div class="tab-pane fade show active" id="public-courses" role="tabpanel" aria-labelledby="public-courses-tab">
                        <div class="card-body">
                            <div class="row">
                                @forelse($public_courses as $course)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        @if($course->cover_image_url)
                                        <img src="{{ $course->cover_image_url }}" class="card-img-top" alt="{{ $course->title }}" style="height: 150px; object-fit: cover;">
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $course->title }}</h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> {{ $course->instructor ?: '未知讲师' }}<br>
                                                    <i class="fa fa-globe"></i> {{ $course->platform ?: '平台' }}<br>
                                                    <i class="fa fa-clock-o"></i> {{ $course->estimated_hours ?: 0 }} 小时<br>
                                                    <i class="fa fa-level-up"></i> 
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
                                                </small>
                                            </p>
                                            <p class="card-text flex-grow-1">{{ str_limit($course->description, 100) }}</p>
                                            <div class="mt-auto">
                                                <a href="{{ url('/courses/' . $course->id) }}" class="btn btn-primary btn-sm">查看详情</a>
                                                @if(!auth()->guest() && !in_array($course->id, $user_course_ids ?? []))
                                                <form action="{{ url('/courses/' . $course->id . '/join') }}" method="POST" class="d-inline">
                                                    {{ csrf_field() }}
                                                    <button type="submit" class="btn btn-success btn-sm">加入课程</button>
                                                </form>
                                                @elseif(auth()->id() == $course->created_by)
                                                <span class="badge badge-info btn-sm disabled" style="pointer-events: none;">创建的课程</span>
                                                @elseif(in_array($course->id, $user_course_ids ?? []))
                                                <span class="badge badge-success btn-sm disabled" style="pointer-events: none;">已加入</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="fa fa-graduation-cap fa-3x text-muted mb-3"></i>
                                        <p class="lead">还没有公开课程</p>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <!-- 我创建的课程标签页 -->
                    @auth
                    <div class="tab-pane fade" id="my-courses" role="tabpanel" aria-labelledby="my-courses-tab">
                        <div class="card-body">
                            <div class="row">
                                @forelse($user_created_courses as $course)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        @if($course->cover_image_url)
                                        <img src="{{ $course->cover_image_url }}" class="card-img-top" alt="{{ $course->title }}" style="height: 150px; object-fit: cover;">
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $course->title }}</h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> {{ $course->instructor ?: '未知讲师' }}<br>
                                                    <i class="fa fa-globe"></i> {{ $course->platform ?: '平台' }}<br>
                                                    <i class="fa fa-clock-o"></i> {{ $course->estimated_hours ?: 0 }} 小时<br>
                                                    <i class="fa fa-level-up"></i> 
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
                                                </small>
                                            </p>
                                            <p class="card-text flex-grow-1">{{ str_limit($course->description, 100) }}</p>
                                            <div class="mt-auto">
                                                <a href="{{ url('/courses/' . $course->id) }}" class="btn btn-primary btn-sm">查看详情</a>
                                                <span class="badge badge-info btn-sm disabled" style="pointer-events: none;">
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
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="fa fa-graduation-cap fa-3x text-muted mb-3"></i>
                                        <p class="lead">您还没有创建任何课程</p>
                                        <a href="{{ url('/courses/create') }}" class="btn btn-primary">创建第一个课程</a>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection