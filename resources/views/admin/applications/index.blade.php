<div class="app-studio">
    <div class="studio-hero">
        <div class="studio-hero-copy">
            <div class="studio-kicker">App Studio</div>
            <h2>应用管理台</h2>
            <p>用卡片看整体状态，用工作台编辑文件、预览页面和回滚历史。</p>
        </div>
        <div class="studio-hero-actions">
            <div class="studio-search">
                <span>⌕</span>
                <input id="appSearch" type="text" placeholder="搜索应用名、slug 或描述">
            </div>
            <button id="createAppBtn" class="studio-btn studio-btn-primary" type="button">+ 新建应用</button>
        </div>
    </div>

    <div class="studio-stats">
        <div class="studio-stat">
            <span>应用总数</span>
            <strong>{{ $stats['total'] ?? 0 }}</strong>
        </div>
        <div class="studio-stat">
            <span>运行中</span>
            <strong>{{ $stats['running'] ?? 0 }}</strong>
        </div>
        <div class="studio-stat">
            <span>开发中</span>
            <strong>{{ $stats['draft'] ?? 0 }}</strong>
        </div>
        <div class="studio-stat">
            <span>Code 文件</span>
            <strong>{{ $stats['files'] ?? 0 }}</strong>
        </div>
        <div class="studio-stat">
            <span>有入口文件</span>
            <strong>{{ $stats['with_entry'] ?? 0 }}</strong>
        </div>
    </div>

    <div class="studio-filterbar">
        <button class="studio-filter is-active" type="button" data-status="all">全部</button>
        @foreach($statusOptions as $value => $label)
            <button class="studio-filter" type="button" data-status="{{ $value }}">{{ $label }}</button>
        @endforeach
        <button class="studio-filter" type="button" data-status="entry">有入口</button>
        <button class="studio-filter" type="button" data-status="no-entry">缺入口</button>
    </div>

    <div class="studio-grid">
        @foreach($applications as $application)
            <article class="studio-card"
                     data-status="{{ $application['status'] }}"
                     data-entry="{{ $application['has_entry'] ? 1 : 0 }}"
                     data-keyword="{{ strtolower($application['name'] . ' ' . $application['slug'] . ' ' . $application['description']) }}">
                <div class="studio-card-head">
                    <div class="studio-app-mark">{{ strtoupper(substr($application['slug'] ?: $application['name'], 0, 1)) }}</div>
                    <div class="studio-card-titlebox">
                        <h3>{{ $application['name'] }}</h3>
                        <div>/{{ $application['slug'] }}</div>
                    </div>
                    <span class="studio-status studio-status-{{ $application['status'] }}">{{ $statusOptions[$application['status']] ?? '未知' }}</span>
                </div>

                <p class="studio-card-desc">{{ $application['description'] ?: '暂无描述。可以在工作台里补充应用说明，方便后续维护。' }}</p>

                <div class="studio-health">
                    <div class="studio-health-item {{ $application['has_entry'] ? 'ok' : 'warn' }}">
                        <span>{{ $application['has_entry'] ? '✓' : '!' }}</span>
                        <div>
                            <strong>{{ $application['has_entry'] ? '入口正常' : '缺少入口' }}</strong>
                            <em>{{ $application['entry_path'] ?: '建议创建 index.html' }}</em>
                        </div>
                    </div>
                    <div class="studio-health-item">
                        <span>≡</span>
                        <div>
                            <strong>{{ $application['codes_count'] }} 个文件</strong>
                            <em>{{ $application['updated_at'] ?: '暂无更新时间' }}</em>
                        </div>
                    </div>
                </div>

                <div class="studio-card-actions">
                    <a class="studio-btn studio-btn-primary" href="/admin/applications/{{ $application['id'] }}">打开工作台</a>
                    @if($application['preview_url'])
                        <a class="studio-btn studio-btn-ghost" href="{{ $application['preview_url'] }}" target="_blank">预览</a>
                        <button class="studio-btn studio-btn-ghost copy-preview" type="button" data-url="{{ $application['preview_url'] }}">复制链接</button>
                    @else
                        <span class="studio-btn studio-btn-disabled">暂无预览</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div id="appEmptyState" class="studio-empty" style="display:none;">
        没有匹配的应用。可以换个关键词，或者新建一个应用。
    </div>

    <div id="createAppModal" class="studio-modal" style="display:none;">
        <div class="studio-modal-card">
            <div class="studio-modal-head">
                <div>
                    <div class="studio-kicker">New App</div>
                    <h3>新建应用</h3>
                </div>
                <button id="closeCreateAppModal" class="studio-modal-close" type="button">&times;</button>
            </div>
            <div class="studio-modal-body">
                <div class="studio-form-grid">
                    <label>
                        <span>名称</span>
                        <input id="createAppName" class="studio-input" type="text" placeholder="例如：日报页面">
                    </label>
                    <label>
                        <span>Slug</span>
                        <input id="createAppSlug" class="studio-input" type="text" placeholder="例如：daily-page">
                    </label>
                    <label class="studio-form-full">
                        <span>描述</span>
                        <textarea id="createAppDescription" class="studio-textarea" rows="4" placeholder="这个应用解决什么问题"></textarea>
                    </label>
                    <label>
                        <span>状态</span>
                        <select id="createAppStatus" class="studio-input">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="studio-modal-actions">
                    <button id="submitCreateApp" class="studio-btn studio-btn-primary" type="button">创建并进入工作台</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper .content { padding-top: 12px; }
    .app-studio {
        --studio-bg: #f4f7fb;
        --studio-panel: #ffffff;
        --studio-text: #172033;
        --studio-muted: #69758a;
        --studio-border: #dce4ef;
        --studio-primary: #4867f0;
        --studio-cyan: #24b3b0;
        --studio-green: #18a058;
        --studio-orange: #d9822b;
        --studio-red: #d64550;
        color: var(--studio-text);
        padding: 2px 0 28px;
    }
    .studio-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 24px;
        padding: 28px;
        border-radius: 26px;
        background:
            radial-gradient(circle at 8% 10%, rgba(36,179,176,.18), transparent 28%),
            radial-gradient(circle at 92% 10%, rgba(72,103,240,.18), transparent 32%),
            linear-gradient(135deg, #fff 0%, #f6f9ff 56%, #eff8f7 100%);
        border: 1px solid var(--studio-border);
        box-shadow: 0 22px 50px rgba(58, 75, 108, .09);
    }
    .studio-kicker {
        color: var(--studio-primary);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }
    .studio-hero h2 { margin: 8px 0; font-size: 34px; font-weight: 800; }
    .studio-hero p { margin: 0; color: var(--studio-muted); font-size: 15px; }
    .studio-hero-actions { display: flex; gap: 12px; align-items: center; min-width: 460px; }
    .studio-search {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 14px;
        height: 44px;
        background: rgba(255,255,255,.82);
        border: 1px solid var(--studio-border);
        border-radius: 14px;
    }
    .studio-search input { width: 100%; border: 0; outline: 0; background: transparent; }
    .studio-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 10px 14px;
        border-radius: 13px;
        border: 0;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        white-space: nowrap;
    }
    .studio-btn:hover { transform: translateY(-1px); text-decoration: none; }
    .studio-btn-primary {
        background: linear-gradient(135deg, var(--studio-primary), var(--studio-cyan));
        color: #fff;
        box-shadow: 0 12px 26px rgba(64, 103, 220, .22);
    }
    .studio-btn-ghost {
        background: #f7f9fd;
        color: #40516b;
        border: 1px solid var(--studio-border);
    }
    .studio-btn-disabled {
        background: #f1f4f8;
        color: #9aa5b7;
        cursor: default;
    }
    .studio-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin: 18px 0;
    }
    .studio-stat {
        padding: 18px;
        border-radius: 20px;
        background: var(--studio-panel);
        border: 1px solid var(--studio-border);
        box-shadow: 0 12px 28px rgba(58, 75, 108, .06);
    }
    .studio-stat span { display: block; color: var(--studio-muted); font-size: 12px; font-weight: 800; }
    .studio-stat strong { display: block; margin-top: 8px; font-size: 28px; line-height: 1; }
    .studio-filterbar {
        display: flex;
        gap: 10px;
        align-items: center;
        overflow-x: auto;
        margin-bottom: 18px;
    }
    .studio-filter {
        border: 1px solid var(--studio-border);
        background: #fff;
        color: var(--studio-muted);
        border-radius: 999px;
        padding: 9px 14px;
        font-weight: 800;
        white-space: nowrap;
    }
    .studio-filter.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--studio-primary), var(--studio-cyan));
    }
    .studio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
        gap: 18px;
    }
    .studio-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
        min-height: 286px;
        border-radius: 24px;
        background: rgba(255,255,255,.96);
        border: 1px solid var(--studio-border);
        box-shadow: 0 16px 36px rgba(58, 75, 108, .08);
        overflow: hidden;
    }
    .studio-card:before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: linear-gradient(90deg, var(--studio-primary), var(--studio-cyan));
    }
    .studio-card-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .studio-app-mark {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: linear-gradient(135deg, #243b80, var(--studio-primary));
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 900;
        flex: 0 0 auto;
    }
    .studio-card-titlebox { min-width: 0; flex: 1; }
    .studio-card-titlebox h3 {
        margin: 0 0 4px;
        font-size: 20px;
        font-weight: 900;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .studio-card-titlebox div { color: var(--studio-muted); font-size: 13px; }
    .studio-status {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }
    .studio-status-1 { background: #fff5df; color: #b76a17; }
    .studio-status-2 { background: #e8f8ef; color: #187a43; }
    .studio-status-3 { background: #eef2f7; color: #617089; }
    .studio-status-4 { background: #fdebed; color: #b42332; }
    .studio-card-desc {
        min-height: 48px;
        margin: 0;
        color: #56657a;
        line-height: 1.6;
    }
    .studio-health {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: auto;
    }
    .studio-health-item {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 12px;
        border-radius: 16px;
        background: #f7f9fd;
        border: 1px solid #e7edf5;
    }
    .studio-health-item > span {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: #fff;
        background: #8a98ad;
        font-weight: 900;
        flex: 0 0 auto;
    }
    .studio-health-item.ok > span { background: var(--studio-green); }
    .studio-health-item.warn > span { background: var(--studio-orange); }
    .studio-health-item strong { display: block; font-size: 13px; }
    .studio-health-item em {
        display: block;
        max-width: 130px;
        margin-top: 2px;
        color: var(--studio-muted);
        font-size: 12px;
        font-style: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .studio-card-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 2px;
    }
    .studio-empty {
        margin-top: 18px;
        padding: 28px;
        border-radius: 18px;
        background: #fff;
        border: 1px dashed #bbc7d8;
        color: var(--studio-muted);
        text-align: center;
    }
    .studio-modal {
        position: fixed;
        inset: 0;
        z-index: 2200;
        padding: 46px 16px;
        background: rgba(28, 39, 58, .42);
    }
    .studio-modal-card {
        max-width: 760px;
        margin: 0 auto;
        border-radius: 24px;
        background: #fff;
        border: 1px solid var(--studio-border);
        box-shadow: 0 26px 70px rgba(18, 28, 45, .22);
        overflow: hidden;
    }
    .studio-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--studio-border);
        background: linear-gradient(135deg, #fff, #f5f8ff);
    }
    .studio-modal-head h3 { margin: 5px 0 0; font-weight: 900; }
    .studio-modal-close { border: 0; background: transparent; color: #6f7d92; font-size: 30px; cursor: pointer; }
    .studio-modal-body { padding: 22px 24px 24px; }
    .studio-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .studio-form-grid label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: #49586d;
        font-weight: 800;
    }
    .studio-form-full { grid-column: 1 / -1; }
    .studio-input, .studio-textarea {
        width: 100%;
        border: 1px solid var(--studio-border);
        border-radius: 14px;
        background: #fbfcff;
        padding: 11px 13px;
        outline: none;
    }
    .studio-input:focus, .studio-textarea:focus {
        border-color: #8aa0ff;
        box-shadow: 0 0 0 4px rgba(72,103,240,.1);
    }
    .studio-modal-actions { display: flex; justify-content: flex-end; margin-top: 18px; }
    @media (max-width: 980px) {
        .studio-hero { align-items: stretch; flex-direction: column; }
        .studio-hero-actions { min-width: 0; flex-direction: column; align-items: stretch; }
        .studio-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .studio-health { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .studio-grid, .studio-stats, .studio-form-grid { grid-template-columns: 1fr; }
        .studio-hero { padding: 22px; }
        .studio-hero h2 { font-size: 28px; }
    }
</style>

<script>
    (function () {
        var activeStatus = 'all';
        var $cards = $('.studio-card');
        var $empty = $('#appEmptyState');
        var $modal = $('#createAppModal');

        function applyFilters() {
            var keyword = $.trim($('#appSearch').val()).toLowerCase();
            var visibleCount = 0;

            $cards.each(function () {
                var $card = $(this);
                var status = String($card.data('status'));
                var hasEntry = Number($card.data('entry')) === 1;
                var text = String($card.data('keyword') || '');
                var matched = true;

                if (keyword && text.indexOf(keyword) === -1) {
                    matched = false;
                }
                if (activeStatus !== 'all') {
                    if (activeStatus === 'entry') {
                        matched = matched && hasEntry;
                    } else if (activeStatus === 'no-entry') {
                        matched = matched && !hasEntry;
                    } else {
                        matched = matched && status === activeStatus;
                    }
                }

                $card.toggle(matched);
                if (matched) {
                    visibleCount += 1;
                }
            });

            $empty.toggle(visibleCount === 0);
        }

        $('#appSearch').on('input', applyFilters);

        $('.studio-filter').on('click', function () {
            activeStatus = String($(this).data('status'));
            $('.studio-filter').removeClass('is-active');
            $(this).addClass('is-active');
            applyFilters();
        });

        $('.copy-preview').on('click', function () {
            var url = $(this).data('url');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url);
                $(this).text('已复制');
                return;
            }
            window.prompt('复制预览地址', url);
        });

        $('#createAppBtn').on('click', function () {
            $modal.show();
            $('#createAppName').focus();
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
