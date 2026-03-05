@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 id="learningTitle">学习课程</h4>
                    <div>
                        <a id="learningBackLink" href="/courses" class="btn btn-secondary">返回课程</a>
                    </div>
                </div>

                <div class="card-body">
                    <div id="learningLoading" class="text-muted mb-3">加载中...</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    课程大纲
                                </a>
                                <div id="learningOutline"></div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header">
                                    <h5 id="learningCourseTitle">课程内容</h5>
                                </div>
                                <div class="card-body" id="learningContent"></div>
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

function escapeHtml(text) {
    return String(text || '').replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
    });
}

function iconByType(type, child) {
    if (type === 'video') return 'video-camera';
    if (type === 'quiz') return 'question-circle';
    if (type === 'assignment') return 'file-text';
    if (type === 'reading') return 'book';
    return child ? 'file' : 'folder';
}

function parseCourseId() {
    var parts = window.location.pathname.split('/').filter(Boolean);
    for (var i = 0; i < parts.length; i++) {
        if ((parts[i] === 'courses' || parts[i] === 'learning') && parts[i + 1] && !isNaN(Number(parts[i + 1]))) {
            return Number(parts[i + 1]);
        }
    }
    return 0;
}

function renderOutlineItems(items, child) {
    if (!Array.isArray(items) || !items.length) return '';
    var html = '';
    items.forEach(function(item) {
        var id = Number(item.id || 0);
        html += '<a href="#item-' + id + '" class="list-group-item list-group-item-action ' + (child ? 'ml-4' : 'ml-3') + '">'
            + '<i class="fa fa-' + iconByType(item.item_type, child) + '"></i> '
            + escapeHtml(item.title || '')
            + '</a>';
        if (Array.isArray(item.children) && item.children.length) {
            html += renderOutlineItems(item.children, true);
        }
    });
    return html;
}

function renderLearningItem(item, child) {
    var id = Number(item.id || 0);
    var description = item.description ? '<p class="text-muted">' + escapeHtml(item.description) + '</p>' : '';
    var externalUrl = item.external_url
        ? '<a href="' + escapeHtml(item.external_url) + '" target="_blank" class="btn btn-sm btn-outline-primary">'
            + '<i class="fa fa-external-link"></i> 访问内容</a>'
        : '';

    return ''
        + '<div id="item-' + id + '" class="mb-4 ' + (child ? 'ml-4' : '') + '">'
        + '<h6><i class="fa fa-' + iconByType(item.item_type, child) + '"></i> ' + escapeHtml(item.title || '') + '</h6>'
        + description
        + externalUrl
        + '<div class="mt-2">'
        + '<button type="button" class="btn btn-sm btn-success mr-2" onclick="markCompleted(' + id + ')">标记为已完成</button>'
        + '<button type="button" class="btn btn-sm btn-info" onclick="addLearningNote(' + id + ')">添加笔记</button>'
        + '</div>'
        + '</div>';
}

function renderContentItems(items, child) {
    if (!Array.isArray(items) || !items.length) return '';
    var html = '';
    items.forEach(function(item) {
        html += renderLearningItem(item, child);
        if (Array.isArray(item.children) && item.children.length) {
            html += renderContentItems(item.children, true);
        }
    });
    return html;
}

function loadLearningData() {
    var loadingEl = document.getElementById('learningLoading');
    if (!apiRequest) {
        if (loadingEl) loadingEl.textContent = 'API客户端未初始化';
        return;
    }

    var courseId = parseCourseId();
    if (!courseId) {
        if (loadingEl) loadingEl.textContent = '课程ID错误';
        return;
    }

    Promise.all([
        apiRequest('GET', '/courses/' + courseId, {}),
        apiRequest('GET', '/courses/' + courseId + '/items', {})
    ]).then(function(results) {
        var courseResp = results[0];
        var itemsResp = results[1];

        if (!courseResp || courseResp.code !== 9999 || !courseResp.result || !courseResp.result.course) {
            throw new Error((courseResp && courseResp.msg) || '课程加载失败');
        }
        if (!itemsResp || itemsResp.code !== 9999 || !itemsResp.result) {
            throw new Error((itemsResp && itemsResp.msg) || '课程内容加载失败');
        }

        var course = courseResp.result.course;
        var structure = Array.isArray(itemsResp.result.structure) ? itemsResp.result.structure : [];

        var titleText = escapeHtml(course.title || '学习课程');
        document.getElementById('learningTitle').textContent = titleText;
        document.getElementById('learningCourseTitle').textContent = titleText;
        document.getElementById('learningBackLink').setAttribute('href', '/courses/' + courseId);

        document.getElementById('learningOutline').innerHTML = structure.length
            ? renderOutlineItems(structure, false)
            : '<div class="list-group-item text-muted">暂无课程大纲</div>';

        document.getElementById('learningContent').innerHTML = structure.length
            ? renderContentItems(structure, false)
            : '<p class="text-muted mb-0">暂无课程内容</p>';

        if (loadingEl) loadingEl.style.display = 'none';
    }).catch(function(err) {
        if (loadingEl) {
            loadingEl.textContent = err && err.message ? err.message : '加载失败';
        }
    });
}

function markCompleted(itemId) {
    alert('课程进度功能开发中，章节ID: ' + itemId);
}

function addLearningNote(itemId) {
    window.open('/notes?source_type=4&source_id=' + itemId, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    loadLearningData();
});
</script>
@endsection
