@extends('layouts.app')
<link type="text/css" rel="stylesheet" href="{{ url('/css/jsmind.css')}}"/>

@section('content')

    <script type="text/javascript" src="{{ url('/js/jsmind.js').'?'.time()}}"></script>
    <script type="text/javascript" src="{{ url('/js/jsmind.screenshot.js')}}"></script>
    <script type="text/javascript" src="{{ url('/js/jsmind.draggable.js')}}"></script>
    <!-- 添加 zoom 插件 -->
    <script type="text/javascript" src="{{ url('/js/jsmind.zoom.js')}}"></script>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css"
          integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">

    <!-- Markdown Editor -->
    <link href="/css/markdown-editor.css" rel="stylesheet">
    <script src="/js/markdown-editor.js"></script>

    <style>
        /* 基本字体和按钮样式 */
        button, input, textarea {
            font-family: 'Noto Sans', 'Helvetica Neue', Arial, sans-serif;
            outline: none;
        }

        /* 卡片化操作栏 */
        .card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 6px 14px;
            border-radius: 8px;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .btn-add { background: linear-gradient(135deg,#4CA1D7,#0F959D);}
        .btn-edit { background: linear-gradient(135deg,#F7AA55,#F25C54);}
        .btn-delete { background: linear-gradient(135deg,#E85205,#FF6B6B);}
        .btn-toggle { background: linear-gradient(135deg,#584029,#8B5E3C);}
        .btn-screenshot { background: linear-gradient(135deg,#9B59B6,#8E44AD);}
        .btn-detail { background: linear-gradient(135deg,#3498DB,#2980B9);}

        /* 添加缩放控制按钮 */
        .btn-zoom { background: linear-gradient(135deg,#2ECC71,#27AE60);}
        .btn-drag { background: linear-gradient(135deg,#9B59B6,#8E44AD);}

        /* 主体布局 */
        #jsmind_container {
            width: 100%;
            height: 800px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            overflow: auto !important; /* 确保容器可以滚动 */
            transition: height 0.3s;
            position: relative;
            touch-action: none; /* 防止触摸默认行为 */
        }

        /* 确保jsmind正确显示 */
        #jsmind_container .jsmind-inner {
            width: 100%;
            height: 100%;
            position: relative;
        }

        #mindcontentdiv {
            border-radius: 10px;
            padding: 10px;
            background: #fafafa;
            border: 1px solid #eee;
            max-height: 800px;
            overflow-y: auto;
            transition: all 0.3s;
        }

        /* 缩放控制面板 */
        .zoom-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 5px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .zoom-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg,#4CA1D7,#0F959D);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .zoom-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .zoom-btn.reset {
            background: linear-gradient(135deg,#F7AA55,#F25C54);
        }

        /* Markdown 编辑区 */
        #mind_content {
            width: 100%;
            min-height: 180px;
            resize: vertical;
            border-radius: 6px;
            border: 1px solid #ccc;
            padding: 8px;
            margin-bottom: 10px;
        }

        #mind_content_show {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            max-height: 400px;
            overflow-y: auto;
        }

        /* 响应式折叠 */
        @media (max-width: 992px) {
            #mindcontentdiv {
                display: none;
            }
        }

        /* 节点选中高亮 */
        .jsmind-selected-node {
            stroke: #FF6B6B !important;
            stroke-width: 3px !important;
        }

        .rowone {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

        /* 添加加载提示 */
        .loading-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #666;
            font-size: 16px;
            z-index: 100;
        }
    </style>

    <div class="container">
        <div class="col-md-12">
            @include('common.success')
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        想法 - {{$mind->name}}

                        <button class="action-btn btn-add" onclick="add_node();" title="新增子节点 [Tab]">
                            <i class="fas fa-plus"></i> 新增
                        </button>
                        <button class="action-btn btn-edit" onclick="modify_node();" title="编辑节点 [F2]">
                            <i class="fas fa-edit"></i> 编辑
                        </button>
                        <button class="action-btn btn-delete" onclick="remove_node();" title="删除节点 [Delete]">
                            <i class="fas fa-trash"></i> 删除
                        </button>
                        <button class="action-btn btn-toggle" onclick="toggle();" title="展开/收缩 [Space]">
                            <i class="fas fa-expand"></i> 展开/收缩
                        </button>
                        <button class="action-btn btn-screenshot" onclick="screen_shot();" title="截屏">
                            <i class="fas fa-camera"></i> 截屏
                        </button>
                        <button class="action-btn btn-detail" onclick="show_selected();" title="查看节点详情 [x]">
                            <i class="fas fa-search"></i> 详情
                        </button>
                        <!-- 添加拖拽和缩放控制按钮 -->
                        <button class="action-btn btn-drag" onclick="toggle_drag_mode();" title="切换拖拽模式">
                            <i class="fas fa-arrows-alt"></i> 拖拽
                        </button>
                        <button class="action-btn btn-zoom" onclick="reset_view();" title="重置视图">
                            <i class="fas fa-sync-alt"></i> 重置
                        </button>
                    </div>

                    <div class="ml-auto d-flex gap-2">
                        <a href="{{'/mindoutlineviewv2/'}}{{$mind->id}}" class="text-decoration-none">大纲预览</a>
                        <a href="javascript:void(0)" id="work_mode" class="text-decoration-none">大屏模式</a>
                        <a href="{{'/minds'}}" class="text-decoration-none">返回</a>
                    </div>
                </div>

                <div class="card-body row g-3">
                    <div id="jsmind_container" class="col-lg-8 col-12">
                        <div class="loading-message" id="loadingMessage">正在加载思维导图...</div>
                        <!-- 缩放控制按钮 -->
                        <div class="zoom-controls">
                            <button class="zoom-btn" onclick="zoom_in()" title="放大">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button class="zoom-btn reset" onclick="reset_zoom()" title="重置缩放">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="zoom-btn" onclick="zoom_out()" title="缩小">
                                <i class="fas fa-search-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div id="mindcontentdiv" class="col-lg-4">
                        <b id="mind_name" class="rowone mb-2">描述: {{$mind->name}}</b>

                        <textarea id="mind_content">{{$mind->content}}</textarea>
                        <input type="hidden" id="mind_id" value="{{$mind->id}}">
                        <input type="hidden" id="mind_token" value="{{ csrf_token() }}">
                        <button class="btn btn-primary w-100" onclick="mind_update()">保存</button>

                        <div id="mind_content_show" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 全局变量
        var _jm = null;
        var task_token = "{{ csrf_token() }}";
        var mindData = null;
        var isDragging = false;
        var dragStartX = 0;
        var dragStartY = 0;
        var dragOffsetX = 0;
        var dragOffsetY = 0;

        // 等待DOM加载完成后初始化
        $(document).ready(function() {
            // 初始化 markdown 编辑器
            if (typeof markdownEditor !== 'undefined') {
                markdownEditor.init('mind_content', 'mind_content_show');
            }

            // 加载思维导图数据
            loadMindData();

            // 大屏模式
            $("#work_mode").click(function(){
                $(".container").toggleClass("container-fluid");
            });
        });

        // 加载思维导图数据
        function loadMindData() {
            $.ajax({
                url: "{{ url('/mindajaxget') }}/{{$mind->id}}",
                type: 'GET',
                data: {_token: task_token},
                success: function (result_arr) {
                    console.log("Received data:", result_arr); // 调试用

                    if (result_arr.code == 9999 && result_arr.result && result_arr.result.jsmind_datas) {
                        try {
                            mindData = JSON.parse(result_arr.result.jsmind_datas);
                            console.log("Parsed mind data:", mindData); // 调试用

                            // 隐藏加载提示
                            $("#loadingMessage").hide();

                            // 初始化jsMind并显示数据
                            initializeJsMind();

                            // 显示成功后居中显示
                            setTimeout(function() {
                                if (_jm) {
                                    _jm.center();
                                }
                            }, 300);

                        } catch (e) {
                            console.error("Failed to parse mind data:", e);
                            $("#loadingMessage").text("数据解析失败，请检查数据格式");
                            createDefaultMind(); // 创建默认思维导图
                        }
                    } else {
                        console.error("Invalid response format:", result_arr);
                        $("#loadingMessage").text("数据加载失败，返回格式不正确");
                        createDefaultMind(); // 创建默认思维导图
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    $("#loadingMessage").text("数据加载失败: " + error);
                    createDefaultMind(); // 创建默认思维导图
                }
            });
        }

        // 创建默认思维导图（当数据加载失败时使用）
        function createDefaultMind() {
            mindData = {
                "meta": {
                    "name": "{{$mind->name}}",
                    "author": "{{Auth::user()->name}}",
                    "version": "1.0"
                },
                "format": "node_tree",
                "data": [
                    {
                        "id": "{{$mind->id}}",
                        "topic": "{{$mind->name}}",
                        "expanded": true
                    }
                ]
            };

            $("#loadingMessage").text("使用默认数据初始化...");
            setTimeout(function() {
                $("#loadingMessage").hide();
                initializeJsMind();
            }, 500);
        }

        // 初始化jsMind
        function initializeJsMind() {
            if (!mindData) {
                console.error("No mind data available");
                return;
            }

            // 初始化 jsMind 选项
            var options = {
                container: 'jsmind_container',
                editable: true,
                theme: 'primary',
                mode: 'full',
                support_html: true,
                view: {
                    hmargin: 100,
                    vmargin: 50,
                    line_width: 2,
                    line_color: '#4a4a4a'
                },
                layout: {
                    hspace: 60,
                    vspace: 30,
                    pspace: 20
                },
                shortcut: {
                    enable: true,
                    mapping: {
                        addchild: 9,      // Tab
                        addbrother: 13,   // Enter
                        editnode: 113,    // F2
                        delnode: 46,      // Delete
                        toggle: 32,       // Space
                        left: 37,
                        up: 38,
                        right: 39,
                        down: 40
                    }
                }
            };

            // 创建jsMind实例
            _jm = new jsMind(options);

            // 应用插件
            if (typeof jsMind.draggable !== 'undefined') {
                jsMind.draggable(_jm);
            }

            if (typeof jsMind.zoom !== 'undefined') {
                jsMind.zoom(_jm);
            }

            // 显示思维导图数据
            try {
                _jm.show(mindData);

                // 默认选中根节点
                var rootId = "{{$mind->id}}";
                if (_jm.get_node(rootId)) {
                    _jm.select_node(rootId);

                    // 如果有内容，显示到编辑区
                    var rootNode = _jm.get_node(rootId);
                    if (rootNode && rootNode.data && rootNode.data.content) {
                        $("#mind_content").val(rootNode.data.content);
                    }
                }

                // 初始化空白区域拖拽
                initBlankDrag();

                // 绑定节点选择事件
                _jm.add_event_listener(function(type, data) {
                    if (type === jsMind.event_type.select) {
                        show_selected();
                    }
                });

            } catch (e) {
                console.error("Failed to show mind data:", e);
                alert("显示思维导图时出错: " + e.message);
            }
        }

        // 初始化空白区域拖拽
        function initBlankDrag() {
            if (!_jm) return;

            var container = document.getElementById('jsmind_container');
            var canvas = container.querySelector('canvas');

            if (!canvas) {
                console.warn("Canvas not found, trying alternative approach");
                canvas = container;
            }

            // 鼠标事件
            canvas.addEventListener('mousedown', function(e) {
                // 检查是否点击在空白区域（不是节点上）
                if (!e.target.closest('.jmnodes') && !e.target.closest('.jmnode')) {
                    isDragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;

                    // 获取当前视图位置
                    var currentView = _jm.get_view();
                    dragOffsetX = currentView.x || 0;
                    dragOffsetY = currentView.y || 0;

                    container.style.cursor = 'grabbing';
                    e.preventDefault();
                }
            });

            document.addEventListener('mousemove', function(e) {
                if (!isDragging || !_jm) return;

                var deltaX = e.clientX - dragStartX;
                var deltaY = e.clientY - dragStartY;

                // 更新视图位置
                var newX = dragOffsetX + deltaX;
                var newY = dragOffsetY + deltaY;

                _jm.set_view(newX, newY);
                e.preventDefault();
            });

            document.addEventListener('mouseup', function(e) {
                if (isDragging) {
                    isDragging = false;
                    if (container) container.style.cursor = 'default';
                    e.preventDefault();
                }
            });

            // 触摸事件支持
            canvas.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1 && !e.target.closest('.jmnode')) {
                    isDragging = true;
                    dragStartX = e.touches[0].clientX;
                    dragStartY = e.touches[0].clientY;

                    var currentView = _jm.get_view();
                    dragOffsetX = currentView.x || 0;
                    dragOffsetY = currentView.y || 0;

                    e.preventDefault();
                }
            }, {passive: false});

            document.addEventListener('touchmove', function(e) {
                if (!isDragging || !_jm || e.touches.length !== 1) return;

                var deltaX = e.touches[0].clientX - dragStartX;
                var deltaY = e.touches[0].clientY - dragStartY;

                var newX = dragOffsetX + deltaX;
                var newY = dragOffsetY + deltaY;

                _jm.set_view(newX, newY);
                e.preventDefault();
            }, {passive: false});

            document.addEventListener('touchend', function(e) {
                isDragging = false;
            });
        }

        // 拖拽模式切换
        function toggle_drag_mode() {
            if (!_jm) return;

            var currentMode = _jm.get_mode();
            var newMode = currentMode === 'full' ? 'side' : 'full';
            _jm.set_mode(newMode);

            var btn = document.querySelector('.btn-drag i');
            if (newMode === 'full') {
                btn.className = 'fas fa-hand-paper';
                btn.parentElement.title = '退出拖拽模式';
            } else {
                btn.className = 'fas fa-arrows-alt';
                btn.parentElement.title = '进入拖拽模式';
            }
        }

        // 缩放功能
        function zoom_in() {
            if (_jm && _jm.zoom_in) _jm.zoom_in();
        }

        function zoom_out() {
            if (_jm && _jm.zoom_out) _jm.zoom_out();
        }

        function reset_zoom() {
            if (_jm && _jm.set_zoom) _jm.set_zoom(1);
        }

        function reset_view() {
            if (!_jm) return;
            if (_jm.set_zoom) _jm.set_zoom(1);
            _jm.center();
        }

        function get_selected_nodeid() {
            if (!_jm) return null;
            var node = _jm.get_selected_node();
            return node ? node.id : null;
        }

        function add_node() {
            if (!_jm) {
                alert('思维导图未初始化');
                return;
            }

            var selected_node = _jm.get_selected_node();
            if (!selected_node) {
                alert('请先选择节点');
                return;
            }

            var name = prompt("请输入节点内容", "");
            if (name) {
                $.post("{{ url('mind') }}", {_token: task_token, name: name, parent_mind_id: selected_node.id}, function(res){
                    if(res.code == 9999) {
                        // 添加节点到思维导图
                        _jm.add_node(selected_node, res.result.id, res.result.name);

                        // 选中新节点
                        var newNode = _jm.get_node(res.result.id);
                        if (newNode) {
                            _jm.select_node(res.result.id);
                        }
                    } else {
                        alert('添加节点失败: ' + res.message);
                    }
                }).fail(function() {
                    alert('网络错误，添加节点失败');
                });
            }
        }

        function remove_node() {
            if (!_jm) {
                alert('思维导图未初始化');
                return;
            }

            var id = get_selected_nodeid();
            if(!id) {
                alert('请先选择节点');
                return;
            }

            if(confirm("确认删除该节点及子节点？")) {
                $.ajax({
                    url: "{{ url('mind') }}/"+id,
                    type: 'DELETE',
                    data: {_token: task_token},
                    success: function(res){
                        if(res.code == 9999) {
                            _jm.remove_node(id);
                        } else {
                            alert('删除节点失败: ' + res.message);
                        }
                    },
                    error: function() {
                        alert('网络错误，删除节点失败');
                    }
                });
            }
        }

        function modify_node() {
            if (!_jm) {
                alert('思维导图未初始化');
                return;
            }

            var id = get_selected_nodeid();
            if(!id) {
                alert('请先选择节点');
                return;
            }

            var node = _jm.get_selected_node();
            var name = prompt("请输入节点内容", node.topic);
            if(name) {
                $.post("{{ url('mind') }}/"+id, {_token: task_token, name: name}, function(res){
                    if(res.code == 9999) {
                        _jm.update_node(id, name);
                    } else {
                        alert('更新节点失败: ' + res.message);
                    }
                }).fail(function() {
                    alert('网络错误，更新节点失败');
                });
            }
        }

        function toggle() {
            if (!_jm) {
                alert('思维导图未初始化');
                return;
            }

            var id = get_selected_nodeid();
            if(id) _jm.toggle_node(id);
        }

        function show_selected() {
            if (!_jm) return;

            var node = _jm.get_selected_node();
            if(node) {
                $("#mind_content").val(node.data.content || "");
                $("#mind_id").val(node.id);
                $("#mind_name").text('描述:' + node.topic);

                // 更新markdown预览
                if (typeof markdownEditor !== 'undefined' && markdownEditor.updatePreview) {
                    markdownEditor.updatePreview();
                }
            }
        }

        function screen_shot() {
            if (_jm && _jm.screenshot && _jm.screenshot.shootDownload) {
                _jm.screenshot.shootDownload();
            } else {
                alert('截屏功能不可用');
            }
        }

        function mind_update() {
            var content = $("#mind_content").val();
            var id = $("#mind_id").val();

            if(id && content !== undefined) {
                $.post("{{ url('mind') }}/"+id, {_token:task_token, content:content}, function(res){
                    if(res.code == 9999){
                        // 更新节点数据
                        var node = _jm.get_node(id);
                        if (node) {
                            if (!node.data) node.data = {};
                            node.data.content = content;
                        }

                        // 重新选中节点
                        _jm.select_node(id);
                        alert('保存成功');
                    } else {
                        alert('保存失败: ' + res.message);
                    }
                }).fail(function() {
                    alert('网络错误，保存失败');
                });
            } else {
                alert('请先选择节点');
            }
        }
    </script>
@endsection