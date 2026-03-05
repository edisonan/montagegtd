@extends('layouts.app')

@section('content')
@include('components.course-item-modal')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 id="courseTitleText">课程章节管理</h4>
                    <div>
                        <a id="courseBackLink" href="/courses" class="btn btn-secondary">返回课程</a>
                    </div>
                </div>

                <div class="card-body">
                    <div id="courseItemLoading" class="text-muted mb-3">加载中...</div>
                    <div class="row">
                        <div class="col-md-8">
                            <h5>课程章节结构</h5>
                            <div id="courseStructureWrap"></div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header"><h5>操作</h5></div>
                                <div class="card-body">
                                    <button class="btn btn-primary btn-block" id="addCourseItemBtn">添加章节</button>
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

function getCourseIdFromPath() {
    var parts = window.location.pathname.split('/').filter(Boolean);
    for (var i = 0; i < parts.length; i++) {
        if (parts[i] === 'courses' && parts[i + 1]) {
            return Number(parts[i + 1] || 0);
        }
    }
    return 0;
}

var COURSE_ID = getCourseIdFromPath();

function escapeHtml(text) {
    return String(text || '').replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
    });
}

function iconByType(type) {
    if (type === 'video') return 'video-camera';
    if (type === 'quiz') return 'question-circle';
    if (type === 'assignment') return 'file-text';
    if (type === 'reading') return 'book';
    if (type === 'chapter' || type === 'module') return 'folder';
    return 'file';
}

function renderStructureItems(items, child) {
    if (!Array.isArray(items) || !items.length) return '';
    var html = '';
    items.forEach(function(item) {
        var sub = '';
        if (Array.isArray(item.children) && item.children.length) {
            sub = '<div class="ml-4 mt-2">' + renderStructureItems(item.children, true) + '</div>';
        }
        html += ''
            + '<div class="list-group-item ' + (child ? 'p-2' : '') + '">'
            + '<div class="d-flex justify-content-between align-items-center">'
            + '<div><i class="fa fa-' + iconByType(item.item_type) + '"></i> '
            + (child ? '' : '<strong>') + escapeHtml(item.title || '') + (child ? '' : '</strong>')
            + (item.duration ? ' <small class="text-muted">(' + Number(item.duration) + ' 分钟)</small>' : '')
            + '</div>'
            + '<div>'
            + '<span class="badge badge-secondary mr-2">' + escapeHtml(String(item.item_type || '')) + '</span>'
            + '<button class="btn btn-sm btn-primary" onclick="editItem(' + Number(item.id || 0) + ')">编辑</button> '
            + '<button class="btn btn-sm btn-danger" onclick="deleteItem(' + Number(item.id || 0) + ')">删除</button>'
            + '</div></div>'
            + (item.description && !child ? '<p class="mb-0 mt-2 text-muted">' + escapeHtml(item.description).slice(0, 100) + '</p>' : '')
            + sub
            + '</div>';
    });
    return html;
}

function renderStructure(items) {
    var wrap = document.getElementById('courseStructureWrap');
    if (!Array.isArray(items) || !items.length) {
        wrap.innerHTML = '<p class="text-muted">暂无课程内容</p>';
        return;
    }
    wrap.innerHTML = '<div class="list-group mb-4">' + renderStructureItems(items, false) + '</div>';
}

