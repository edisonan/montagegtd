@extends('layouts.app')

@section('title', '记录想法 - 蒙太奇')
@section('description', '记录和分享您的思考、笔记和灵感，支持文字、语音、图片多种形式')

@section('content')
    @include('components.ai-ask-modal')

    <style>
        /* 录音相关样式 */
        .audio-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }

        .audio-controls button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: white;
            font-size: 18px;
        }

        .audio-controls #start {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .audio-controls #start:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .audio-controls #start:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .audio-controls #stop {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .audio-controls #stop:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .audio-controls #stop:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .audio-timer {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            background: var(--gray-100);
            padding: 8px 16px;
            border-radius: 20px;
            min-width: 80px;
            text-align: center;
        }

        .audio-recording-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 8px;
            margin-top: 12px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* 笔记标签 */
        .note-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .tag-badge {
            padding: 6px 12px;
            background: var(--gray-100);
            color: var(--gray-700);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .tag-badge:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
            border-color: var(--gray-300);
        }

        /* 笔记卡片 */
        .note-card {
            transition: all 0.2s ease;
            border: 1px solid var(--gray-200);
            margin-bottom: 16px;
        }

        .note-card:hover {
            border-color: var(--gray-300);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .note-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px 12px;
            border-bottom: 1px solid var(--gray-100);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--gray-900);
        }

        .note-time {
            font-size: 13px;
            color: var(--gray-500);
        }

        .note-status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        .status-private {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .status-public {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .note-operations {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .operation-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .operation-btn:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .operation-btn.edit:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .operation-btn.delete:hover {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .operation-btn.like:hover {
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .note-body {
            padding: 20px;
            line-height: 1.6;
            color: var(--gray-800);
            position: relative;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .note-body.has-collapse {
            max-height: 200px;
            cursor: pointer;
        }

        .note-body.has-collapse::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0.9));
            pointer-events: none;
        }

        .note-body.has-collapse:hover::after {
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(248,250,252,0.9));
        }

        .expand-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background: var(--gray-50);
            border-radius: 8px;
            margin-top: 12px;
            color: var(--gray-600);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
        }

        .expand-indicator:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
        }

        .expand-indicator i {
            margin-left: 4px;
            transition: transform 0.2s ease;
        }

        .note-body.expanded .expand-indicator i {
            transform: rotate(180deg);
        }

        /* 媒体内容 */
        .note-media {
            margin-top: 16px;
            margin-bottom: 16px;
        }

        .audio-player {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .audio-player-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .audio-player-btn:hover {
            background: var(--primary-color);
            transform: scale(1.05);
        }

        .audio-duration {
            font-size: 14px;
            color: var(--gray-600);
            margin-left: auto;
        }

        .note-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 12px;
            border: 1px solid var(--gray-200);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .note-image:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* 发布按钮 */
        .publish-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-top: 1px solid var(--gray-100);
        }

        .publish-btns {
            display: flex;
            gap: 12px;
        }

        .ai-assist-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }

        .ai-assist-btn:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: translateY(-1px);
        }
    </style>

    <script src="/js/recorder/recorder.js"></script>
    <script>
        let recorder = null;
        let timerInterval = null;
        let startTime = null;
        let isRecording = false;

        function submitProcess(status) {
            document.getElementById('status_id').value = status;
            document.getElementById('add_note_form').submit();
        }

        function addContent(content) {
            let noteInput = document.getElementById('note-name');
            if (content === 'code') {
                noteInput.value += "\n```\n// 在这里输入代码\n```\n";
            } else {
                noteInput.value += content + ' ';
            }
            noteInput.focus();
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
            return `${mins}:${secs}`;
        }

        function updateTimer() {
            if (!startTime) return;

            const currentTime = new Date();
            const elapsedSeconds = Math.floor((currentTime - startTime) / 1000);
            const timerDisplay = document.getElementById('audio-timer');

            if (timerDisplay) {
                timerDisplay.textContent = formatTime(elapsedSeconds);
            }
        }

        function showRecordingIndicator() {
            const container = document.getElementById('audio-container');
            container.innerHTML = `
            <div class="audio-recording-indicator" id="recording-indicator">
                <div class="pulse-dot"></div>
                <span style="color: var(--danger-color); font-weight: 500;">正在录音中...</span>
                <span id="audio-timer">00:00</span>
            </div>
        `;
        }

        function hideRecordingIndicator() {
            const indicator = document.getElementById('recording-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        function startRecording() {
            if (!recorder) {
                alert('录音功能初始化失败，请刷新页面重试');
                return;
            }

            try {
                // 检查浏览器权限
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(function(stream) {
                        // 开始录音
                        recorder.start();

                        // 更新UI状态
                        document.getElementById('start').disabled = true;
                        document.getElementById('start').style.display = 'none';
                        document.getElementById('stop').disabled = false;
                        document.getElementById('stop').style.display = 'inline-flex';

                        // 开始计时
                        startTime = new Date();
                        timerInterval = setInterval(updateTimer, 1000);
                        isRecording = true;

                        // 显示录音指示器
                        showRecordingIndicator();
                    })
                    .catch(function(err) {
                        console.error('麦克风权限被拒绝:', err);
                        alert('需要麦克风权限才能录音。请在浏览器设置中允许麦克风访问。');
                        resetRecordingState();
                    });
            } catch (error) {
                console.error('录音启动失败:', error);
                alert('录音功能启动失败，请确保浏览器支持录音功能');
                resetRecordingState();
            }
        }

        function stopRecording() {
            if (!recorder || !isRecording) return;

            try {
                // 停止录音
                recorder.stop();
                isRecording = false;

                // 停止计时
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }

                // 获取录音数据
                recorder.getBlob(function(blob) {
                    // 处理录音数据
                    processRecordingBlob(blob);
                });

                // 隐藏录音指示器
                hideRecordingIndicator();

            } catch (error) {
                console.error('停止录音失败:', error);
                alert('停止录音失败');
            } finally {
                resetRecordingState();
            }
        }

        function processRecordingBlob(blob) {
            // 创建音频播放器
            const container = document.getElementById('audio-container');
            const audioUrl = URL.createObjectURL(blob);
            const duration = Math.round(blob.size / (128 * 1000) * 8); // 估算时长

            container.innerHTML = `
            <div class="audio-player">
                <button class="audio-player-btn" onclick="playAudio(this)">
                    <i class="fas fa-play"></i>
                </button>
                <div style="flex: 1">
                    <div style="font-weight: 500; color: var(--gray-700); margin-bottom: 4px;">录音回放</div>
                    <div style="font-size: 13px; color: var(--gray-500);">点击播放按钮收听录音</div>
                </div>
                <div class="audio-duration">${formatTime(duration)}</div>
                <audio id="recorded-audio" src="${audioUrl}" preload="metadata"></audio>
            </div>
        `;

            // 设置输入框内容
            const noteInput = document.getElementById('note-name');
            if (!noteInput.value.includes("#分享语音#")) {
                noteInput.value = noteInput.value ? noteInput.value + "\n#分享语音#" : "#分享语音#";
            }

            // 上传音频文件
            uploadAudioFile(blob);
        }

        function playAudio(button) {
            const audio = document.getElementById('recorded-audio');
            const icon = button.querySelector('i');

            if (audio.paused) {
                audio.play();
                icon.className = 'fas fa-pause';
                button.onclick = function() { pauseAudio(button); };
            } else {
                audio.pause();
                icon.className = 'fas fa-play';
                button.onclick = function() { playAudio(button); };
            }
        }

        function pauseAudio(button) {
            const audio = document.getElementById('recorded-audio');
            const icon = button.querySelector('i');

            audio.pause();
            icon.className = 'fas fa-play';
            button.onclick = function() { playAudio(button); };
        }

        function uploadAudioFile(blob) {
            const formData = new FormData();
            const filename = '{{ md5(date('YmdHis').rand(0,99)) }}.mp3';

            formData.append('file', blob, filename);
            formData.append('_token', "{{ csrf_token() }}");

            $.ajax({
                url: '{{ url("notes/upload") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    // 可以在这里显示上传进度
                }
            }).done(function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.code == 9999) {
                        document.getElementById('fname').value = filename;
                        showToast('音频上传成功', 'success');
                    } else {
                        showToast('音频上传失败: ' + (data.msg || '未知错误'), 'error');
                    }
                } catch (e) {
                    showToast('音频上传失败: 响应格式错误', 'error');
                }
            }).fail(function() {
                showToast('音频上传失败: 网络错误', 'error');
            });
        }

        function resetRecordingState() {
            document.getElementById('start').disabled = false;
            document.getElementById('start').style.display = 'inline-flex';
            document.getElementById('stop').disabled = true;
            document.getElementById('stop').style.display = 'none';

            startTime = null;
            isRecording = false;
        }

        function showToast(message, type = 'info') {
            // 实现一个简单的toast提示
            const toast = document.createElement('div');
            toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#4a90e2'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
        `;

            toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // 页面加载时初始化录音器
        document.addEventListener('DOMContentLoaded', function() {
            try {
                recorder = new Recorder({
                    sampleRate: 44100,
                    bitRate: 128,
                    success: function() {
                        console.log('录音器初始化成功');
                        document.getElementById('start').disabled = false;
                    },
                    error: function(msg) {
                        console.error('录音器初始化失败:', msg);
                        document.getElementById('start').disabled = true;
                        document.getElementById('start').innerHTML = '<i class="fas fa-microphone-slash"></i>';
                        document.getElementById('start').title = '录音功能不可用';
                    }
                });

                // 绑定按钮事件
                document.getElementById('start').addEventListener('click', startRecording);
                document.getElementById('stop').addEventListener('click', stopRecording);

                // 输入框自动调整高度
                const textarea = document.getElementById('note-name');
                if (textarea) {
                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = Math.min(this.scrollHeight, 300) + 'px';
                    });
                }
            } catch (error) {
                console.error('页面初始化失败:', error);
            }
        });
    </script>

    <!-- 注意：这里使用了 max-w-7xl 与其他页面保持一致 -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">记录想法</h1>
            <p class="text-gray-600">记录您的思考、灵感和笔记，支持文字、语音和图片多种形式</p>
        </div>

        <!-- 新建笔记卡片 -->
        <div class="card card-elevated mb-8">
            <form id="add_note_form" action="{{ url('note') }}" method="POST">
                {{ csrf_field() }}

                <div class="p-6">
                <textarea id="note-name"
                          name="name"
                          class="input w-full min-h-[120px] max-h-[300px] resize-y"
                          placeholder="记录下您的想法、灵感或学习笔记...">{{ $add_content }}</textarea>

                    <!-- 标签快捷输入 -->
                    <div class="note-tags">
                        <div class="tag-badge" onclick="addContent('#每日小目标#')">#每日小目标#</div>
                        <div class="tag-badge" onclick="addContent('#每日总结#')">#每日总结#</div>
                        <div class="tag-badge" onclick="addContent('#读书笔记#')">#读书笔记#</div>
                        <div class="tag-badge" onclick="addContent('#工作思考#')">#工作思考#</div>
                        <div class="tag-badge" onclick="addContent('#碎碎念#')">#碎碎念#</div>
                        <div class="tag-badge" onclick="addContent('#灵感闪现#')">#灵感闪现#</div>
                        <div class="tag-badge" onclick="addContent('#会议记录#')">#会议记录#</div>
                        <div class="tag-badge" onclick='addContent("code")'>
                            <i class="fas fa-code mr-1"></i>代码片段
                        </div>
                    </div>

                    <!-- 录音控制 -->
                    <div class="audio-controls">
                        <button type="button" id="start" disabled title="开始录音">
                            <i class="fas fa-microphone"></i>
                        </button>
                        <button type="button" id="stop" disabled title="停止录音" style="display:none;">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>

                    <!-- 音频容器 -->
                    <div id="audio-container"></div>

                    <!-- 图片预览 -->
                    @if(!empty($add_image))
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-gray-700">图片预览</span>
                                <button type="button" onclick="this.parentElement.parentElement.remove()"
                                        class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <img src="{{ $add_image }}"
                                 alt="预览图片"
                                 class="rounded-lg max-h-[200px] mx-auto">
                            <input type="hidden" name="add_image" value="{{ $add_image }}">
                        </div>
                    @endif
                </div>

                <!-- 隐藏字段 -->
                <input type="hidden" name="source_type" value="{{ $source_type }}">
                <input type="hidden" name="source_id" value="{{ $source_id }}">
                <input type="hidden" name="fname" id="fname">
                <input type="hidden" name="status" id="status_id">

                <!-- 发布操作 -->
                <div class="publish-actions">
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>支持文字、语音、图片多种格式</span>
                    </div>
                    <div class="publish-btns">
                        <button type="button"
                                class="btn btn-outline"
                                onclick="submitProcess(1)">
                            <i class="fas fa-lock mr-2"></i>私密保存
                        </button>
                        <button type="button"
                                class="btn btn-primary"
                                onclick="submitProcess(2)">
                            <i class="fas fa-globe mr-2"></i>公开发布
                        </button>
                        <button type="button"
                                class="btn ai-assist-btn"
                                onclick="openAskAIModal('note-name', 'note-name')">
                            <i class="fas fa-robot mr-2"></i>AI润色
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 笔记列表 -->
        @if(count($notes) > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-comments text-blue-500 mr-2"></i>
                        社区动态
                    </h2>
                    <div class="text-sm text-gray-500">
                        共 {{ $notes->total() }} 条笔记
                    </div>
                </div>

                @foreach ($notes as $note)
                    <div class="note-card card" id="note-{{ $note->id }}">
                        <!-- 笔记头部 -->
                        <div class="note-header">
                            <div class="user-info">
                                <div class="user-avatar" style="background: linear-gradient(135deg, {{ $note->user->color_1 ?? '#4a90e2' }}, {{ $note->user->color_2 ?? '#8a6cff' }})">
                                    {{ substr($note->user->name, 0, 1) }}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">
                                        {{ $note->user->name }}
                                        <span class="note-status-badge {{ $note->status == 2 ? 'status-public' : 'status-private' }}">
                                {{ $note->status == 2 ? '公开' : '私密' }}
                            </span>
                                    </div>
                                    <div class="note-time">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $note->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <!-- 操作按钮 -->
                            <div class="note-operations">
                                @if($note->user_id == Auth::id())
                                    <button class="operation-btn edit"
                                            onclick="window.location='{{ url('noteupdate/'.$note->id) }}'"
                                            title="编辑笔记">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="operation-btn delete delete_note"
                                            note_value="{{ $note->id }}"
                                            note_token="{{ csrf_token() }}"
                                            title="删除笔记">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button class="operation-btn like like_note"
                                            note_value="{{ $note->id }}"
                                            note_token="{{ csrf_token() }}"
                                            title="点赞">
                                        <i class="far fa-thumbs-up"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- 笔记内容 -->
                        <div class="note-body" id="note-body-{{ $note->id }}">
                            <!-- 语音记录 -->
                            @if(!empty($note->record_path) && ($note->user_id == Auth::id() || $note->status == 2))
                                <div class="note-media">
                                    <div class="audio-player">
                                        <button class="audio-player-btn"
                                                onclick="playNoteAudio('{{ $note->id }}', this)">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <div style="flex: 1">
                                            <div style="font-weight: 500; color: var(--gray-700); margin-bottom: 4px;">语音记录</div>
                                            <div style="font-size: 13px; color: var(--gray-500);">点击播放收听</div>
                                        </div>
                                        <audio id="audio-{{ $note->id }}"
                                               src="{{ url('note/getRecord/'.$note->id) }}"
                                               preload="metadata"></audio>
                                    </div>
                                </div>
                            @endif

                            <!-- 图片 -->
                            @if(!empty($note->image_path) && ($note->user_id == Auth::id() || $note->status == 2))
                                <div class="note-media">
                                    <a href="{{ $note->image_path }}" target="_blank">
                                        <img src="{{ $note->image_path }}"
                                             alt="笔记图片"
                                             class="note-image">
                                    </a>
                                </div>
                            @endif

                            <!-- 文本内容 -->
                            <div id="note-content-{{ $note->id }}">
                                {!! App\Http\Utils\CommonUtil::formatContentHtml($note->name) !!}
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- 分页 - 使用自定义分页 -->
                @if($notes->hasPages())
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col md:flex-row items-center justify-between">
                            <div class="text-sm text-gray-500 mb-4 md:mb-0">
                                显示 {{ $notes->firstItem() }} - {{ $notes->lastItem() }} 条，共 {{ $notes->total() }} 条记录
                            </div>
                            <div class="flex items-center space-x-2">
                                {{-- 上一页 --}}
                                @if($notes->onFirstPage())
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                @else
                                    <a href="{{ $notes->previousPageUrl() }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif

                                {{-- 页码 --}}
                                @php
                                    $current = $notes->currentPage();
                                    $last = $notes->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $start + 4);
                                    $start = max(1, $end - 4);
                                @endphp

                                @if($start > 1)
                                    <a href="{{ $notes->url(1) }}" class="btn btn-sm btn-outline">1</a>
                                    @if($start > 2)
                                        <span class="px-3 py-1">...</span>
                                    @endif
                                @endif

                                @for($page = $start; $page <= $end; $page++)
                                    @if($page == $current)
                                        <button class="btn btn-sm btn-primary">{{ $page }}</button>
                                    @else
                                        <a href="{{ $notes->url($page) }}" class="btn btn-sm btn-outline">{{ $page }}</a>
                                    @endif
                                @endfor

                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <span class="px-3 py-1">...</span>
                                    @endif
                                    <a href="{{ $notes->url($last) }}" class="btn btn-sm btn-outline">{{ $last }}</a>
                                @endif

                                {{-- 下一页 --}}
                                @if($notes->hasMorePages())
                                    <a href="{{ $notes->nextPageUrl() }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- 空状态 -->
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gray-100 mb-6">
                    <i class="fas fa-sticky-note text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">还没有人分享笔记</h3>
                <p class="text-gray-600 mb-6">发布您的第一条笔记，与社区分享您的想法吧！</p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('note-name').focus()">
                    <i class="fas fa-plus mr-2"></i>
                    开始记录
                </button>
            </div>
        @endif
    </div>

    <script>
        $(document).ready(function(){
            // 删除笔记
            $(document).on('click', '.delete_note', function(){
                if(!confirm('确认要删除此笔记吗？删除后无法恢复。')) return;

                const noteId = $(this).attr('note_value');
                const token = $(this).attr('note_token');

                $.ajax({
                    url: "{{ url('note') }}/" + noteId,
                    type: 'DELETE',
                    data: {_token: token, type: 'delete'},
                    success: function(response) {
                        if (response.code == 9999) {
                            // 移除笔记元素
                            $('#note-' + noteId).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('删除失败，请稍后重试');
                        }
                    },
                    error: function() {
                        alert('网络错误，请稍后重试');
                    }
                });
            });

            // 点赞笔记
            $(document).on('click', '.like_note', function(){
                const noteId = $(this).attr('note_value');
                const token = $(this).attr('note_token');
                const button = $(this);

                $.ajax({
                    url: "{{ url('note') }}/" + noteId + "/like",
                    type: 'POST',
                    data: {_token: token},
                    beforeSend: function() {
                        button.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.code == 9999) {
                            button.html('<i class="fas fa-thumbs-up"></i>');
                            button.attr('title', '已点赞');
                            button.removeClass('like_note');
                        } else {
                            alert('操作失败: ' + (response.msg || '未知错误'));
                        }
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });

            // 笔记内容折叠展开
            $('.note-body').each(function() {
                const $content = $(this).find('#note-content-' + $(this).attr('id').replace('note-body-', ''));
                const contentHeight = $content.prop('scrollHeight');
                const noteId = $(this).attr('id').replace('note-body-', '');

                if (contentHeight > 200) {
                    $content.addClass('has-collapse');

                    // 添加展开/折叠按钮
                    $(this).append(`
                <div class="expand-indicator" onclick="toggleNoteExpand('${noteId}')">
                    <span>展开全文</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            `);
                }
            });

            // 图片灯箱效果
            $(document).on('click', '.note-image', function(e) {
                e.stopPropagation();

                const src = $(this).attr('src');
                const lightbox = `
            <div class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="$(this).remove()">
                <div class="max-w-4xl max-h-full">
                    <img src="${src}" class="max-w-full max-h-full object-contain rounded-lg" onclick="event.stopPropagation()">
                    <button class="absolute top-4 right-4 text-white text-2xl" onclick="$(this).closest('.fixed').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

                $('body').append(lightbox);
            });
        });

        function toggleNoteExpand(noteId) {
            const $content = $('#note-content-' + noteId);
            const $indicator = $('#note-body-' + noteId).find('.expand-indicator');

            if ($content.hasClass('has-collapse')) {
                $content.removeClass('has-collapse').css('max-height', 'none');
                $indicator.find('span').text('收起全文');
                $indicator.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $content.addClass('has-collapse').css('max-height', '200px');
                $indicator.find('span').text('展开全文');
                $indicator.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        }

        function playNoteAudio(noteId, button) {
            const audio = document.getElementById('audio-' + noteId);
            const icon = button.querySelector('i');

            if (audio.paused) {
                audio.play();
                icon.className = 'fas fa-pause';
                button.onclick = function() { pauseNoteAudio(noteId, button); };
            } else {
                audio.pause();
                icon.className = 'fas fa-play';
                button.onclick = function() { playNoteAudio(noteId, button); };
            }
        }

        function pauseNoteAudio(noteId, button) {
            const audio = document.getElementById('audio-' + noteId);
            const icon = button.querySelector('i');

            audio.pause();
            icon.className = 'fas fa-play';
            button.onclick = function() { playNoteAudio(noteId, button); };
        }
    </script>
@endsection