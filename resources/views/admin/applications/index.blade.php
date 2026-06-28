<div class="app-admin-shell">
    <div class="app-admin-hero">
        <div>
            <div class="app-admin-kicker">Applications</div>
            <h2>应用卡片工作台</h2>
            <p>先选应用，再进入左侧文件树和右侧编辑区。</p>
        </div>
        <div class="app-admin-hero-actions">
            <input id="appSearch" class="app-input" type="text" placeholder="搜索应用名或 slug">
            <button id="createAppBtn" class="app-btn app-btn-primary" type="button">新建应用</button>
        </div>
    </div>

    <div class="app-admin-grid">
        @foreach($applications as $application)
            <div class="app-card" data-name="{{ strtolower($application['name']) }}" data-slug="{{ strtolower($application['slug']) }}">
                <div class="app-card-top">
                    <div>
                        <div class="app-card-title">{{ $application['name'] }}</div>
                        <div class="app-card-subtitle">/{{ $application['slug'] }}</div>
                    </div>
                    <span class="app-status app-status-{{ $application['status'] }}">{{ $statusOptions[$application['status']] ?? '未知' }}</span>
                </div>
                <div class="app-card-desc">{{ $application['description'] ?: '暂无描述' }}</div>
                <div class="app-card-meta">
                    <span>{{ $application['codes_count'] }} 个文件</span>
                    <span>{{ $application['updated_at'] ?: '暂无更新时间' }}</span>
                </div>
                <div class="app-card-actions">
                    <a class="app-btn app-btn-primary" href="/admin/applications/{{ $application['id'] }}">进入工作台</a>
                    @if($application['preview_url'])
                        <a class="app-btn app-btn-light" href="{{ $application['preview_url'] }}" target="_blank">预览</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div id="appEmptyState" class="app-empty" style="display:none;">没有匹配的应用</div>

    <div id="createAppModal" class="app-modal" style="display:none;">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <h3>新建应用</h3>
                <button id="closeCreateAppModal" class="app-modal-close" type="button">&times;</button>
            </div>
            <div class="app-modal-body">
                <div class="app-form-grid">
                    <label>
                        <span>名称</span>
                        <input id="createAppName" class="app-input" type="text" placeholder="例如：日报页面">
                    </label>
                    <label>
                        <span>Slug</span>
                        <input id="createAppSlug" class="app-input" type="text" placeholder="例如：daily-page">
                    </label>
                    <label class="app-form-full">
                        <span>描述</span>
                        <textarea id="createAppDescription" class="app-textarea" rows="4" placeholder="这个应用用于什么"></textarea>
                    </label>
                    <label>
                        <span>状态</span>
                        <select id="createAppStatus" class="app-input">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="app-modal-actions">
                    <button id="submitCreateApp" class="app-btn app-btn-primary" type="button">创建并进入</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .app-admin-shell {
        --app-bg: #f6f9fc;
        --app-panel: rgba(255, 255, 255, 0.92);
        --app-panel-strong: #ffffff;
        --app-border: #dce7ef;
        --app-border-strong: #c8d9e6;
        --app-text: #1f2d3d;
        --app-muted: #64748b;
        --app-primary: #4aa3a2;
        --app-primary-deep: #3f8f90;
        --app-secondary: #5f9ae0;
        --app-warm: #eef6f3;
        padding: 8px 4px 24px;
        color: var(--app-text);
    }
    .app-admin-hero {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-end;
        padding: 24px 28px;
        border-radius: 20px;
        margin-bottom: 24px;
        background:
            radial-gradient(circle at top right, rgba(95, 154, 224, 0.16), transparent 35%),
            radial-gradient(circle at left bottom, rgba(74, 163, 162, 0.12), transparent 34%),
            linear-gradient(135deg, #fbfdff 0%, #f2f8fb 55%, #edf6f3 100%);
        border: 1px solid var(--app-border);
        box-shadow: 0 16px 40px rgba(80, 109, 138, 0.08);
    }
    .app-admin-kicker { font-size: 12px; letter-spacing: .12em; text-transform: uppercase; color: var(--app-secondary); margin-bottom: 10px; }
    .app-admin-hero h2 { margin: 0 0 8px; font-size: 28px; color: var(--app-text); }
    .app-admin-hero p { margin: 0; color: var(--app-muted); }
    .app-admin-hero-actions { display: flex; gap: 12px; align-items: center; min-width: 320px; }
    .app-admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 18px;
    }
    .app-card {
        background: var(--app-panel);
        border: 1px solid var(--app-border);
        border-radius: 18px;
        padding: 20px;
        backdrop-filter: blur(8px);
        box-shadow: 0 14px 32px rgba(93, 122, 147, 0.08);
        display: flex;
        flex-direction: column;
        gap: 16px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .app-card:hover {
        transform: translateY(-4px);
        border-color: var(--app-border-strong);
        box-shadow: 0 20px 44px rgba(93, 122, 147, 0.14);
    }
    .app-card-top { display: flex; justify-content: space-between; gap: 12px; }
    .app-card-title { font-size: 20px; font-weight: 700; color: var(--app-text); }
    .app-card-subtitle { margin-top: 4px; color: var(--app-muted); font-size: 13px; }
    .app-card-desc { min-height: 44px; color: #516173; line-height: 1.6; }
    .app-card-meta { display: flex; justify-content: space-between; gap: 10px; color: var(--app-muted); font-size: 12px; }
    .app-card-actions { display: flex; gap: 10px; }
    .app-status {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .app-status-1 { background: #eef6ff; color: #4f81c7; }
    .app-status-2 { background: #e7f5f1; color: #2f857f; }
    .app-status-3 { background: #eef2f5; color: #667788; }
    .app-status-4 { background: #fdeeee; color: #b25a67; }
    .app-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
    }
    .app-btn-primary {
        background: linear-gradient(135deg, var(--app-primary), var(--app-secondary));
        color: #fff;
        box-shadow: 0 10px 24px rgba(79, 145, 191, 0.22);
    }
    .app-btn-light {
        background: #f3f8fb;
        color: #4d6782;
        border: 1px solid var(--app-border);
    }
    .app-input, .app-textarea {
        width: 100%;
        border: 1px solid var(--app-border);
        border-radius: 12px;
        padding: 11px 13px;
        background: #fcfeff;
        color: var(--app-text);
    }
    .app-empty {
        margin-top: 18px;
        padding: 26px;
        border-radius: 16px;
        text-align: center;
        background: var(--app-panel-strong);
        border: 1px dashed var(--app-border-strong);
        color: var(--app-muted);
    }
    .app-modal {
        position: fixed;
        inset: 0;
        background: rgba(58, 76, 97, 0.28);
        z-index: 2000;
        padding: 40px 16px;
    }
    .app-modal-card {
        max-width: 720px;
        margin: 0 auto;
        background: #fbfdff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--app-border);
        box-shadow: 0 24px 60px rgba(67, 88, 112, 0.2);
    }
    .app-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 22px;
        background: linear-gradient(135deg, #f6fbff 0%, #eef7f4 100%);
        border-bottom: 1px solid var(--app-border);
    }
    .app-modal-header h3 { margin: 0; font-size: 20px; }
    .app-modal-close { border: 0; background: transparent; font-size: 28px; cursor: pointer; color: #6c8199; }
    .app-modal-body { padding: 22px; }
    .app-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .app-form-grid label { display: flex; flex-direction: column; gap: 8px; color: #4f6478; font-weight: 600; }
    .app-form-full { grid-column: 1 / -1; }
    .app-modal-actions { margin-top: 18px; display: flex; justify-content: flex-end; }
    @media (max-width: 900px) {
        .app-admin-hero { flex-direction: column; align-items: stretch; }
        .app-admin-hero-actions { min-width: 0; }
        .app-form-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
    (function () {
        var $search = $('#appSearch');
        var $cards = $('.app-card');
        var $empty = $('#appEmptyState');
        var $modal = $('#createAppModal');

        function toggleEmpty() {
            var visibleCount = $('.app-card:visible').length;
            $empty.toggle(visibleCount === 0);
        }

        $search.on('input', function () {
            var keyword = $.trim($(this).val()).toLowerCase();
            $cards.each(function () {
                var $card = $(this);
                var matched = !keyword
                    || $card.data('name').indexOf(keyword) !== -1
                    || $card.data('slug').indexOf(keyword) !== -1;
                $card.toggle(matched);
            });
            toggleEmpty();
        });

        $('#createAppBtn').on('click', function () {
            $modal.show();
        });

        $('#closeCreateAppModal').on('click', function () {
            $modal.hide();
        });

        $modal.on('click', function (event) {
            if (event.target.id === 'createAppModal') {
                $modal.hide();
            }
        });

        $('#submitCreateApp').on('click', function () {
            $.ajax({
                url: '/admin/applications',
                method: 'POST',
                data: {
                    name: $('#createAppName').val(),
                    slug: $('#createAppSlug').val(),
                    description: $('#createAppDescription').val(),
                    status: $('#createAppStatus').val(),
                    _token: '{{ csrf_token() }}'
                }
            }).done(function (response) {
                if (Number(response.code) === 9999 && response.data.workspace_url) {
                    window.location.href = response.data.workspace_url;
                    return;
                }
                alert(response.message || '创建失败');
            }).fail(function (xhr) {
                var message = '创建失败';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            });
        });
    })();
</script>