function loadPageData() {
    if (!apiRequest || !COURSE_ID) {
        document.getElementById('courseItemLoading').textContent = 'API客户端未初始化或课程ID错误';
        return;
    }

    Promise.all([
        apiRequest('GET', '/courses/' + COURSE_ID, {}),
        apiRequest('GET', '/course-items/structure/' + COURSE_ID, {})
    ]).then(function(results) {
        var cResp = results[0], sResp = results[1];
        if (!cResp || cResp.code !== 9999 || !cResp.result || !cResp.result.course) {
            throw new Error((cResp && cResp.msg) || '课程加载失败');
        }
        var course = cResp.result.course;
        document.getElementById('courseTitleText').textContent = (course.title || '课程') + ' - 章节管理';
        document.getElementById('courseBackLink').setAttribute('href', '/courses/' + Number(course.id || COURSE_ID));

        if (!sResp || sResp.code !== 9999) {
            throw new Error((sResp && sResp.msg) || '章节结构加载失败');
        }
        var structure = sResp.result || [];
        renderStructure(structure);
        document.getElementById('courseItemLoading').style.display = 'none';
    }).catch(function(err) {
        document.getElementById('courseItemLoading').textContent = err && err.message ? err.message : '加载失败';
    });
}

// 提交课程章节表单
function submitCourseItemForm() {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }

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
            loadPageData();
            return;
        }
        $('#courseItemErrorList').empty();
        $('#courseItemErrorList').append('<li>' + (response.msg || '操作失败') + '</li>');
        $('#courseItemErrors').show();
    }).catch(function() {
        $('#courseItemErrorList').empty();
        $('#courseItemErrorList').append('<li>操作失败，请稍后重试</li>');
        $('#courseItemErrors').show();
    });
}

function loadCourseStructure(courseId, excludeItemId, currentParentId) {
    if (!apiRequest) return;
    apiRequest('GET', '/course-items/structure/' + courseId, {}).then(function(response) {
        if(response.code != 9999) return;
        var selectElement = $('#parent_id_modal');
        selectElement.empty();
        selectElement.append('<option value="">无父级（顶级章节）</option>');

        function buildOptions(items, level) {
            var prefix = level > 0 ? '--'.repeat(level) + ' ' : '';
            $.each(items, function(index, item) {
                if(excludeItemId && item.id == excludeItemId) return true;
                selectElement.append('<option value="' + item.id + '">' + prefix + item.title + '</option>');
                if(item.children && item.children.length > 0) buildOptions(item.children, level + 1);
            });
        }

        buildOptions(response.result || [], 0);
        if(currentParentId) selectElement.val(currentParentId);
    });
}

function openCourseItemModal(courseId, itemData) {
    $('#courseItemErrors').hide();
    $('#courseItemErrorList').empty();
    $('#course_id_modal').val(courseId);

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
        loadCourseStructure(courseId, itemData.id, itemData.parent_id);
    } else {
        $('#courseItemModalLabel').text('添加章节');
        $('#item_id_modal').val('');
        $('#title_modal').val('');
        $('#parent_id_modal').val('');
        $('#item_type_modal').val('chapter');
        $('#duration_modal').val(0);
        $('#external_url_modal').val('');
        $('#description_modal').val('');
        $('#order_index_modal').val(0);
        loadCourseStructure(courseId, null, null);
    }

    $('#courseItemModal').modal('show');
}

function cancelEdit() {
    $('#courseItemModal').modal('hide');
}

function deleteItem(id) {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }
    if (!confirm('确定要删除这个章节吗？')) return;
    apiRequest('DELETE', '/course-items/' + id, {}).then(function(response) {
        if(response.code == 9999) {
            alert(response.msg || '删除成功');
            loadPageData();
            return;
        }
        alert('删除失败: ' + (response.msg || '未知错误'));
    }).catch(function() {
        alert('删除失败: 未知错误');
    });
}

function editItem(id) {
    if (!apiRequest) {
        alert('API客户端未初始化');
        return;
    }
    apiRequest('GET', '/course-items/' + id, {}).then(function(response) {
        if(response.code == 9999) {
            var item = response.result.course_item;
            openCourseItemModal(COURSE_ID, item);
            return;
        }
        alert('获取章节信息失败: ' + (response.msg || '未知错误'));
    }).catch(function() {
        alert('获取章节信息失败: 未知错误');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var addBtn = document.getElementById('addCourseItemBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            openCourseItemModal(COURSE_ID);
        });
    }
    loadPageData();
});
</script>
@endsection
