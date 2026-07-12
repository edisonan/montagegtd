<div class="workspace-shell">
    <div class="workspace-topbar">
        <div class="workspace-titleblock">
            <div class="workspace-kicker">Workspace</div>
            <h2 id="appTitle">{{ $application['name'] }}</h2>
            <div class="workspace-slug" id="appSlug">/{{ $application['slug'] }}</div>
        </div>
        <div class="workspace-top-actions">
            <div class="workspace-app-glance">
                <span class="workspace-glance-pill">{{ count($files) }} 个文件</span>
                <span class="workspace-glance-pill workspace-glance-pill-accent">实时预览</span>
            </div>
            <button id="quickSaveBtn" class="workspace-btn workspace-btn-primary" type="button">保存</button>
            <button id="quickPreviewBtn" class="workspace-btn workspace-btn-light" type="button">预览</button>
            <button id="toggleMetaBtn" class="workspace-btn workspace-btn-light" type="button">基础信息</button>
            <button id="toggleDataTablesBtn" class="workspace-btn workspace-btn-light" type="button">数据表</button>
            <a href="/admin/applications" class="workspace-btn workspace-btn-light">返回应用列表</a>
        </div>
    </div>

    <div id="metaPanel" class="workspace-panel workspace-meta-panel" style="display:none;">
        <div class="workspace-panel-header">
            <h3>应用基础信息</h3>
            <button id="saveMetaBtn" class="workspace-btn workspace-btn-primary" type="button">保存应用信息</button>
        </div>
        <div class="workspace-form-grid">
            <label>
                <span>名称</span>
                <input id="metaName" class="workspace-input" type="text" value="{{ $application['name'] }}">
            </label>
            <label>
                <span>Slug</span>
                <input id="metaSlug" class="workspace-input" type="text" value="{{ $application['slug'] }}">
            </label>
            <label class="workspace-form-full">
                <span>描述</span>
                <textarea id="metaDescription" class="workspace-textarea" rows="3">{{ $application['description'] }}</textarea>
            </label>
            <label>
                <span>状态</span>
                <select id="metaStatus" class="workspace-input">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @if($application['status'] === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>访问权限</span>
                <select id="metaAuthMode" class="workspace-input">
                    @foreach($authModeOptions as $value => $label)
                        <option value="{{ $value }}" @if(($application['auth_mode'] ?? 'public') === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="workspace-form-full">
                <span>白名单用户（邮箱或用户名，每行一个）</span>
                <textarea id="metaAllowedUsernames" class="workspace-textarea" rows="2" placeholder="仅 whitelist 模式使用">{{ $allowedUsernames }}</textarea>
            </label>
        </div>
    </div>

    <div id="dataTablesPanel" class="workspace-panel workspace-data-panel" style="display:none;">
        <div class="workspace-panel-header">
            <div class="workspace-panel-titlebox">
                <h3>虚拟数据表</h3>
                <span class="workspace-panel-subtitle">界面定义表和字段，系统自动创建 app_vt_ 前缀物理表</span>
            </div>
            <button id="reloadVirtualTablesBtn" class="workspace-btn workspace-btn-light" type="button">刷新</button>
        </div>

        <div class="workspace-data-layout">
            <aside class="workspace-data-left">
                <div class="workspace-data-create">
                    <h4>新建虚拟表</h4>
                    <input id="vtName" class="workspace-input" type="text" placeholder="表名称，例如：客户">
                    <input id="vtSlug" class="workspace-input" type="text" placeholder="标识，例如：customer">
                    <textarea id="vtDescription" class="workspace-textarea" rows="3" placeholder="表说明"></textarea>
                    <button id="createVirtualTableBtn" class="workspace-btn workspace-btn-primary" type="button">创建虚拟表</button>
                </div>
                <div id="virtualTableList" class="workspace-vt-list"></div>
            </aside>

            <section class="workspace-data-main">
                <div id="virtualTableEmpty" class="workspace-vt-empty">选择或创建一个虚拟表</div>
                <div id="virtualTableDetail" style="display:none;">
                    <div class="workspace-vt-head">
                        <div>
                            <h4 id="selectedVtName">-</h4>
                            <p id="selectedVtMeta">-</p>
                        </div>
                        <span id="selectedVtPhysical" class="workspace-meta-badge workspace-meta-badge-soft">-</span>
                    </div>

                    <div class="workspace-vt-section">
                        <div class="workspace-vt-section-head">
                            <h4>字段定义</h4>
                            <span>字段会同步为物理表列，列名统一加 f_ 前缀</span>
                        </div>
                        <div class="workspace-field-form">
                            <input id="vfName" class="workspace-input" type="text" placeholder="字段名，例如：客户名称">
                            <input id="vfSlug" class="workspace-input" type="text" placeholder="字段标识，例如：name">
                            <select id="vfType" class="workspace-input">
                                @foreach($virtualTableFieldTypeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input id="vfLength" class="workspace-input" type="number" min="1" max="1000" placeholder="长度，可选">
                            <label class="workspace-inline-check"><input id="vfNullable" type="checkbox" checked> 可空</label>
                            <label class="workspace-inline-check"><input id="vfIndexed" type="checkbox"> 建索引</label>
                            <button id="createVirtualFieldBtn" class="workspace-btn workspace-btn-primary" type="button">添加字段</button>
                        </div>
                        <div id="virtualFieldList" class="workspace-field-list"></div>
                    </div>

                    <div class="workspace-vt-section">
                        <div class="workspace-vt-section-head">
                            <h4>数据记录</h4>
                            <span>这里的增删改查最终落到对应物理表</span>
                        </div>
                        <div id="recordForm" class="workspace-record-form"></div>
                        <div class="workspace-record-actions">
                            <button id="createRecordBtn" class="workspace-btn workspace-btn-primary" type="button">新增记录</button>
                            <button id="reloadRecordsBtn" class="workspace-btn workspace-btn-light" type="button">刷新记录</button>
                        </div>
                        <div id="recordList" class="workspace-record-list"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="workspace-layout">
        <aside class="workspace-sidebar">
            <div class="workspace-panel">
                <div class="workspace-panel-header">
                    <div class="workspace-panel-titlebox">
                        <h3>文件</h3>
                        <span class="workspace-panel-subtitle">按目录分组</span>
                    </div>
                    <button id="newFileBtn" class="workspace-btn workspace-btn-primary" type="button">新建文件</button>
                </div>
                <div class="workspace-file-search">
                    <input id="fileSearch" class="workspace-input" type="text" placeholder="搜索文件名或路径">
                </div>
                <div class="workspace-file-filters">
                    <button class="workspace-chip is-active" type="button" data-filter="all">全部</button>
                    <button class="workspace-chip" type="button" data-filter="entry">首页</button>
                    <button class="workspace-chip" type="button" data-filter="html">HTML</button>
                    <button class="workspace-chip" type="button" data-filter="js">JS</button>
                    <button class="workspace-chip" type="button" data-filter="css">CSS</button>
                </div>
                <div id="fileList" class="workspace-file-list"></div>
            </div>
        </aside>

        <section class="workspace-main">
            <div class="workspace-panel">
                <div class="workspace-panel-header">
                    <div class="workspace-panel-titlebox">
                        <h3>编辑器</h3>
                        <span id="currentFileHint" class="workspace-panel-subtitle">选择文件开始编辑</span>
                    </div>
                    <div class="workspace-header-actions">
                        <span id="saveState" class="workspace-save-state">已保存</span>
                        <button id="toggleFilesBtn" class="workspace-btn workspace-btn-light" type="button">文件</button>
                        <button id="openHistoryBtn" class="workspace-btn workspace-btn-light" type="button">历史版本</button>
                        <button id="formatCodeBtn" class="workspace-btn workspace-btn-light" type="button">格式化</button>
                        <button id="openAiBtn" class="workspace-btn workspace-btn-light" type="button">AI 生成/优化</button>
                        <button id="toggleEditorFullscreenBtn" class="workspace-btn workspace-btn-light" type="button">全屏</button>
                        <button id="saveFileBtn" class="workspace-btn workspace-btn-primary" type="button">保存文件</button>
                    </div>
                </div>
                <div id="workspaceTabs" class="workspace-tabs"></div>
                <div class="workspace-editor-meta">
                    <label>
                        <span>标题</span>
                        <input id="fileName" class="workspace-input" type="text">
                    </label>
                    <label>
                        <span>路径</span>
                        <input id="filePath" class="workspace-input" type="text" placeholder="index.html">
                    </label>
                    <label>
                        <span>类型</span>
                        <select id="fileType" class="workspace-input">
                            @foreach($codeTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>状态</span>
                        <select id="fileStatus" class="workspace-input">
                            <option value="1">启用</option>
                            <option value="2">禁用</option>
                        </select>
                    </label>
                    <label>
                        <span>文件权限（留空继承应用）</span>
                        <select id="fileAuthMode" class="workspace-input">
                            <option value="">继承应用</option>
                            @foreach($authModeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="workspace-preview-actions">
                        <button id="refreshPreviewBtn" class="workspace-btn workspace-btn-light" type="button">刷新预览</button>
                        <a id="openPreviewBtn" class="workspace-btn workspace-btn-light" href="#" target="_blank">新窗口预览</a>
                    </div>
                    <div class="workspace-current-badges">
                        <span id="currentTypeBadge" class="workspace-meta-badge">-</span>
                        <span id="currentPathBadge" class="workspace-meta-badge workspace-meta-badge-soft">-</span>
                    </div>
                </div>
                <div class="workspace-editor-wrap">
                    <div id="codeEditor"></div>
                </div>
                <div class="workspace-statusbar">
                    <span id="statusPath">未选择文件</span>
                    <span id="statusType">-</span>
                    <span id="statusSize">0 字符</span>
                    <span id="statusUpdated">-</span>
                    <span class="workspace-shortcut">⌘/Ctrl + S 保存</span>
                </div>
            </div>
        </section>

        <aside class="workspace-preview">
            <div class="workspace-panel workspace-preview-panel">
                <div class="workspace-panel-header">
                    <div class="workspace-panel-titlebox">
                        <h3>预览</h3>
                        <span id="previewHint" class="workspace-preview-hint">当前文件可预览时显示</span>
                    </div>
                    <div class="workspace-device-switch">
                        <button class="workspace-device is-active" type="button" data-device="desktop">桌面</button>
                        <button class="workspace-device" type="button" data-device="tablet">平板</button>
                        <button class="workspace-device" type="button" data-device="phone">手机</button>
                    </div>
                </div>
                <div class="workspace-preview-canvas">
                    <iframe id="previewFrame" class="workspace-iframe" title="preview"></iframe>
                </div>
            </div>
        </aside>
    </div>

    <div id="newFileModal" class="workspace-modal" style="display:none;">
        <div class="workspace-modal-card">
            <div class="workspace-panel-header">
                <h3>新建文件</h3>
                <button id="closeNewFileModal" class="workspace-modal-close" type="button">&times;</button>
            </div>
            <div class="workspace-modal-body">
                <div class="workspace-form-grid">
                    <label>
                        <span>标题</span>
                        <input id="newFileName" class="workspace-input" type="text" placeholder="首页">
                    </label>
                    <label>
                        <span>路径</span>
                        <input id="newFilePath" class="workspace-input" type="text" placeholder="index.html">
                    </label>
                    <label>
                        <span>类型</span>
                        <select id="newFileType" class="workspace-input">
                            @foreach($codeTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>状态</span>
                        <select id="newFileStatus" class="workspace-input">
                            <option value="1">启用</option>
                            <option value="2">禁用</option>
                        </select>
                    </label>
                    <label class="workspace-form-full">
                        <span>初始内容</span>
                        <textarea id="newFileContent" class="workspace-textarea" rows="8"></textarea>
                    </label>
                </div>
                <div class="workspace-modal-actions">
                    <button id="submitNewFileBtn" class="workspace-btn workspace-btn-primary" type="button">创建文件</button>
                </div>
            </div>
        </div>
    </div>

    <div id="aiModal" class="workspace-modal" style="display:none;">
        <div class="workspace-modal-card">
            <div class="workspace-panel-header">
                <h3>AI 生成与优化</h3>
                <button id="closeAiModal" class="workspace-modal-close" type="button">&times;</button>
            </div>
            <div class="workspace-modal-body">
                <div class="workspace-form-grid">
                    <label>
                        <span>模式</span>
                        <select id="aiMode" class="workspace-input">
                            <option value="generate">生成新代码</option>
                            <option value="optimize">优化当前代码</option>
                        </select>
                    </label>
                    <label class="workspace-form-full">
                        <span>补充需求</span>
                        <textarea id="aiPrompt" class="workspace-textarea" rows="5" placeholder="描述你想生成或优化的目标，例如：做成双栏 dashboard、补上错误处理、拆出可复用函数。"></textarea>
                    </label>
                    <label class="workspace-form-full">
                        <span>生成结果</span>
                        <textarea id="aiResult" class="workspace-textarea" rows="12" placeholder="点击生成后显示结果"></textarea>
                    </label>
                </div>
                <div class="workspace-modal-actions workspace-modal-actions-split">
                    <button id="runAiBtn" class="workspace-btn workspace-btn-light" type="button">开始生成</button>
                    <button id="applyAiBtn" class="workspace-btn workspace-btn-primary" type="button">应用到编辑器</button>
                </div>
            </div>
        </div>
    </div>

    <div id="historyModal" class="workspace-modal" style="display:none;">
        <div class="workspace-modal-card workspace-history-card">
            <div class="workspace-panel-header">
                <h3>历史版本</h3>
                <button id="closeHistoryModal" class="workspace-modal-close" type="button">&times;</button>
            </div>
            <div class="workspace-modal-body workspace-history-body">
                <div id="historyList" class="workspace-history-list"></div>
                <div class="workspace-history-preview">
                    <div class="workspace-history-preview-head">
                        <span id="historyPreviewTitle">选择一个历史版本</span>
                        <button id="rollbackHistoryBtn" class="workspace-btn workspace-btn-primary" type="button" style="display:none;">回滚到此版本</button>
                    </div>
                    <pre id="historyPreviewContent" class="workspace-history-pre"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper .content { padding-top: 12px; }
    .workspace-shell {
        --ws-bg: #f6f9fc;
        --ws-panel: rgba(255, 255, 255, 0.94);
        --ws-panel-strong: #ffffff;
        --ws-border: #dce7ef;
        --ws-border-strong: #c8d9e6;
        --ws-text: #213043;
        --ws-muted: #687b8f;
        --ws-primary: #4aa3a2;
        --ws-primary-deep: #3d8f8e;
        --ws-secondary: #5f9ae0;
        --ws-soft-blue: #eef6ff;
        --ws-soft-green: #eef7f3;
        --ws-soft-slate: #f3f6f9;
        padding: 0 0 24px;
        color: var(--ws-text);
    }
    .workspace-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
        padding: 18px 22px;
        border-radius: 20px;
        background:
            radial-gradient(circle at top right, rgba(95, 154, 224, 0.16), transparent 32%),
            radial-gradient(circle at left bottom, rgba(74, 163, 162, 0.12), transparent 34%),
            linear-gradient(135deg, #fbfdff 0%, #f2f8fb 58%, #edf6f3 100%);
        border: 1px solid var(--ws-border);
        box-shadow: 0 18px 42px rgba(87, 113, 138, 0.09);
    }
    .workspace-kicker { font-size: 11px; letter-spacing: .14em; text-transform: uppercase; color: var(--ws-secondary); margin-bottom: 8px; }
    .workspace-titleblock h2 { margin: 0; font-size: 28px; color: var(--ws-text); }
    .workspace-slug { margin-top: 6px; color: var(--ws-muted); }
    .workspace-top-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
    .workspace-app-glance { display: flex; align-items: center; gap: 8px; margin-right: 4px; }
    .workspace-glance-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255,255,255,0.7);
        color: var(--ws-muted);
        border: 1px solid rgba(200, 217, 230, 0.9);
    }
    .workspace-glance-pill-accent {
        background: linear-gradient(135deg, rgba(74, 163, 162, 0.12), rgba(95, 154, 224, 0.12));
        color: var(--ws-primary-deep);
    }
    .workspace-layout {
        display: grid;
        grid-template-columns: minmax(640px, 2fr) minmax(360px, 1fr);
        gap: 16px;
        align-items: start;
    }
    .workspace-sidebar, .workspace-main, .workspace-preview { min-width: 0; }
    .workspace-sidebar {
        position: fixed;
        top: 80px;
        bottom: 24px;
        left: 24px;
        width: 320px;
        max-width: calc(100vw - 48px);
        z-index: 2100;
        display: none;
        overflow: auto;
        box-shadow: 0 24px 60px rgba(35, 50, 72, .18);
    }
    .workspace-shell.files-open .workspace-sidebar { display: block; }
    .workspace-panel {
        background: var(--ws-panel);
        border: 1px solid var(--ws-border);
        border-radius: 18px;
        backdrop-filter: blur(8px);
        box-shadow: 0 14px 30px rgba(93, 122, 147, 0.08);
    }
    .workspace-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 18px 14px;
        border-bottom: 1px solid #e9f0f5;
    }
    .workspace-panel-header h3 { margin: 0; font-size: 18px; }
    .workspace-panel-titlebox { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .workspace-panel-subtitle { font-size: 12px; color: var(--ws-muted); }
    .workspace-meta-panel { margin-bottom: 16px; }
    .workspace-data-panel { margin-bottom: 16px; overflow: hidden; }
    .workspace-data-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 0;
        min-height: 520px;
    }
    .workspace-data-left {
        border-right: 1px solid #e9f0f5;
        background: #f8fbfd;
        padding: 16px;
    }
    .workspace-data-create {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid var(--ws-border);
        margin-bottom: 14px;
    }
    .workspace-data-create h4,
    .workspace-vt-section h4,
    .workspace-vt-head h4 {
        margin: 0;
        font-weight: 800;
        color: var(--ws-text);
    }
    .workspace-vt-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 520px;
        overflow: auto;
    }
    .workspace-vt-item {
        border: 1px solid var(--ws-border);
        border-radius: 15px;
        background: #fff;
        padding: 12px;
        cursor: pointer;
    }
    .workspace-vt-item.active {
        border-color: #9bc9c8;
        background: linear-gradient(135deg, var(--ws-soft-green) 0%, var(--ws-soft-blue) 100%);
    }
    .workspace-vt-item-title {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-weight: 800;
    }
    .workspace-vt-item-meta {
        margin-top: 6px;
        color: var(--ws-muted);
        font-size: 12px;
        word-break: break-all;
    }
    .workspace-data-main {
        padding: 18px;
        min-width: 0;
    }
    .workspace-vt-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 420px;
        border: 1px dashed var(--ws-border-strong);
        border-radius: 18px;
        color: var(--ws-muted);
        background: #fbfdff;
    }
    .workspace-vt-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 18px;
        background: linear-gradient(135deg, #fff 0%, #f5faff 100%);
        border: 1px solid var(--ws-border);
    }
    .workspace-vt-head p {
        margin: 6px 0 0;
        color: var(--ws-muted);
    }
    .workspace-vt-section {
        margin-top: 16px;
        padding: 16px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid var(--ws-border);
    }
    .workspace-vt-section-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        color: var(--ws-muted);
        font-size: 12px;
    }
    .workspace-field-form,
    .workspace-record-form {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        align-items: center;
    }
    .workspace-inline-check {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        color: #4f6478;
        font-weight: 700;
        margin: 0;
    }
    .workspace-field-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .workspace-field-pill {
        display: inline-flex;
        flex-direction: column;
        gap: 3px;
        min-width: 120px;
        border: 1px solid #e0e9f2;
        border-radius: 13px;
        padding: 9px 10px;
        background: #f8fbfd;
    }
    .workspace-field-pill strong { font-size: 13px; }
    .workspace-field-pill span { color: var(--ws-muted); font-size: 12px; }
    .workspace-record-form label {
        display: flex;
        flex-direction: column;
        gap: 6px;
        color: #4f6478;
        font-weight: 700;
        margin: 0;
    }
    .workspace-record-actions {
        display: flex;
        gap: 10px;
        margin: 12px 0;
    }
    .workspace-record-list {
        overflow: auto;
        border: 1px solid #e9f0f5;
        border-radius: 14px;
    }
    .workspace-record-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        background: #fff;
    }
    .workspace-record-table th,
    .workspace-record-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #edf2f7;
        text-align: left;
        vertical-align: top;
        font-size: 12px;
    }
    .workspace-record-table th {
        background: #f8fbfd;
        color: #52667c;
        font-weight: 800;
    }
    .workspace-record-table td {
        color: #31445a;
        max-width: 260px;
        word-break: break-word;
    }
    .workspace-record-table .workspace-record-op {
        white-space: nowrap;
        width: 120px;
    }
    .workspace-link-btn {
        border: 0;
        background: transparent;
        color: var(--ws-secondary);
        font-weight: 800;
        padding: 0 6px 0 0;
        cursor: pointer;
    }
    .workspace-form-grid, .workspace-editor-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        padding: 18px;
    }
    .workspace-editor-meta { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .workspace-form-grid label, .workspace-editor-meta label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-weight: 600;
        color: #4f6478;
    }
    .workspace-form-full { grid-column: 1 / -1; }
    .workspace-input, .workspace-textarea {
        width: 100%;
        border: 1px solid var(--ws-border);
        border-radius: 12px;
        padding: 10px 12px;
        background: #fcfeff;
        color: var(--ws-text);
    }
    .workspace-textarea { resize: vertical; }
    .workspace-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        text-decoration: none;
        font-weight: 700;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
    }
    .workspace-btn:hover {
        transform: translateY(-1px);
    }
    .workspace-btn-primary {
        background: linear-gradient(135deg, var(--ws-primary), var(--ws-secondary));
        color: #fff;
        box-shadow: 0 10px 24px rgba(79, 145, 191, 0.22);
    }
    .workspace-btn-light {
        background: #f3f8fb;
        color: #4d6782;
        border: 1px solid var(--ws-border);
    }
    .workspace-file-search { padding: 0 18px 14px; }
    .workspace-file-filters {
        display: flex;
        gap: 8px;
        padding: 0 18px 14px;
        overflow-x: auto;
    }
    .workspace-chip {
        border: 1px solid var(--ws-border);
        background: #f7fbfd;
        color: var(--ws-muted);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        cursor: pointer;
    }
    .workspace-chip.is-active {
        background: linear-gradient(135deg, rgba(74, 163, 162, 0.14), rgba(95, 154, 224, 0.14));
        color: var(--ws-primary-deep);
        border-color: #a7cfd0;
    }
    .workspace-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 12px 18px 0;
        background: linear-gradient(180deg, #fff 0%, #f8fbfd 100%);
    }
    .workspace-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 220px;
        border: 1px solid var(--ws-border);
        border-bottom-color: #cbd8e5;
        border-radius: 12px 12px 0 0;
        background: #eef3f8;
        color: #607287;
        padding: 9px 11px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }
    .workspace-tab.active {
        background: #172033;
        border-color: #172033;
        color: #fff;
    }
    .workspace-tab.dirty:after {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #ffc857;
        flex: 0 0 auto;
    }
    .workspace-tab-name {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .workspace-file-list {
        max-height: calc(100vh - 310px);
        overflow: auto;
        padding: 0 12px 12px;
    }
    .workspace-file-group { margin-bottom: 16px; }
    .workspace-file-group-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 8px 8px;
        color: var(--ws-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .workspace-file-group-header::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--ws-primary), var(--ws-secondary));
        flex: 0 0 auto;
    }
    .workspace-file-item {
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.84);
        transition: background .2s ease, border-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .workspace-file-item:hover {
        transform: translateY(-1px);
        border-color: var(--ws-border);
        box-shadow: 0 10px 24px rgba(94, 123, 149, 0.08);
    }
    .workspace-file-item.active {
        background: linear-gradient(135deg, var(--ws-soft-green) 0%, var(--ws-soft-blue) 100%);
        border-color: #9bc9c8;
        box-shadow: 0 12px 28px rgba(83, 142, 162, 0.12);
    }
    .workspace-file-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .workspace-file-icon {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: #fff;
        flex: 0 0 auto;
        box-shadow: inset 0 -8px 18px rgba(0,0,0,0.08);
    }
    .workspace-file-icon-html { background: linear-gradient(135deg, #5da6df, #6ec4d2); }
    .workspace-file-icon-js { background: linear-gradient(135deg, #5d8be0, #7ab1f1); }
    .workspace-file-icon-css { background: linear-gradient(135deg, #48b1b4, #67d0b8); }
    .workspace-file-icon-json { background: linear-gradient(135deg, #7b9cc4, #8fb7d1); }
    .workspace-file-icon-php { background: linear-gradient(135deg, #6f8fb5, #8da9cc); }
    .workspace-file-body { min-width: 0; flex: 1; }
    .workspace-file-titleline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .workspace-file-name { font-weight: 700; color: var(--ws-text); }
    .workspace-file-path { margin-top: 4px; font-size: 12px; color: var(--ws-muted); }
    .workspace-file-meta {
        margin-top: 8px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 12px;
        color: var(--ws-muted);
    }
    .workspace-entry-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        border-radius: 999px;
        background: rgba(74, 163, 162, 0.12);
        color: var(--ws-primary-deep);
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }
    .workspace-header-actions { display: flex; align-items: center; gap: 10px; }
    .workspace-header-actions { flex-wrap: wrap; justify-content: flex-end; }
    .workspace-save-state {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 999px;
        background: var(--ws-soft-green);
        color: #367a78;
    }
    .workspace-save-state.dirty {
        background: #fff5de;
        color: #a17222;
    }
    .workspace-editor-wrap { padding: 0 18px 18px; }
    #codeEditor {
        height: calc(100vh - 390px);
        min-height: 520px;
        border-radius: 16px;
        border: 1px solid var(--ws-border);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        overflow: hidden;
    }
    .workspace-shell.editor-fullscreen .workspace-main {
        position: fixed;
        inset: 16px;
        z-index: 2300;
        overflow: auto;
    }
    .workspace-shell.editor-fullscreen .workspace-main > .workspace-panel {
        min-height: calc(100vh - 32px);
        display: flex;
        flex-direction: column;
    }
    .workspace-shell.editor-fullscreen .workspace-editor-wrap {
        flex: 1;
        display: flex;
        min-height: 0;
    }
    .workspace-shell.editor-fullscreen #codeEditor {
        flex: 1;
        height: auto;
        min-height: calc(100vh - 260px);
    }
    .workspace-shell.editor-fullscreen .workspace-preview,
    .workspace-shell.editor-fullscreen .workspace-topbar,
    .workspace-shell.editor-fullscreen #metaPanel,
    .workspace-shell.editor-fullscreen #dataTablesPanel {
        display: none;
    }
    body.workspace-editor-fullscreen {
        overflow: hidden;
    }
    .workspace-statusbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        padding: 10px 18px 14px;
        color: #687b8f;
        font-size: 12px;
        border-top: 1px solid #e9f0f5;
        background: #fbfdff;
        border-radius: 0 0 18px 18px;
    }
    .workspace-statusbar span {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #f2f6fa;
    }
    .workspace-statusbar .workspace-shortcut {
        margin-left: auto;
        background: #172033;
        color: #dbe7f3;
    }
    .workspace-preview-actions {
        grid-column: span 2;
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }
    .workspace-current-badges {
        grid-column: 1 / -1;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: -2px;
    }
    .workspace-meta-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(74, 163, 162, 0.12);
        color: var(--ws-primary-deep);
        font-size: 12px;
        font-weight: 700;
    }
    .workspace-meta-badge-soft {
        background: #f4f8fb;
        color: var(--ws-muted);
        border: 1px solid var(--ws-border);
    }
    .workspace-preview-panel { overflow: hidden; }
    .workspace-preview-hint { font-size: 12px; color: var(--ws-muted); }
    .workspace-device-switch {
        display: flex;
        gap: 6px;
        padding: 4px;
        border-radius: 999px;
        background: #eef3f8;
        border: 1px solid var(--ws-border);
    }
    .workspace-device {
        border: 0;
        border-radius: 999px;
        padding: 7px 10px;
        background: transparent;
        color: #607287;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }
    .workspace-device.is-active {
        color: #fff;
        background: #172033;
        box-shadow: 0 8px 18px rgba(23,32,51,.16);
    }
    .workspace-preview-canvas {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 14px;
        min-height: calc(100vh - 250px);
        background:
            linear-gradient(45deg, rgba(148,163,184,.14) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(148,163,184,.14) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(148,163,184,.14) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(148,163,184,.14) 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    }
    .workspace-iframe {
        width: 100%;
        height: calc(100vh - 250px);
        min-height: 620px;
        border: 1px solid #d8e2ec;
        border-radius: 16px;
        background: #fbfdff;
        box-shadow: 0 18px 40px rgba(35, 50, 72, .12);
        transition: width .22s ease;
    }
    .workspace-preview-canvas.device-tablet .workspace-iframe {
        width: 768px;
        max-width: 100%;
    }
    .workspace-preview-canvas.device-phone .workspace-iframe {
        width: 390px;
        max-width: 100%;
    }
    .workspace-modal {
        position: fixed;
        inset: 0;
        background: rgba(58, 76, 97, 0.28);
        z-index: 2200;
        padding: 40px 16px;
    }
    .workspace-modal-card {
        max-width: 760px;
        margin: 0 auto;
        background: #fbfdff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--ws-border);
        box-shadow: 0 24px 60px rgba(67, 88, 112, 0.2);
    }
    .workspace-modal-body { padding: 18px; }
    .workspace-modal-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 18px;
    }
    .workspace-modal-actions-split {
        justify-content: space-between;
        gap: 12px;
    }
    .workspace-modal-close { border: 0; background: transparent; font-size: 28px; color: #6c8199; cursor: pointer; }
    .workspace-history-card { max-width: 1100px; }
    .workspace-history-body {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 18px;
    }
    .workspace-history-list {
        max-height: 70vh;
        overflow: auto;
        border-right: 1px solid #e9f0f5;
        padding-right: 12px;
    }
    .workspace-history-item {
        border: 1px solid var(--ws-border);
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        background: rgba(255,255,255,0.88);
        transition: background .2s ease, border-color .2s ease, transform .2s ease;
    }
    .workspace-history-item:hover {
        transform: translateY(-1px);
        border-color: var(--ws-border-strong);
    }
    .workspace-history-item.active {
        background: linear-gradient(135deg, var(--ws-soft-green) 0%, var(--ws-soft-blue) 100%);
        border-color: #9bc9c8;
    }
    .workspace-history-item-title { font-weight: 700; color: var(--ws-text); }
    .workspace-history-item-time { margin-top: 4px; font-size: 12px; color: var(--ws-muted); }
    .workspace-history-preview { min-width: 0; }
    .workspace-history-preview-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 12px;
    }
    .workspace-history-pre {
        max-height: 65vh;
        overflow: auto;
        margin: 0;
        padding: 16px;
        border-radius: 14px;
        background: linear-gradient(180deg, #f7fbfe 0%, #eef5f8 100%);
        border: 1px solid var(--ws-border);
        color: #375064;
        white-space: pre-wrap;
        word-break: break-word;
    }
    @media (max-width: 1400px) {
        .workspace-layout { grid-template-columns: minmax(0, 1fr); }
        .workspace-preview { grid-column: 1 / -1; }
        .workspace-iframe { height: 520px; min-height: 520px; }
    }
    @media (max-width: 900px) {
        .workspace-topbar { flex-direction: column; align-items: stretch; }
        .workspace-top-actions { flex-wrap: wrap; }
        .workspace-app-glance { margin-right: 0; flex-wrap: wrap; }
        .workspace-layout { grid-template-columns: 1fr; }
        .workspace-editor-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .workspace-data-layout { grid-template-columns: 1fr; }
        .workspace-data-left { border-right: 0; border-bottom: 1px solid #e9f0f5; }
        .workspace-field-form, .workspace-record-form { grid-template-columns: 1fr; }
        .workspace-preview-actions { grid-column: 1 / -1; }
        #codeEditor { height: 420px; min-height: 420px; }
        .workspace-history-body { grid-template-columns: 1fr; }
        .workspace-history-list { border-right: 0; padding-right: 0; }
    }
</style>

<script src="/js/src-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
    (function () {
        var appId = {{ (int) $application['id'] }};
        var files = @json($files);
        var selectedCodeId = {{ $selectedCodeId ? (int) $selectedCodeId : 'null' }};
        var editor = ace.edit('codeEditor');
        var currentFile = null;
        var dirty = false;
        var previewTimer = null;
        var historyState = {
            items: [],
            selectedId: null
        };
        var virtualTables = [];
        var selectedVirtualTable = null;
        var selectedRecords = [];
        var activeFileFilter = 'all';
        var openTabs = [];
        var fileLoadRequest = 0;

        editor.setTheme('ace/theme/chrome');
        editor.session.setUseWrapMode(true);
        editor.setFontSize(14);
        editor.on('change', function () {
            if (!currentFile) {
                return;
            }
            setDirty(true);
            updateStatusbar();
            schedulePreviewRefresh();
        });

        window.addEventListener('beforeunload', function (event) {
            if (!dirty) {
                return;
            }
            event.preventDefault();
            event.returnValue = '';
        });

        function modeByType(type) {
            var map = {
                1: 'ace/mode/php',
                2: 'ace/mode/html',
                3: 'ace/mode/javascript',
                4: 'ace/mode/css',
                5: 'ace/mode/json'
            };
            return map[type] || 'ace/mode/text';
        }

        function setDirty(value) {
            dirty = value;
            $('#saveState').toggleClass('dirty', !!value).text(value ? '未保存' : '已保存');
            renderTabs();
            updateStatusbar();
        }

        function renderTabs() {
            var html = '';

            openTabs.forEach(function (file) {
                var active = currentFile && Number(currentFile.id) === Number(file.id) ? ' active' : '';
                var isDirty = active && dirty ? ' dirty' : '';
                html += '<button class="workspace-tab' + active + isDirty + '" type="button" data-id="' + file.id + '">'
                    + '<span class="workspace-tab-name">' + escapeHtml(file.basename || file.name || file.path || '未命名') + '</span>'
                    + '</button>';
            });

            $('#workspaceTabs').html(html || '<button class="workspace-tab active" type="button">未打开文件</button>');
        }

        function ensureTab(file) {
            var exists = openTabs.some(function (item) {
                return Number(item.id) === Number(file.id);
            });

            if (!exists) {
                openTabs.push($.extend({}, file));
            } else {
                openTabs = openTabs.map(function (item) {
                    return Number(item.id) === Number(file.id) ? $.extend({}, item, file) : item;
                });
            }
        }

        function updateStatusbar() {
            if (!currentFile) {
                $('#statusPath').text('未选择文件');
                $('#statusType').text('-');
                $('#statusSize').text('0 字符');
                $('#statusUpdated').text('-');
                return;
            }

            var content = editor ? editor.getValue() : (currentFile.content || '');
            $('#statusPath').text(currentFile.path || '-');
            $('#statusType').text((currentFile.type_text || '-').toUpperCase());
            $('#statusSize').text(String(content.length) + ' 字符');
            $('#statusUpdated').text(currentFile.updated_at ? '更新 ' + currentFile.updated_at : '暂无更新时间');
        }

        function renderFiles() {
            var keyword = $.trim($('#fileSearch').val()).toLowerCase();
            var groups = {};
            var groupOrder = [];

            files.forEach(function (file) {
                var matched = !keyword
                    || String(file.name || '').toLowerCase().indexOf(keyword) !== -1
                    || String(file.path || '').toLowerCase().indexOf(keyword) !== -1;

                if (activeFileFilter === 'entry' && !file.is_entry) {
                    matched = false;
                }
                if (activeFileFilter !== 'all' && activeFileFilter !== 'entry' && String(file.type_text || '') !== activeFileFilter) {
                    matched = false;
                }

                if (!matched) {
                    return;
                }

                var groupName = file.directory || 'root';
                if (!groups[groupName]) {
                    groups[groupName] = [];
                    groupOrder.push(groupName);
                }
                groups[groupName].push(file);
            });

            groupOrder.sort(function (a, b) {
                if (a === 'root') {
                    return -1;
                }
                if (b === 'root') {
                    return 1;
                }
                return a.localeCompare(b);
            });

            var html = '';

            groupOrder.forEach(function (groupName) {
                html += '<div class="workspace-file-group">';
                html += '<div class="workspace-file-group-header">' + escapeHtml(groupName === 'root' ? 'Root' : groupName) + '</div>';

                groups[groupName].forEach(function (file) {
                    var active = currentFile && Number(currentFile.id) === Number(file.id) ? ' active' : '';
                    html += ''
                        + '<div class="workspace-file-item' + active + '" data-id="' + file.id + '">'
                        +   '<div class="workspace-file-row">'
                        +       '<div class="workspace-file-icon workspace-file-icon-' + escapeHtml(file.type_text || 'txt') + '">' + escapeHtml((file.type_text || 'txt').toUpperCase()) + '</div>'
                        +       '<div class="workspace-file-body">'
                        +           '<div class="workspace-file-titleline">'
                        +               '<div class="workspace-file-name">' + escapeHtml(file.name || file.basename || '未命名文件') + '</div>'
                        +               (file.is_entry ? '<span class="workspace-entry-badge">首页</span>' : '')
                        +           '</div>'
                        +           '<div class="workspace-file-path">' + escapeHtml(file.path || '') + '</div>'
                        +           '<div class="workspace-file-meta"><span>' + escapeHtml(file.type_text || '') + '</span><span>' + escapeHtml(file.status_text || '') + '</span></div>'
                        +       '</div>'
                        +   '</div>'
                        + '</div>';
                });

                html += '</div>';
            });

            if (!html) {
                html = '<div class="workspace-file-item"><div class="workspace-file-path">没有匹配文件</div></div>';
            }

            $('#fileList').html(html);
        }

        function selectFile(fileId) {
            if (dirty && currentFile && Number(currentFile.id) !== Number(fileId)) {
                if (!window.confirm('当前文件有未保存修改，确认切换吗？')) {
                    return;
                }
            }

            var next = files.find(function (file) {
                return Number(file.id) === Number(fileId);
            });

            if (!next) {
                return;
            }

            currentFile = $.extend({}, next);
            var requestId = ++fileLoadRequest;
            ensureTab(currentFile);
            $('#fileName').val(currentFile.name || '');
            $('#filePath').val(currentFile.path || '');
            $('#fileType').val(String(currentFile.type || 2));
            $('#fileStatus').val(String(currentFile.status || 1));
            $('#fileAuthMode').val(currentFile.auth_mode || '');
            editor.session.setMode(modeByType(Number(currentFile.type)));
            editor.setValue('', -1);
            $('#openPreviewBtn').attr('href', currentFile.preview_url || '#');
            $('#currentFileHint').text((currentFile.name || '未命名文件') + ' · ' + (currentFile.directory || 'root'));
            $('#currentTypeBadge').text((currentFile.type_text || '-').toUpperCase());
            $('#currentPathBadge').text(currentFile.path || '-');
            $('#previewHint').text('正在加载代码…');
            renderFiles();
            setDirty(false);
            renderTabs();
            updateStatusbar();

            if (currentFile.content_loaded) {
                editor.setValue(currentFile.content || '', -1);
                refreshPreview(false);
                setDirty(false);
                updateStatusbar();
                return;
            }

            $.get('/admin/applications/' + appId + '/codes/' + currentFile.id, function (response) {
                if (requestId !== fileLoadRequest || !currentFile || Number(currentFile.id) !== Number(fileId)) {
                    return;
                }
                if (Number(response.code) !== 9999 || !response.data || !response.data.file) {
                    $('#previewHint').text('代码加载失败');
                    return;
                }
                currentFile = $.extend({}, currentFile, response.data.file);
                syncFileInList(currentFile);
                ensureTab(currentFile);
                editor.setValue(currentFile.content || '', -1);
                refreshPreview(false);
                setDirty(false);
                renderTabs();
                updateStatusbar();
            }).fail(function () {
                if (requestId === fileLoadRequest) {
                    $('#previewHint').text('代码加载失败，请重试');
                }
            });
        }

        function refreshPreview(force) {
            if (!currentFile) {
                $('#previewHint').text('先选择文件');
                $('#previewFrame').attr('src', 'about:blank');
                return;
            }

            var preview = buildPreviewDocument();
            if (preview) {
                $('#previewHint').text('当前编辑态预览');
                $('#previewFrame').attr('srcdoc', preview);
                return;
            }

            if (!currentFile.preview_url || !currentFile.is_previewable) {
                $('#previewHint').text('当前文件类型不支持预览');
                $('#previewFrame').attr('src', 'about:blank');
                $('#previewFrame').removeAttr('srcdoc');
                return;
            }

            $('#previewHint').text(currentFile.path || '');
            $('#previewFrame').removeAttr('srcdoc');
            var src = currentFile.preview_url;
            if (force) {
                src += (src.indexOf('?') === -1 ? '?' : '&') + '_ts=' + Date.now();
            }
            $('#previewFrame').attr('src', src);
        }

        function schedulePreviewRefresh() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(function () {
                refreshPreview(false);
            }, 250);
        }

        function updateFileFromForm() {
            if (!currentFile) {
                return null;
            }

            return {
                name: $('#fileName').val(),
                path: $('#filePath').val(),
                type: Number($('#fileType').val()),
                status: Number($('#fileStatus').val()),
                auth_mode: $('#fileAuthMode').val(),
                content: editor.getValue()
            };
        }

        function syncFileInList(file) {
            var index = files.findIndex(function (item) {
                return Number(item.id) === Number(file.id);
            });

            if (index === -1) {
                files.push(file);
            } else {
                files[index] = file;
            }

            openTabs = openTabs.map(function (item) {
                return Number(item.id) === Number(file.id) ? $.extend({}, item, file) : item;
            });
        }

        function suggestTitleFromPath(path) {
            var normalized = String(path || '').split('/').pop() || '';
            var withoutExt = normalized.replace(/\.[^.]+$/, '');
            var text = withoutExt.replace(/[-_]+/g, ' ').trim();
            return text ? text.replace(/\b[a-z]/g, function (char) { return char.toUpperCase(); }) : '';
        }

        function formatCurrentCode() {
            if (!currentFile) {
                return;
            }

            var type = Number($('#fileType').val());
            var content = editor.getValue();

            if (type === 5) {
                try {
                    var pretty = JSON.stringify(JSON.parse(content), null, 4);
                    editor.setValue(pretty, -1);
                    setDirty(true);
                } catch (error) {
                    alert('JSON 格式不合法，无法格式化');
                }
                return;
            }

            editor.execCommand('beautify');
            setDirty(true);
        }

        function getWorkingFiles() {
            return files.map(function (file) {
                if (currentFile && Number(file.id) === Number(currentFile.id)) {
                    return $.extend({}, file, updateFileFromForm());
                }
                return $.extend({}, file);
            });
        }

        function buildPreviewDocument() {
            var workingFiles = getWorkingFiles();
            var fileMap = {};

            workingFiles.forEach(function (file) {
                fileMap[String(file.path || '').replace(/^\/+/, '')] = file;
            });

            var type = Number($('#fileType').val());
            var content = editor.getValue();

            if (type === 2) {
                return inlineLinkedAssets(content, fileMap);
            }

            if (type === 4 || type === 3) {
                var entry = fileMap['index.html'] || fileMap['index.php'];
                if (entry && Number(entry.type) === 2) {
                    var html = String(entry.content || '');
                    if (type === 4) {
                        fileMap[String($('#filePath').val()).replace(/^\/+/, '')] = $.extend({}, currentFile, updateFileFromForm());
                    }
                    if (type === 3) {
                        fileMap[String($('#filePath').val()).replace(/^\/+/, '')] = $.extend({}, currentFile, updateFileFromForm());
                    }
                    return inlineLinkedAssets(html, fileMap);
                }
            }

            if (type === 5) {
                return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>JSON Preview</title><style>body{font-family:Menlo,monospace;background:#f6f2ea;margin:0;padding:24px;}pre{white-space:pre-wrap;word-break:break-word;background:#fff;padding:16px;border-radius:14px;}</style></head><body><pre>'
                    + escapeHtml(content)
                    + '</pre></body></html>';
            }

            return null;
        }

        function inlineLinkedAssets(html, fileMap) {
            var result = String(html || '');

            result = result.replace(/<link([^>]+href=["']([^"']+)["'][^>]*)>/gi, function (full, attrs, href) {
                var normalized = String(href || '').replace(/^\/+/, '');
                var file = fileMap[normalized];
                if (!file || Number(file.type) !== 4) {
                    return full;
                }
                return '<style data-inline-path="' + escapeHtml(normalized) + '">' + String(file.content || '') + '</style>';
            });

            result = result.replace(/<script([^>]+src=["']([^"']+)["'][^>]*)><\/script>/gi, function (full, attrs, src) {
                var normalized = String(src || '').replace(/^\/+/, '');
                var file = fileMap[normalized];
                if (!file || Number(file.type) !== 3) {
                    return full;
                }
                return '<script data-inline-path="' + escapeHtml(normalized) + '">' + String(file.content || '') + '<\/script>';
            });

            return result;
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[char];
            });
        }

        function loadVirtualTables() {
            $.get('/admin/applications/' + appId + '/virtual-tables', function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '加载虚拟表失败');
                    return;
                }

                virtualTables = response.data.tables || [];
                if (selectedVirtualTable) {
                    var matched = virtualTables.find(function (table) {
                        return Number(table.id) === Number(selectedVirtualTable.id);
                    });
                    selectedVirtualTable = matched || null;
                }

                renderVirtualTables();
                renderVirtualTableDetail();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '加载虚拟表失败');
            });
        }

        function renderVirtualTables() {
            var html = '';

            virtualTables.forEach(function (table) {
                var active = selectedVirtualTable && Number(selectedVirtualTable.id) === Number(table.id) ? ' active' : '';
                html += '<div class="workspace-vt-item' + active + '" data-id="' + table.id + '">'
                    + '<div class="workspace-vt-item-title"><span>' + escapeHtml(table.name) + '</span><span>' + table.fields_count + ' 字段</span></div>'
                    + '<div class="workspace-vt-item-meta">/' + escapeHtml(table.slug) + '</div>'
                    + '<div class="workspace-vt-item-meta">' + escapeHtml(table.physical_table || '未同步物理表') + '</div>'
                    + '</div>';
            });

            $('#virtualTableList').html(html || '<div class="workspace-vt-empty" style="min-height:140px;">暂无虚拟表</div>');
        }

        function renderVirtualTableDetail() {
            if (!selectedVirtualTable) {
                $('#virtualTableEmpty').show();
                $('#virtualTableDetail').hide();
                return;
            }

            $('#virtualTableEmpty').hide();
            $('#virtualTableDetail').show();
            $('#selectedVtName').text(selectedVirtualTable.name);
            $('#selectedVtMeta').text('/' + selectedVirtualTable.slug + ' · ' + (selectedVirtualTable.description || '暂无说明'));
            $('#selectedVtPhysical').text(selectedVirtualTable.physical_table || '-');
            renderVirtualFields();
            renderRecordForm();
            loadRecords();
        }

        function renderVirtualFields() {
            var fields = selectedVirtualTable.fields || [];
            var html = '';

            fields.forEach(function (field) {
                html += '<div class="workspace-field-pill">'
                    + '<strong>' + escapeHtml(field.name) + '</strong>'
                    + '<span>' + escapeHtml(field.slug) + ' → ' + escapeHtml(field.physical_name) + '</span>'
                    + '<span>' + escapeHtml(field.type) + (field.nullable ? ' · 可空' : ' · 必填') + (field.indexed ? ' · 索引' : '') + '</span>'
                    + '</div>';
            });

            $('#virtualFieldList').html(html || '<div class="workspace-file-path">还没有字段，先添加字段后再录入数据。</div>');
        }

        function renderRecordForm(record) {
            if (!selectedVirtualTable) {
                return;
            }

            var fields = selectedVirtualTable.fields || [];
            var html = '';

            fields.forEach(function (field) {
                var value = record && record[field.physical_name] !== undefined && record[field.physical_name] !== null
                    ? record[field.physical_name]
                    : '';
                var input = '<input class="workspace-input record-input" data-slug="' + escapeHtml(field.slug) + '" type="text" value="' + escapeHtml(value) + '">';

                if (field.type === 'text' || field.type === 'json') {
                    input = '<textarea class="workspace-textarea record-input" data-slug="' + escapeHtml(field.slug) + '" rows="3">' + escapeHtml(value) + '</textarea>';
                } else if (field.type === 'boolean') {
                    input = '<select class="workspace-input record-input" data-slug="' + escapeHtml(field.slug) + '">'
                        + '<option value="0"' + (String(value) === '0' ? ' selected' : '') + '>否</option>'
                        + '<option value="1"' + (String(value) === '1' ? ' selected' : '') + '>是</option>'
                        + '</select>';
                } else if (field.type === 'date') {
                    input = '<input class="workspace-input record-input" data-slug="' + escapeHtml(field.slug) + '" type="date" value="' + escapeHtml(value) + '">';
                } else if (field.type === 'datetime') {
                    input = '<input class="workspace-input record-input" data-slug="' + escapeHtml(field.slug) + '" type="text" placeholder="YYYY-MM-DD HH:MM:SS" value="' + escapeHtml(value) + '">';
                } else if (field.type === 'integer' || field.type === 'decimal') {
                    input = '<input class="workspace-input record-input" data-slug="' + escapeHtml(field.slug) + '" type="number" value="' + escapeHtml(value) + '">';
                }

                html += '<label><span>' + escapeHtml(field.name) + ' <em style="font-style:normal;color:#98a6b7;">' + escapeHtml(field.slug) + '</em></span>' + input + '</label>';
            });

            $('#recordForm').html(html || '<div class="workspace-file-path">没有可录入字段</div>');
            $('#createRecordBtn').data('editing-id', record && record.id ? record.id : '');
            $('#createRecordBtn').text(record && record.id ? '保存修改' : '新增记录');
        }

        function collectRecordPayload() {
            var payload = {};
            $('#recordForm .record-input').each(function () {
                payload[$(this).data('slug')] = $(this).val();
            });
            payload._token = '{{ csrf_token() }}';
            return payload;
        }

        function loadRecords() {
            if (!selectedVirtualTable) {
                return;
            }

            $.get('/admin/applications/' + appId + '/virtual-tables/' + selectedVirtualTable.id + '/records', function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '加载记录失败');
                    return;
                }
                selectedRecords = response.data.items || [];
                renderRecords();
            }).fail(function (xhr) {
                $('#recordList').html('<div class="workspace-vt-empty" style="min-height:120px;">' + escapeHtml(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '加载记录失败') + '</div>');
            });
        }

        function renderRecords() {
            var fields = selectedVirtualTable ? (selectedVirtualTable.fields || []) : [];
            var html = '<table class="workspace-record-table"><thead><tr><th>ID</th>';

            fields.forEach(function (field) {
                html += '<th>' + escapeHtml(field.name) + '</th>';
            });

            html += '<th>更新时间</th><th>操作</th></tr></thead><tbody>';

            selectedRecords.forEach(function (record) {
                html += '<tr><td>' + record.id + '</td>';
                fields.forEach(function (field) {
                    html += '<td>' + escapeHtml(record[field.physical_name]) + '</td>';
                });
                html += '<td>' + escapeHtml(record.updated_at || '') + '</td>'
                    + '<td class="workspace-record-op">'
                    + '<button class="workspace-link-btn edit-record" type="button" data-id="' + record.id + '">编辑</button>'
                    + '<button class="workspace-link-btn delete-record" type="button" data-id="' + record.id + '">删除</button>'
                    + '</td></tr>';
            });

            if (!selectedRecords.length) {
                html += '<tr><td colspan="' + (fields.length + 3) + '">暂无记录</td></tr>';
            }

            html += '</tbody></table>';
            $('#recordList').html(html);
        }

        $('#fileList').on('click', '.workspace-file-item[data-id]', function () {
            selectFile($(this).data('id'));
            $('.workspace-shell').removeClass('files-open');
        });

        $('#workspaceTabs').on('click', '.workspace-tab[data-id]', function () {
            selectFile($(this).data('id'));
        });

        $('#fileSearch').on('input', renderFiles);

        $('#toggleFilesBtn').on('click', function () {
            $('.workspace-shell').toggleClass('files-open');
        });

        $('#toggleEditorFullscreenBtn').on('click', function () {
            var enabled = !$('.workspace-shell').hasClass('editor-fullscreen');
            $('.workspace-shell').toggleClass('editor-fullscreen', enabled).removeClass('files-open');
            $('body').toggleClass('workspace-editor-fullscreen', enabled);
            $(this).text(enabled ? '退出全屏' : '全屏');
            setTimeout(function () {
                editor.resize();
                editor.focus();
            }, 80);
        });

        $('#fileName, #filePath, #fileStatus').on('input change', function () {
            if (!currentFile) {
                return;
            }
            setDirty(true);
            updateStatusbar();
        });

        $('#fileType').on('change', function () {
            if (!currentFile) {
                return;
            }
            editor.session.setMode(modeByType(Number($(this).val())));
            setDirty(true);
            updateStatusbar();
            schedulePreviewRefresh();
        });

        $('.workspace-chip').on('click', function () {
            activeFileFilter = $(this).data('filter');
            $('.workspace-chip').removeClass('is-active');
            $(this).addClass('is-active');
            renderFiles();
        });

        document.addEventListener('keydown', function (event) {
            var isSave = (event.metaKey || event.ctrlKey) && String(event.key).toLowerCase() === 's';
            if (event.key === 'Escape') {
                $('.workspace-shell').removeClass('files-open');
                if ($('.workspace-shell').hasClass('editor-fullscreen')) {
                    $('.workspace-shell').removeClass('editor-fullscreen');
                    $('body').removeClass('workspace-editor-fullscreen');
                    $('#toggleEditorFullscreenBtn').text('全屏');
                    setTimeout(function () {
                        editor.resize();
                    }, 80);
                }
                return;
            }
            if (!isSave) {
                return;
            }
            event.preventDefault();
            $('#saveFileBtn').trigger('click');
        });

        $('#toggleMetaBtn').on('click', function () {
            $('#metaPanel').toggle();
        });

        $('#toggleDataTablesBtn').on('click', function () {
            $('#dataTablesPanel').toggle();
            if ($('#dataTablesPanel').is(':visible')) {
                loadVirtualTables();
            }
        });

        $('#reloadVirtualTablesBtn').on('click', function () {
            loadVirtualTables();
        });

        $('#virtualTableList').on('click', '.workspace-vt-item[data-id]', function () {
            var tableId = Number($(this).data('id'));
            selectedVirtualTable = virtualTables.find(function (table) {
                return Number(table.id) === tableId;
            }) || null;
            renderVirtualTables();
            renderVirtualTableDetail();
        });

        $('#createVirtualTableBtn').on('click', function () {
            $.ajax({
                url: '/admin/applications/' + appId + '/virtual-tables',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#vtName').val(),
                    slug: $('#vtSlug').val(),
                    description: $('#vtDescription').val(),
                    status: 1
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '创建虚拟表失败');
                    return;
                }
                $('#vtName, #vtSlug, #vtDescription').val('');
                selectedVirtualTable = response.data.table;
                loadVirtualTables();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '创建虚拟表失败');
            });
        });

        $('#createVirtualFieldBtn').on('click', function () {
            if (!selectedVirtualTable) {
                alert('请先选择虚拟表');
                return;
            }

            $.ajax({
                url: '/admin/applications/' + appId + '/virtual-tables/' + selectedVirtualTable.id + '/fields',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#vfName').val(),
                    slug: $('#vfSlug').val(),
                    type: $('#vfType').val(),
                    length: $('#vfLength').val(),
                    nullable: $('#vfNullable').is(':checked') ? 1 : 0,
                    default_enabled: 0,
                    default_value: '',
                    indexed: $('#vfIndexed').is(':checked') ? 1 : 0,
                    description: '',
                    sort_order: 0,
                    status: 1
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '添加字段失败');
                    return;
                }
                $('#vfName, #vfSlug, #vfLength').val('');
                $('#vfNullable').prop('checked', true);
                $('#vfIndexed').prop('checked', false);
                loadVirtualTables();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '添加字段失败');
            });
        });

        $('#createRecordBtn').on('click', function () {
            if (!selectedVirtualTable) {
                alert('请先选择虚拟表');
                return;
            }

            var editingId = $(this).data('editing-id');
            var payload = collectRecordPayload();
            var url = '/admin/applications/' + appId + '/virtual-tables/' + selectedVirtualTable.id + '/records';

            if (editingId) {
                payload._method = 'PUT';
                url += '/' + editingId;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: payload
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '保存记录失败');
                    return;
                }
                renderRecordForm();
                loadRecords();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '保存记录失败');
            });
        });

        $('#reloadRecordsBtn').on('click', function () {
            loadRecords();
        });

        $('#recordList').on('click', '.edit-record', function () {
            var recordId = Number($(this).data('id'));
            var record = selectedRecords.find(function (item) {
                return Number(item.id) === recordId;
            });
            if (record) {
                renderRecordForm(record);
            }
        });

        $('#recordList').on('click', '.delete-record', function () {
            if (!selectedVirtualTable) {
                return;
            }

            var recordId = Number($(this).data('id'));
            if (!window.confirm('确认删除这条记录吗？')) {
                return;
            }

            $.ajax({
                url: '/admin/applications/' + appId + '/virtual-tables/' + selectedVirtualTable.id + '/records/' + recordId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '删除记录失败');
                    return;
                }
                loadRecords();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '删除记录失败');
            });
        });

        $('#quickSaveBtn').on('click', function () {
            $('#saveFileBtn').trigger('click');
        });

        $('#quickPreviewBtn').on('click', function () {
            if (!currentFile || !currentFile.preview_url) {
                refreshPreview(true);
                return;
            }
            window.open(currentFile.preview_url, '_blank');
        });

        $('.workspace-device').on('click', function () {
            var device = $(this).data('device');
            $('.workspace-device').removeClass('is-active');
            $(this).addClass('is-active');
            $('.workspace-preview-canvas')
                .removeClass('device-desktop device-tablet device-phone')
                .addClass('device-' + device);
        });

        $('#saveMetaBtn').on('click', function () {
            $.ajax({
                url: '/admin/applications/' + appId + '/meta',
                method: 'POST',
                data: {
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}',
                    name: $('#metaName').val(),
                    slug: $('#metaSlug').val(),
                    description: $('#metaDescription').val(),
                    status: $('#metaStatus').val(),
                    auth_mode: $('#metaAuthMode').val(),
                    allowed_usernames: $('#metaAllowedUsernames').val()
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '保存失败');
                    return;
                }
                var app = response.data.application;
                $('#appTitle').text(app.name);
                $('#appSlug').text('/' + app.slug);
                alert('应用信息已保存');
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '保存失败');
            });
        });

        $('#saveFileBtn').on('click', function () {
            if (!currentFile) {
                alert('请先选择文件');
                return;
            }

            var payload = updateFileFromForm();

            $.ajax({
                url: '/admin/applications/' + appId + '/codes/' + currentFile.id,
                method: 'POST',
                data: $.extend(payload, {
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}'
                })
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '保存失败');
                    return;
                }

                currentFile = response.data.file;
                syncFileInList(currentFile);
                renderFiles();
                selectFile(currentFile.id);
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '保存失败');
            });
        });

        $('#newFileBtn').on('click', function () {
            $('#newFileModal').show();
        });

        $('#closeNewFileModal').on('click', function () {
            $('#newFileModal').hide();
        });

        $('#newFileModal').on('click', function (event) {
            if (event.target.id === 'newFileModal') {
                $('#newFileModal').hide();
            }
        });

        $('#submitNewFileBtn').on('click', function () {
            $.ajax({
                url: '/admin/applications/' + appId + '/codes',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#newFileName').val(),
                    path: $('#newFilePath').val(),
                    type: $('#newFileType').val(),
                    status: $('#newFileStatus').val(),
                    content: $('#newFileContent').val()
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '创建失败');
                    return;
                }

                syncFileInList(response.data.file);
                renderFiles();
                selectFile(response.data.file.id);
                $('#newFileModal').hide();
                $('#newFileName, #newFilePath, #newFileContent').val('');
                $('#newFileType').val('2');
                $('#newFileStatus').val('1');
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '创建失败');
            });
        });

        $('#refreshPreviewBtn').on('click', function () {
            refreshPreview(true);
        });

        $('#openHistoryBtn').on('click', function () {
            if (!currentFile) {
                alert('请先选择文件');
                return;
            }

            $.get('/admin/applications/' + appId + '/codes/' + currentFile.id + '/history', function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '加载失败');
                    return;
                }
                historyState.items = response.data.history || [];
                historyState.selectedId = null;
                renderHistoryList();
                $('#historyPreviewTitle').text('选择一个历史版本');
                $('#historyPreviewContent').text((response.data.current && response.data.current.content) || '');
                $('#rollbackHistoryBtn').hide();
                $('#historyModal').show();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '加载失败');
            });
        });

        $('#closeHistoryModal').on('click', function () {
            $('#historyModal').hide();
        });

        $('#historyModal').on('click', function (event) {
            if (event.target.id === 'historyModal') {
                $('#historyModal').hide();
            }
        });

        function renderHistoryList() {
            var html = '';
            if (!historyState.items.length) {
                html = '<div class="workspace-history-item"><div class="workspace-history-item-title">暂无历史版本</div></div>';
            } else {
                historyState.items.forEach(function (item) {
                    var active = Number(item.id) === Number(historyState.selectedId) ? ' active' : '';
                    html += '<div class="workspace-history-item' + active + '" data-id="' + item.id + '">'
                        + '<div class="workspace-history-item-title">版本 #' + item.id + '</div>'
                        + '<div class="workspace-history-item-time">' + escapeHtml(item.created_at || '') + '</div>'
                        + '</div>';
                });
            }
            $('#historyList').html(html);
        }

        $('#historyList').on('click', '.workspace-history-item[data-id]', function () {
            var historyId = Number($(this).data('id'));
            historyState.selectedId = historyId;
            renderHistoryList();
            var selected = historyState.items.find(function (item) {
                return Number(item.id) === historyId;
            });
            $('#historyPreviewTitle').text('版本 #' + historyId);
            $('#historyPreviewContent').text((selected && selected.content) || '');
            $('#rollbackHistoryBtn').show();
        });

        $('#rollbackHistoryBtn').on('click', function () {
            if (!historyState.selectedId || !currentFile) {
                return;
            }
            if (!window.confirm('确认回滚到这个历史版本吗？当前内容会被覆盖。')) {
                return;
            }

            $.ajax({
                url: '/admin/applications/' + appId + '/codes/' + currentFile.id + '/history/' + historyState.selectedId + '/rollback',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '回滚失败');
                    return;
                }
                currentFile = response.data.file;
                syncFileInList(currentFile);
                selectFile(currentFile.id);
                $('#historyModal').hide();
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '回滚失败');
            });
        });

        $('#openAiBtn').on('click', function () {
            if (!currentFile) {
                alert('请先选择文件');
                return;
            }
            $('#aiResult').val('');
            $('#aiPrompt').val('');
            $('#aiModal').show();
        });

        $('#closeAiModal').on('click', function () {
            $('#aiModal').hide();
        });

        $('#aiModal').on('click', function (event) {
            if (event.target.id === 'aiModal') {
                $('#aiModal').hide();
            }
        });

        $('#runAiBtn').on('click', function () {
            if (!currentFile) {
                alert('请先选择文件');
                return;
            }

            $('#runAiBtn').prop('disabled', true).text('生成中...');

            $.ajax({
                url: '/admin/applications/' + appId + '/codes/' + currentFile.id + '/ai-generate',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    mode: $('#aiMode').val(),
                    prompt: $('#aiPrompt').val()
                }
            }).done(function (response) {
                if (Number(response.code) !== 9999) {
                    alert(response.message || '生成失败');
                    return;
                }
                $('#aiResult').val(response.data.content || '');
            }).fail(function (xhr) {
                alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '生成失败');
            }).always(function () {
                $('#runAiBtn').prop('disabled', false).text('开始生成');
            });
        });

        $('#applyAiBtn').on('click', function () {
            var value = $('#aiResult').val();
            if (!value) {
                alert('还没有生成结果');
                return;
            }
            editor.setValue(value, -1);
            $('#aiModal').hide();
            setDirty(true);
        });

        $('#formatCodeBtn').on('click', function () {
            formatCurrentCode();
        });

        $('#filePath').on('blur', function () {
            var currentName = $.trim($('#fileName').val());
            if (!currentName) {
                $('#fileName').val(suggestTitleFromPath($(this).val()));
            }
        });

        $('#newFilePath').on('blur', function () {
            var currentName = $.trim($('#newFileName').val());
            if (!currentName) {
                $('#newFileName').val(suggestTitleFromPath($(this).val()));
            }
        });

        renderFiles();

        if (selectedCodeId) {
            selectFile(selectedCodeId);
        } else if (files.length > 0) {
            selectFile(files[0].id);
        } else {
            editor.session.setMode('ace/mode/html');
            editor.setValue('', -1);
            $('#previewHint').text('先创建一个文件');
            renderTabs();
            updateStatusbar();
        }
    })();
</script>
