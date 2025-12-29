@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>学习课程</h4>
                    <div>
                        <a href="{{ url('/courses/' . $course->id) }}" class="btn btn-secondary">返回课程</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    课程大纲
                                </a>
                                @foreach($courseStructure as $item)
                                <a href="#item-{{ $item->id }}" class="list-group-item list-group-item-action ml-3">
                                    <i class="fa fa-{{ 
                                        $item->item_type == 'video' ? 'video-camera' : 
                                        ($item->item_type == 'quiz' ? 'question-circle' : 
                                        ($item->item_type == 'assignment' ? 'file-text' : 
                                        ($item->item_type == 'reading' ? 'book' : 'folder')))
                                    }}"></i>
                                    {{ $item->title }}
                                </a>
                                @if($item->children && count($item->children) > 0)
                                    @foreach($item->children as $child)
                                    <a href="#item-{{ $child->id }}" class="list-group-item list-group-item-action ml-4">
                                        <i class="fa fa-{{ 
                                            $child->item_type == 'video' ? 'video-camera' : 
                                            ($child->item_type == 'quiz' ? 'question-circle' : 
                                            ($child->item_type == 'assignment' ? 'file-text' : 
                                            ($child->item_type == 'reading' ? 'book' : 'file')))
                                        }}"></i>
                                        {{ $child->title }}
                                    </a>
                                    @endforeach
                                @endif
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ $course->title }}</h5>
                                </div>
                                <div class="card-body">
                                    @foreach($courseStructure as $item)
                                    <div id="item-{{ $item->id }}" class="mb-4">
                                        <h6>
                                            <i class="fa fa-{{ 
                                                $item->item_type == 'video' ? 'video-camera' : 
                                                ($item->item_type == 'quiz' ? 'question-circle' : 
                                                ($item->item_type == 'assignment' ? 'file-text' : 
                                                ($item->item_type == 'reading' ? 'book' : 'folder')))
                                            }}"></i>
                                            {{ $item->title }}
                                        </h6>
                                        @if($item->description)
                                        <p class="text-muted">{{ $item->description }}</p>
                                        @endif
                                        @if($item->external_url)
                                        <a href="{{ $item->external_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-external-link"></i> 访问内容
                                        </a>
                                        @endif
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-success">标记为已完成</button>
                                            <button class="btn btn-sm btn-info">添加笔记</button>
                                        </div>
                                    </div>
                                    
                                    @if($item->children && count($item->children) > 0)
                                        @foreach($item->children as $child)
                                        <div id="item-{{ $child->id }}" class="mb-4 ml-4">
                                            <h6>
                                                <i class="fa fa-{{ 
                                                    $child->item_type == 'video' ? 'video-camera' : 
                                                    ($child->item_type == 'quiz' ? 'question-circle' : 
                                                    ($child->item_type == 'assignment' ? 'file-text' : 
                                                    ($child->item_type == 'reading' ? 'book' : 'file')))
                                                }}"></i>
                                                {{ $child->title }}
                                            </h6>
                                            @if($child->description)
                                            <p class="text-muted">{{ $child->description }}</p>
                                            @endif
                                            @if($child->external_url)
                                            <a href="{{ $child->external_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-external-link"></i> 访问内容
                                            </a>
                                            @endif
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-success">标记为已完成</button>
                                                <button class="btn btn-sm btn-info">添加笔记</button>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                    @endforeach
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