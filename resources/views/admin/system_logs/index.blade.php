<div class="system-log-page">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">查询条件</h3>
        </div>
        <form method="get" action="{{ url('/admin/system-logs') }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>日志文件</label>
                            <select class="form-control" name="file">
                                @foreach($files as $file)
                                    <option value="{{ $file['name'] }}" {{ $selectedFile === $file['name'] ? 'selected' : '' }}>
                                        {{ $file['name'] }} ({{ $file['size_label'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>日志级别</label>
                            <select class="form-control" name="level">
                                <option value="">全部</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>排序</label>
                            <select class="form-control" name="order">
                                <option value="desc" {{ request('order', 'desc') === 'desc' ? 'selected' : '' }}>倒序：最新在前</option>
                                <option value="asc" {{ request('order') === 'asc' ? 'selected' : '' }}>正序：最旧在前</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>显示行数</label>
                            <select class="form-control" name="lines">
                                @foreach($lineOptions as $line)
                                    <option value="{{ $line }}" {{ (int) request('lines', 500) === $line ? 'selected' : '' }}>{{ $line }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>关键词</label>
                            <input class="form-control" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="错误信息、SQL、类名">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group system-log-actions">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 查询</button>
                            <a class="btn btn-default" href="{{ url('/admin/system-logs') }}"><i class="fa fa-refresh"></i> 最新</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if($error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        @if($result)
                            {{ $result['file'] }} / {{ $result['size_label'] }}
                        @else
                            日志内容
                        @endif
                    </h3>
                    <div class="box-tools pull-right">
                        <button id="copySystemLog" class="btn btn-box-tool" type="button" title="复制当前内容">
                            <i class="fa fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    @if($result)
                        <div class="system-log-meta">
                            更新时间：{{ $result['modified_at'] }} /
                            显示：{{ $result['matched_count'] }} 条 /
                            扫描：{{ $result['scan_size_label'] }} /
                            排序：{{ $result['order'] === 'desc' ? '倒序' : '正序' }}
                            @if($result['truncated'])
                                <span class="label label-warning">仅搜索最近部分日志</span>
                            @endif
                        </div>
                        <pre id="systemLogContent" class="system-log-content">{{ $result['content'] }}</pre>
                    @else
                        <div class="text-muted">请选择日志文件</div>
                        <pre id="systemLogContent" class="system-log-content" style="display:none;"></pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .system-log-page .system-log-actions {
        padding-top: 25px;
        white-space: nowrap;
    }
    .system-log-meta {
        color: #666;
        margin-bottom: 10px;
    }
    .system-log-content {
        min-height: 720px;
        height: calc(100vh - 285px);
        max-height: none;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
        background: #111827;
        color: #d1d5db;
        border: 0;
        border-radius: 4px;
        padding: 14px;
        font-size: 12px;
        line-height: 1.6;
    }
</style>

<script>
    (function () {
        var button = document.getElementById('copySystemLog');
        var content = document.getElementById('systemLogContent');

        if (!button || !content) {
            return;
        }

        button.addEventListener('click', function () {
            var text = content.innerText || content.textContent || '';
            if (!text) {
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                return;
            }

            var textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        });
    })();
</script>
