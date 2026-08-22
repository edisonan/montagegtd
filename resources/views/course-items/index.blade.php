@extends('layouts.app')

@section('content')
@include('components.course-item-modal')
@include('components.course-quiz-editor-modal')
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
            + (item.item_type === 'quiz' ? '<button class="btn btn-sm" style="background:#8b5cf6;color:#fff;cursor:pointer" onclick="openQuizEditorModal(' + Number(item.id || 0) + ')">测验</button> ' : '')
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

// 章节的新增/编辑统一走组件（course-item-modal.blade.php）中的 openCourseItemModal / submitCourseItemForm
// 这里不再重复定义同名函数，避免覆盖组件实现导致弹窗失效（旧版误用 Bootstrap .modal()）

function deleteItem(id) {
    if (!apiRequest) {
        Swal.fire('提示', 'API客户端未初始化', 'warning');
        return;
    }
    Swal.fire({
        title: '确定要删除这个章节吗？',
        text: '删除后不可恢复，其子章节也将无法通过课程查看',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '删除',
        cancelButtonText: '取消',
        confirmButtonColor: '#dc2626'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        apiRequest('DELETE', '/course-items/' + id, {}).then(function(response) {
            if (response && response.code == 9999) {
                Swal.fire('已删除', (response.msg || '删除成功'), 'success').then(function() {
                    loadPageData();
                });
                return;
            }
            Swal.fire('删除失败', (response && response.msg) ? response.msg : '未知错误', 'error');
        }).catch(function() {
            Swal.fire('删除失败', '网络错误，请稍后重试', 'error');
        });
    });
}

function editItem(id) {
    if (!apiRequest) {
        Swal.fire('提示', 'API客户端未初始化', 'warning');
        return;
    }
    apiRequest('GET', '/course-items/' + id, {}).then(function(response) {
        if (response && response.code == 9999 && response.result && response.result.course_item) {
            openCourseItemModal(COURSE_ID, response.result.course_item);
            return;
        }
        Swal.fire('获取章节信息失败', (response && response.msg) ? response.msg : '未知错误', 'error');
    }).catch(function() {
        Swal.fire('获取章节信息失败', '网络错误，请稍后重试', 'error');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // 供组件在保存成功后刷新整棵树，替代 location.reload
    window.refreshCourseStructure = loadPageData;

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
