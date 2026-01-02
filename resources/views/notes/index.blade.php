@extends('layouts.app')

@section('content')
    <style>
        /* ================== 基础风格 ================== */
        body {
            background: #fdfdfd;
            font-family: 'Noto Sans', Arial, sans-serif;
            color: #4a4a4a;
        }

        .container {
            max-width: 1140px;
        }

        /* ================== 笔记创建区 ================== */
        .card.note-card {
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .note-card textarea {
            width: 100%;
            resize: vertical;
            min-height: 120px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .note-tags {
            margin-top: 10px;
        }

        .note-tags .tag {
            display: inline-block;
            background: #e6f5ea;
            color: #39b54a;
            padding: 5px 10px;
            margin: 3px 5px 3px 0;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .note-tags .tag:hover {
            background: #39b54a;
            color: #fff;
        }

        .audio-controls {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .audio-controls button {
            background: #39b54a;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .audio-controls button#stop {
            background: #e74c3c;
        }
        
        .audio-timer {
            margin-left: 10px;
            font-size: 14px;
            color: #666;
            display: inline-block;
            vertical-align: middle;
        }

        .audio-controls button:hover {
            transform: scale(1.1);
        }

        #audio-container audio {
            display: block;
            margin-top: 10px;
            width: 100%;
        }

        /* ================== 发布按钮 ================== */
        .publish-btns {
            margin-top: 15px;
        }

        .publish-btns button {
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .publish-btns button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ================== 笔记列表 ================== */
        .card.note-list-card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 15px;
            padding: 15px;
            position: relative;
            transition: all 0.2s;
        }

        .card.note-list-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .note-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .note-header .user-info {
            display: flex;
            align-items: center;
        }

        .note-header img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .note-time {
            font-size: 0.85rem;
            color: #888;
        }

        .note-status {
            font-weight: 500;
            font-size: 0.85rem;
            margin-left: 10px;
        }

        .note-body {
            font-size: 16px;
            margin-top: 5px;
            line-height: 1.6;
        }

        .note-body img {
            max-height: 150px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .note-operations {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .note-operations i {
            font-size: 1.4rem;
            margin-left: 10px;
            cursor: pointer;
            color: #555;
            transition: all 0.2s;
        }

        .note-operations i:hover {
            color: #39b54a;
        }

        .note-body {
            font-size: 16px;
            margin-top: 5px;
            line-height: 1.6;
            transition: max-height 0.3s ease-in-out;
        }
        
        .note-body.has-collapse {
            max-height: 200px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: max-height 0.3s ease-in-out;
        }

        .note-body.has-collapse::after {
            content: '﹀';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: linear-gradient(transparent, #fdfdfd);
            text-align: center;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 10px;
            color: #88c888;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2em;
        }

        .note-body.has-collapse:hover::after {
            background: linear-gradient(transparent, #f0f0f0);
            color: #66b266;
        }

        .note-body.expanded {
            max-height: none;
            overflow: visible;
        }
        
        .note-body.expanded::after {
            content: '︿';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px 0;
            color: #88c888;
            font-weight: bold;
            cursor: pointer;
            background: #f8f8f8;
            transition: all 0.3s ease;
            font-size: 1.2em;
        }
        
        .note-body.expanded:hover::after {
            background: #e8e8e8;
            color: #66b266;
        }

        /* ================== 图片缩略图 ================== */
        .note-body img.thumbnail {
            max-height: 100px;
            cursor: pointer;
        }
    </style>

    <script src="/js/recorder/recorder.js"></script>
    <script>
        function submitProcess(status) {
            document.getElementById('status_id').value = status;
            document.getElementById('add_note_form').submit();
        }

        function addContent(content) {
            let note_name = document.getElementById('note-name');
            if(content === 'code') {
                note_name.value += "\n<code>\n</code>";
            } else {
                note_name.value += content;
            }
        }

        window.onload = function () {
            let startBtn = document.getElementById('start');
            let stopBtn = document.getElementById('stop');
            let container = document.getElementById('audio-container');
            let timerDisplay = document.getElementById('audio-timer');
            
            let startTime;
            let timerInterval;

            let recorder = new Recorder({
                sampleRate: 44100,
                bitRate: 128,
                success: function () { startBtn.disabled = false; },
                error: function () { startBtn.value = '录音不支持'; },
                fix: function () { startBtn.value = '录音不支持'; }
            });

            startBtn.addEventListener('click', function () {
                this.disabled = true;
                stopBtn.disabled = false;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
                
                // 开始计时
                startTime = new Date();
                timerDisplay.textContent = '00:00';
                timerInterval = setInterval(function() {
                    let currentTime = new Date();
                    let elapsedTime = Math.floor((currentTime - startTime) / 1000);
                    let minutes = Math.floor(elapsedTime / 60);
                    let seconds = elapsedTime % 60;
                    
                    let formattedTime = ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2);
                    timerDisplay.textContent = formattedTime;
                }, 1000);
                
                let audios = document.querySelectorAll('audio');
                audios.forEach(a => { if(!a.paused) a.pause(); });
                recorder.start();
            });

            stopBtn.addEventListener('click', function () {
                this.disabled = true;
                startBtn.disabled = false;
                stopBtn.style.display = 'none';
                startBtn.style.display = 'inline-block';
                
                // 停止计时
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                
                recorder.stop();
                recorder.getBlob(function (blob) {
                    if (!document.getElementById('note-name').value.includes("#分享语音#")) {
                        document.getElementById('note-name').value = "#分享语音#";
                    }

                    container.innerHTML = '';
                    let audio = document.createElement('audio');
                    audio.src = URL.createObjectURL(blob);
                    audio.controls = true;
                    container.appendChild(audio);

                    // 上传音频
                    let fd = new FormData();
                    let fname = '{{ md5(date('YmdHis').rand(0,99)) }}';
                    fd.append('fname', fname);
                    fd.append('file', blob);
                    fd.append('_token', "{{ csrf_token() }}");

                    $.ajax({
                        type: 'POST',
                        url: '{{ url("notes/upload") }}',
                        data: fd,
                        processData: false,
                        contentType: false
                    }).done(function(data){
                        let data_arr = JSON.parse(data);
                        if(data_arr.code == 9999){
                            document.getElementById('fname').value = fname;
                        }
                    });
                });
            });
        };
    </script>

    <div class="container">
        <!-- ================== 新建笔记 ================== -->
        <div class="card note-card">
            <form id="add_note_form" action="{{ url('note') }}" method="POST">
                {{ csrf_field() }}
                <textarea id="note-name" name="name" placeholder="记录下你的想法...">{{ $add_content }}</textarea>

                <div class="note-tags">
                    <span class="tag" onclick="addContent('#每日小目标#')">#每日小目标#</span>
                    <span class="tag" onclick="addContent('#每日总结#')">#每日总结#</span>
                    <span class="tag" onclick="addContent('#读书笔记#')">#读书笔记#</span>
                    <span class="tag" onclick="addContent('#分享#')">#分享#</span>
                    <span class="tag" onclick="addContent('#碎碎念#')">#碎碎念#</span>
                    <span class="tag" onclick='addContent("code")'>[代码片段]</span>
                </div>

                <div class="audio-controls">
                    <button type="button" id="start" disabled title="开始录音"><i class="fa fa-microphone"></i></button>
                    <button type="button" id="stop" disabled title="停止录音" style="display:none;"><i class="fa fa-stop"></i></button>
                    <span class="audio-timer" id="audio-timer">00:00</span>
                </div>
                <div id="audio-container"></div>

                @if(!empty($add_image))
                    <div style="margin-top:10px">
                        <span>预览：</span>
                        <img src="{{ $add_image }}" alt="preview" height="150px">
                        <input type="hidden" name="add_image" value="{{ $add_image }}">
                    </div>
                @endif

                <input type="hidden" name="source_type" value="{{ $source_type }}">
                <input type="hidden" name="source_id" value="{{ $source_id }}">
                <input type="hidden" name="fname" id="fname">
                <input type="hidden" name="status" id="status_id">

                <div class="publish-btns">
                    <button type="button" class="btn btn-primary" onclick="submitProcess(1)">私密发布</button>
                    <button type="button" class="btn btn-secondary" onclick="submitProcess(2)">公开发布</button>
                </div>
            </form>
        </div>

        <!-- ================== 笔记列表 ================== -->
        @if(count($notes) > 0)
            <h5 style="margin:15px 0">大家在分享什么</h5>
            @foreach ($notes as $note)
                <div class="card note-list-card" id="{{ $note->id }}">
                    <div class="note-header">
                        <div class="user-info">
                            <img src="https://gravatar.loli.net/avatar/{{ md5(strtolower(trim($note->user->email))) }}?s=40">
                            <strong>{{ $note->user->name }}</strong>
                            <span class="note-status" style="color: {{ $note->status == 2 ? 'green' : '#f98282' }}">
                            {{ $note->status == 2 ? '公开' : '仅个人可见' }}
                        </span>
                        </div>
                        <div class="note-time">{{ date('Y-m-d H:i', strtotime($note->created_at)) }}</div>
                        <div class="note-operations">
                            @if($note->user_id == Auth::user()->id)
                                <i class="fa fa-edit" onclick="window.location='{{ url('noteupdate/'.$note->id) }}'"></i>
                                <i class="fa fa-trash-o delete_note" note_value="{{ $note->id }}" note_token="{{ csrf_token() }}" note_type="delete"></i>
                            @else
                                <i class="bi-hand-thumbs-up like_note" note_value="{{ $note->id }}" note_token="{{ csrf_token() }}" note_type="like"></i>
                            @endif
                        </div>
                    </div>
                    <div class="note-body">
                        @if(!empty($note->record_path) && ($note->user_id == Auth::user()->id  || $note->status == 2))
                            <div style="margin-top:5px">语音记录: <a href="{{ url('note/getRecord/'.$note->id) }}">播放🎵</a></div>
                        @endif

                        @if(!empty($note->image_path) && ($note->user_id == Auth::user()->id  || $note->status == 2))
                            <a href="{{ $note->image_path }}" target="_blank">
                                <img src="{{ $note->image_path }}" class="thumbnail">
                            </a>
                        @endif

                        {!! App\Http\Utils\CommonUtil::formatContentHtml($note->name) !!}
                    </div>
                </div>
            @endforeach
            {!! $notes->links() !!}
        @endif
    </div>

    <script>
        $(document).ready(function(){
            $(".delete_note").click(function(){
                if(!confirm('确认要删除此笔记吗？')) return;
                let note_id = $(this).attr('note_value');
                let token = $(this).attr('note_token');
                $.ajax({
                    url: "{{ url('note') }}/" + note_id,
                    type: 'DELETE',
                    data: {_token: token, type:'delete'},
                    success: function(res){
                        if(res.code == 9999){
                            $('#' + note_id).remove();
                        } else {
                            alert('删除失败，请稍后重试');
                        }
                    }
                });
            });

            // 长文本折叠展开
            $(".note-body").each(function(){
                var $this = $(this);
                
                // 创建临时元素进行准确测量，确保移除所有可能影响高度的样式
                var $tempDiv = $this.clone()
                    .css({
                        position: 'absolute',
                        visibility: 'hidden',
                        'max-height': 'none',
                        height: 'auto',
                        overflow: 'visible',
                        'z-index': '-9999',
                        top: 0,
                        left: 0
                    })
                    .removeClass('has-collapse')
                    .removeClass('expanded')
                    .appendTo('body');
                
                var contentHeight = $tempDiv.height();
                $tempDiv.remove();
                
                // 只有当内容高度超过200px时才添加折叠功能
                if(contentHeight > 200){
                    $this.addClass('has-collapse').click(function(){
                        $(this).toggleClass('has-collapse expanded');
                    });
                }
            });
        });
    </script>
@endsection
