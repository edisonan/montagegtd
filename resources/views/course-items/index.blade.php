@extends('layouts.app')

@section('content')
    @include('components.course-item-modal')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $course->title }} - 章节管理</h4>
                    <div>
                        <a href="{{ url('/courses/' . $course->id) }}" class="btn btn-secondary">返回课程</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>课程章节结构</h5>
                            @if($structure && count($structure) > 0)
                            <div class="list-group mb-4">
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
                                        <div>
                                            <span class="badge badge-secondary mr-2">{{ ucfirst($item->item_type) }}</span>
                                            <button class="btn btn-sm btn-primary" onclick="editItem({{ $item->id }})">编辑</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteItem({{ $item->id }})">删除</button>
                                        </div>
                                    </div>
                                    @if($item->description)
                                    <p class="mb-0 mt-2 text-muted">{{ str_limit($item->description, 100) }}</p>
                                    @endif
                                    
                                    @if($item->children && count($item->children) > 0)
                                    <div class="ml-4 mt-2">
                                        @foreach($item->children as $child)
                                        <div class="list-group-item p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
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
                                                </div>
                                                <div>
                                                    <span class="badge badge-secondary mr-2">{{ ucfirst($child->item_type) }}</span>
                                                    <button class="btn btn-sm btn-primary" onclick="editItem({{ $child->id }})">编辑</button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteItem({{ $child->id }})">删除</button>
                                                </div>
                                            </div>
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
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>操作</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary btn-block" onclick="openCourseItemModal({{ $course->id }})">添加章节</button>
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
var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
    ? window.TaskApiBridge.requestWithFallback
    : null;

// 提交课程章节表单
function submitCourseItemForm() {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }

    var form = document.getElementById('courseItemFormModal');
    var itemId = $('#item_id_modal').val();
    var courseId = $('#course_id_modal').val();

    var payload = {
        course_id: courseId,
        title: $('#title_modal').val(),
        parent_id: $('#parent_id_modal').val() || null,
        item_type: $('#item_type_modal').val(),
        duration: $('#duration_modal').val(),
        external_url: $('#external_url_modal').val(),
        description: $('#description_modal').val(),
        order_index: $('#order_index_modal').val()
    };

    var apiPath = itemId ? ('/course-items/' + itemId) : '/course-items';

    apiRequest(itemId ? 'PUT' : 'POST', apiPath, payload).then(function(response) {
        if(response.code == 9999) {
            $('#courseItemModal').modal('hide');
            alert(response.msg || '操作成功');
            location.reload();
        } else {
            $('#courseItemErrorList').empty();
            if(response.msg) {
                $('#courseItemErrorList').append('<li>' + response.msg + '</li>');
            } else {
                $('#courseItemErrorList').append('<li>操作失败</li>');
            }
            $('#courseItemErrors').show();
        }
    }).catch(function() {
        $('#courseItemErrorList').empty();
        $('#courseItemErrorList').append('<li>操作失败，请稍后重试</li>');
        $('#courseItemErrors').show();
    });
}

// 加载章节结构到下拉框
function loadCourseStructure(courseId, excludeItemId = null, currentParentId = null) {
    if (!apiRequest) {
        console.log('API客户端未初始化');
        return;
    }
    apiRequest('GET', '/course-items/structure/' + courseId, {}).then(function(response) {
        if(response.code == 9999) {
            var selectElement = $('#parent_id_modal');
            selectElement.empty();
            selectElement.append('<option value="">无父级（顶级章节）</option>');

            function buildOptions(items, level = 0) {
                var prefix = level > 0 ? '--'.repeat(level) + ' ' : '';
                $.each(items, function(index, item) {
                    if(excludeItemId && item.id == excludeItemId) {
                        return true;
                    }

                    selectElement.append('<option value="' + item.id + '">' + prefix + item.title + '</option>');

                    if(item.children && item.children.length > 0) {
                        buildOptions(item.children, level + 1);
                    }
                });
            }

            buildOptions(response.result);
            if(currentParentId) {
                selectElement.val(currentParentId);
            }
        }
    }).catch(function(err) {
        console.log('加载章节结构失败');
        console.log(err);
    });
}

// 打开课程章节编辑模态框
function openCourseItemModal(courseId, itemData = null) {
    // 清除错误信息
    $('#courseItemErrors').hide();
    $('#courseItemErrorList').empty();
    
    // 设置课程ID
    $('#course_id_modal').val(courseId);
    
    // 如果是编辑模式，填充数据
    if(itemData) {
        $('#courseItemModalLabel').text('编辑章节');
        $('#item_id_modal').val(itemData.id);
        $('#title_modal').val(itemData.title);
        $('#parent_id_modal').val(itemData.parent_id || '');
        $('#item_type_modal').val(itemData.item_type);
        $('#duration_modal').val(itemData.duration || 0);
        $('#external_url_modal').val(itemData.external_url || '');
        $('#description_modal').val(itemData.description || '');
        $('#order_index_modal').val(itemData.order_index || 0);
        
        // 加载章节结构，排除当前项及其子项，以避免循环引用
        loadCourseStructure(courseId, itemData.id, itemData.parent_id);
    } else {
        // 如果是添加模式，清空表单
        $('#courseItemModalLabel').text('添加章节');
        $('#item_id_modal').val('');
        $('#title_modal').val('');
        $('#parent_id_modal').val('');
        $('#item_type_modal').val('chapter');
        $('#duration_modal').val(0);
        $('#external_url_modal').val('');
        $('#description_modal').val('');
        $('#order_index_modal').val(0);
        
        // 加载章节结构
        loadCourseStructure(courseId);
    }
    
    // 显示模态框
    $('#courseItemModal').modal('show');
}

// 取消编辑
function cancelEdit() {
    $('#courseItemModal').modal('hide');
}

// 删除项目
function deleteItem(id) {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }
    if (confirm('确定要删除这个章节吗？')) {
        apiRequest('DELETE', '/course-items/' + id, {}).then(function(response) {
            if(response.code == 9999) {
                alert(response.msg || '删除成功');
                location.reload();
            } else {
                alert('删除失败: ' + (response.msg || '未知错误'));
            }
        }).catch(function() {
            alert('删除失败: 未知错误');
        });
    }
}

// 编辑项目 - 现在使用模态框
function editItem(id) {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }
    apiRequest('GET', '/course-items/' + id, {}).then(function(response) {
        if(response.code == 9999) {
            const item = response.result.course_item;
            // 调用模态框组件的函数来打开编辑窗口
            openCourseItemModal({{ $course->id }}, item);
        } else {
            alert('获取章节信息失败: ' + (response.msg || '未知错误'));
        }
    }).catch(function() {
        alert('获取章节信息失败: 未知错误');
    });
}
</script>
@endsection
