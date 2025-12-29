<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Codeview</title>

        <!-- Fonts -->
        <link href="https://fonts.loli.netamily=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #f5f7fa;
                color: #636b6f;
                font-family: 'Segoe UI', 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .header a {
                color: white;
                text-decoration: none;
                margin-right: 15px;
                padding: 8px 12px;
                border-radius: 4px;
                transition: background-color 0.3s;
            }
            
            .header a:hover {
                background-color: rgba(255,255,255,0.2);
            }
            
            .controls {
                padding: 15px 20px;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            
            .btn {
                display: inline-block;
                padding: 10px 20px;
                margin-right: 10px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: 500;
                cursor: pointer;
                border: none;
                transition: all 0.3s;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-success {
                background-color: #4CAF50;
                color: white;
            }
            
            .btn:hover {
                opacity: 0.9;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            #editor { 
                position: absolute;
                top: 120px;
                right: 0;
                bottom: 0;
                left: 0;
            }
        </style>
        <script src="/js/jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="/js/src-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript">
            var editor = null;
            
            $(document).ready(function(){
            	editor = ace.edit("editor");
                editor.setTheme("ace/theme/twilight");
                
                // 根据type设置ace mode
                var type = "{{$type}}";
                var mode = "ace/mode/sh"; // 默认mode
                if (type == 1) {
                    mode = "ace/mode/php";
                } else if (type == 2) {
                    mode = "ace/mode/html";
                }
                editor.session.setMode(mode);
                
                editor.setOption("wrap", "free");
                editor.setFontSize('14px');
        	    editor.setAutoScrollEditorIntoView(true);
        	    
                $.get("/admin/getCode/"+$('#id').val(), function(result){
                    console.log(result);
                    // 移除JSON.parse，因为response()->json()已经返回了正确的对象
                    // var result_arr = JSON.parse(result);
                    $('#content').text(result.data.content);
                    editor.setValue(result.data.content);
                 });

            });
        </script>
    </head>
    <body>
        <div class="header">
            <a href="/admin/codes">← 返回列表</a>
            <span style="float: right;">当前：<a href="../code/{{ $id }}"> {{$name}}</a> </span>
        </div>
        
        <div class="controls">
            <button id="aiGenerate" class="btn btn-primary">AI生成</button>
            <button id="historyBtn" class="btn" style="background-color: #6c757d; color: white;">查看历史</button>
            <button id="saveBtn" class="btn btn-success">保存修改</button>
        </div>
        
        <input type="hidden" value="{{ $id }}" id="id">
        
        <!-- 历史记录模态框 -->
        <div id="historyModal" style="display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="background-color: #fff; margin: 5% auto; padding: 0; border-radius: 8px; width: 80%; max-width: 900px; height: 80%; box-shadow: 0 4px 8px rgba(0,0,0,0.2); overflow: hidden; display: flex; flex-direction: column;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; position: relative;">
                    <h2 style="margin: 0; font-size: 24px;">代码历史记录</h2>
                    <span id="closeHistoryModal" style="position: absolute; top: 20px; right: 20px; font-size: 28px; cursor: pointer; color: white;">&times;</span>
                </div>
                <div style="padding: 20px; flex: 1; display: flex; overflow: hidden;">
                    <div style="width: 40%; overflow-y: auto; padding-right: 10px; border-right: 1px solid #eee;">
                        <h3 style="color: #333; margin-top: 0;">历史版本</h3>
                        <div id="historyList" style="margin-bottom: 20px;">
                            <!-- 历史记录列表将通过JS动态加载 -->
                        </div>
                    </div>
                    <div style="width: 60%; padding-left: 10px; display: flex; flex-direction: column;">
                        <h3 style="color: #333; margin-top: 0;">代码对比</h3>
                        <div id="diffContainer" style="flex: 1; border: 1px solid #eee; border-radius: 4px; overflow: hidden;">
                            <div id="diffEditor" style="height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- AI生成代码的模态框 -->
        <div id="aiModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="background-color: #fff; margin: 5% auto; padding: 0; border-radius: 8px; width: 80%; max-width: 700px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); overflow: hidden;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; position: relative;">
                    <h2 style="margin: 0; font-size: 24px;">AI代码生成器</h2>
                    <span id="closeModal" style="position: absolute; top: 20px; right: 20px; font-size: 28px; cursor: pointer; color: white;">&times;</span>
                </div>
                <div style="padding: 20px;">
                    <div style="margin-bottom: 20px;">
                        <label for="aiPrompt" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">请描述您需要生成的代码：</label>
                        <textarea id="aiPrompt" placeholder="例如：创建一个待办事项列表，支持添加、删除和标记完成..." rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical; box-sizing: border-box;"></textarea>
                    </div>
                    <button id="generateCodeBtn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%;">生成代码</button>
                    
                    <div id="aiLoading" style="display: none; text-align: center; margin: 20px 0;">
                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <p style="margin-top: 10px; color: #666;">AI正在为您生成代码，请稍候...</p>
                    </div>
                    
                    <div id="aiResult" style="margin-top: 20px; display: none;">
                        <h3 style="color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">生成结果</h3>
                        <pre id="aiGeneratedCode" style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto; border: 1px solid #eee; white-space: pre-wrap; font-size: 14px;"></pre>
                        <div style="margin-top: 15px; text-align: center;">
                            <button id="useAiCodeBtn" style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">使用此代码</button>
                            <button id="regenerateCodeBtn" style="background-color: #f0ad4e; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 4px; cursor: pointer;">重新生成</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            #aiGeneratedCode {
                font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                line-height: 1.4;
            }
        </style>
        
        <div id="editor"></div>
         
        <script>
    	    // 保存按钮功能
    	    $('#saveBtn').click(function() {
    	        var id = $('#id').val();
    	        var content = editor.getValue();
    	        
    	        $.post("/admin/updateCode", {
    	            id: id,
    	            content: content,
    	            _token: "{{ csrf_token() }}"
    	        }, function(result) {
    	            if (result.code === '9999') {
    	                alert('保存成功');
    	            } else {
    	                alert('保存失败: ' + result.message);
    	            }
    	        });
    	    });
    	    
    	    // AI生成代码功能
    	    $('#aiGenerate').click(function() {
    	        $('#aiModal').show();
    	    });
    	    
    	    // 关闭模态框
    	    $('#closeModal').click(function() {
    	        $('#aiModal').hide();
    	        $('#aiResult').hide();
    	            $('#aiLoading').hide();
    	    });
    	    
    	    // 点击模态框外部关闭
    	    $('#aiModal').click(function(e) {
    	        if (e.target.id === 'aiModal') {
    	            $(this).hide();
    	            $('#aiResult').hide();
    	            $('#aiLoading').hide();
    	        }
    	    });
    	    
    	    // 生成代码按钮
    	    $('#generateCodeBtn').click(function() {
    	        var prompt = $('#aiPrompt').val();
    	        if (!prompt) {
    	            alert('请输入代码描述');
    	            return;
    	        }
    	        
    	        // 显示加载状态
    	        $('#aiLoading').show();
    	        $('#aiResult').hide();
    	        
    	        // 调用后端AI代码生成接口
    	        $.post("/admin/generateCode", {
    	            id: {{ $id }},
    	            prompt: prompt,
    	            _token: "{{ csrf_token() }}"
    	        }, function(result) {
    	            if (result.code === '9999') {
    	                $('#aiGeneratedCode').text(result.data.content);
    	                $('#aiLoading').hide();
    	                $('#aiResult').show();
    	            } else {
    	                $('#aiLoading').hide();
    	                alert('生成失败: ' + result.message);
    	            }
    	        }).fail(function() {
    	            $('#aiLoading').hide();
    	            alert('请求失败，请稍后重试');
    	        });
    	    });
    	    
    	    // 使用AI生成的代码
    	    $('#useAiCodeBtn').click(function() {
    	        var aiCode = $('#aiGeneratedCode').text();
    	        editor.setValue(aiCode);
    	        $('#aiModal').hide();
    	        $('#aiResult').hide();
    	        $('#aiLoading').hide();
    	    });
    	    
    	    // 重新生成代码
    	    $('#regenerateCodeBtn').click(function() {
    	        $('#generateCodeBtn').click();
    	    });
    	    
    	    // 查看历史记录
    	    $('#historyBtn').click(function() {
    	        loadHistory();
    	        $('#historyModal').show();
    	    });
    	    
    	    // 关闭历史记录模态框
    	    $('#closeHistoryModal').click(function() {
    	        $('#historyModal').hide();
    	    });
    	    
    	    // 点击历史记录模态框外部关闭
    	    $('#historyModal').click(function(e) {
    	        if (e.target.id === 'historyModal') {
    	            $(this).hide();
    	        }
    	    });
    	    
    	    // 加载历史记录
    	    function loadHistory() {
    	        var codeId = $('#id').val();
    	        $.get("/admin/getCodeHistory/" + codeId, function(result) {
    	            if (result.code === '9999') {
    	                displayHistory(result.data.history, result.data.current);
    	            } else {
    	                alert('加载历史记录失败: ' + result.message);
    	            }
    	        }).fail(function() {
    	            alert('请求失败，请稍后重试');
    	        });
    	    }
    	    
    	    // 显示历史记录
    	    function displayHistory(history, current) {
    	        var historyListHtml = '';
    	        
    	        // 添加当前版本
    	        historyListHtml += '<div class="history-item" style="padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px; cursor: pointer;" data-type="current">' +
    	                           '<div><strong>当前版本</strong></div>' +
    	                           '<div>更新时间: ' + current.updated_at + '</div>' +
    	                           '</div>';
    	        
    	        // 添加历史版本
    	        if (history.length > 0) {
    	            history.forEach(function(item) {
    	                historyListHtml += '<div class="history-item" style="padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px; cursor: pointer;" data-id="' + item.id + '" data-type="history">' +
    	                                   '<div><strong>历史版本 #' + item.id + '</strong></div>' +
    	                                   '<div>创建时间: ' + item.created_at + '</div>' +
    	                                   '<button class="compare-btn" style="margin-top: 5px; background-color: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">对比</button>' +
    	                                   '</div>';
    	            });
    	        } else {
    	            historyListHtml += '<p>暂无历史记录</p>';
    	        }
    	        
    	        $('#historyList').html(historyListHtml);
    	        
    	        // 绑定对比按钮事件
    	        $('.compare-btn').click(function(e) {
    	            e.stopPropagation();
    	            var historyItem = $(this).closest('.history-item');
    	            var historyId = historyItem.data('id');
    	            var historyType = historyItem.data('type');
    	            compareWithCurrent(historyId, historyType);
    	        });
    	    }
    	    
    	    // 对比当前版本与历史版本
    	    function compareWithCurrent(historyId, historyType) {
    	        var codeId = $('#id').val();
    	        var currentContent = editor.getValue();
    	        var historyContent = '';
    	        
    	        if (historyType === 'current') {
    	            historyContent = currentContent;
    	        } else {
    	            // 获取历史版本内容
    	            $.get("/admin/getHistoryCode/" + historyId, function(result) {
    	                if (result.code === '9999') {
    	                    historyContent = result.data.content;
    	                    showDiff(currentContent, historyContent);
    	                } else {
    	                    alert('加载历史版本失败: ' + result.message);
    	                }
    	            }).fail(function() {
    	                alert('请求失败，请稍后重试');
    	            });
    	            return;
    	        }
    	        
    	        showDiff(currentContent, historyContent);
    	    }
    	    
    	    // 显示代码对比
    	    function showDiff(currentContent, historyContent) {
    	        // 构建双栏对比视图
    	        var diffHtml = `
    	            <div style="display: flex; height: 100%;">
    	                <div style="width: 50%; border-right: 1px solid #ccc; padding: 10px; overflow: auto;">
    	                    <h4 style="margin-top: 0; color: #333;">历史版本</h4>
    	                    <pre id="historyCodeView" style="background: #f8f9fa; padding: 10px; border-radius: 4px; height: calc(100% - 40px); overflow: auto; font-size: 12px;"></pre>
    	                </div>
    	                <div style="width: 50%; padding: 10px; overflow: auto;">
    	                    <h4 style="margin-top: 0; color: #333;">当前版本</h4>
    	                    <pre id="currentCodeView" style="background: #f8f9fa; padding: 10px; border-radius: 4px; height: calc(100% - 40px); overflow: auto; font-size: 12px;"></pre>
    	                </div>
    	            </div>
    	        `;
    	        
    	        // 设置对比内容
    	        $('#diffEditor').html(diffHtml);
    	        $('#historyCodeView').text(historyContent);
    	        $('#currentCodeView').text(currentContent);
    	    }
    	    
    	    // 页面加载时检查是否引入了diff库
    	    $(document).ready(function() {
    	        // 移除动态加载diff库的代码，因为我们使用了简单的对比方式
    	    });
    	 </script>
    </body>
</html>