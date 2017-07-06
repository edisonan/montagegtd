@extends('layouts.app')
<link type="text/css" rel="stylesheet" href="{{ url('/css/jsmind.css')}}" />
<script type="text/javascript" src="{{ url('/js/jsmind.js')}}"></script>
<script type="text/javascript" src="{{ url('/js/jsmind.screenshot.js')}}"></script>

@section('content')
<script type="text/javascript">
$(document).ready(function () {

// 	$("#check_url").click(function(){
// 		url = $("#url").val();
// 		$.get("{{ url('feed/checkFeedUrl') }}",{url:url},function(result){
// 			result_arr = JSON.parse(result);
// 			if(result_arr.code != 9999){
// 				alert('该url未检测到内容，请确认！');
// 			} else {
// 				alert('检测成功');
// 			}
// 			$("#feed_name").val(result_arr.result.title);
// 		});
// 	});
});
</script>


    <div class="container">
    
        <div class="col-sm-offset-0 col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	想法-{{$mind->name}} 
                    	<button onclick="add_node();">add node</button>
                    	<button onclick="modify_node();">modify node</button>
                    	<button onclick="show_selected();">get the selected</button>
                    	<button onclick="remove_node();">remove node</button>
                    	<button onclick="toggle();">toggle node</button>
                    	<button onclick="screen_shot();">screen shot</button>
                </div>

                <div class="panel-body">
					<div id="jsmind_container" class="col-sm-offset-0 col-sm-10">
					</div>
					<div id="jsmind_container" class="col-sm-offset-0 col-sm-2">
					</div>
                </div>
            </div>

        </div>
    </div>
<script type="text/javascript">
    var mind = eval('(' +' <?php echo $jsmind_datas;?>' + ')');
    var options = {
        container:'jsmind_container',
        editable:true,
        theme:'primary',
        mode :'full',           // 显示模式
        support_html : true,    // 是否支持节点里的HTML元素
        view:{
            hmargin:100,        // 思维导图距容器外框的最小水平距离
            vmargin:50,         // 思维导图距容器外框的最小垂直距离
            line_width:2,       // 思维导图线条的粗细
            line_color:'#555'   // 思维导图线条的颜色
        },
        layout:{
            hspace:30,          // 节点之间的水平间距
            vspace:20,          // 节点之间的垂直间距
            pspace:13           // 节点收缩/展开控制器的尺寸
        },
        shortcut:{
            enable:true,        // 是否启用快捷键
            handles:{
//             	delnode
            },         // 命名的快捷键事件处理器
            mapping:{           // 快捷键映射
                addchild   : 45,    // <Insert>
                addbrother : 13,    // <Enter>
                editnode   : 113,   // <F2>
                delnode    : 46,    // <Delete>
                toggle     : 32,    // <Space>
                left       : 37,    // <Left>
                up         : 38,    // <Up>
                right      : 39,    // <Right>
                down       : 40,    // <Down>
            }
        },
    };

    var _jm = new jsMind(options);
    // 让 _jm 显示这个 mind 即可
    _jm.show(mind); 

    function add_node(){
        var selected_node = _jm.get_selected_node(); // as parent of new node
        if(!selected_node){prompt_info('please select a node first.');return;}

        var selected_id = get_selected_nodeid();

        var name = prompt("Please enter content!", "")
        if (name != null && name != "")
        {
        	task_token = "{{ csrf_token() }}";
        	
        	$.ajax({
    		    url: "{{ url('mind') }}",
    		    type: 'POST',
    		    data: {_token:task_token,name:name,parent_mind_id:selected_id},
    		    success: function(result) {
    		    	result_arr = JSON.parse(result);
    				if(result_arr.code != 9999){
    					alert('处理失败，请稍后再试');
    				} else {
    					var nodeid = result_arr['result']['id'];
    					var topic = result_arr['result']['name'];
    										
   					 	var node = _jm.add_node(selected_node, nodeid, topic);
    				}
    		    }
    		});
        }

//         //开始写入
//         task_token = "{{ csrf_token() }}";

//         //执行移除
//         $.ajax({
// 		    url: "{{ url('mind') }}",
// 		    type: 'POST',
// 		    data: {_token:task_token,name:"New Node",parent_mind_id:selected_id},
// 		    success: function(result) {
// 		    	result_arr = JSON.parse(result);
// 				if(result_arr.code != 9999){
// 					alert('处理失败，请稍后再试');
					
// 				} else {
// 					var nodeid = result_arr['result']['id'];
// // 					var topic = result_arr['result']['name'];
// 					var topic = "New Node";
					
// 					console.log(result_arr);
					
// 			        var node = _jm.add_node(selected_node, nodeid, topic);
// 				}
// 		    }
// 		});

//         var nodeid = jsMind.util.uuid.newid();
//         var topic = '* Node_'+nodeid.substr(0,5)+' *';
//         var node = _jm.add_node(selected_node, nodeid, topic);
    }

    function remove_node(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info('please select a node first.');return;}

        task_token = "{{ csrf_token() }}";

        //执行移除
        $.ajax({
		    url: "{{ url('mind') }}"+"/"+selected_id,
		    type: 'DELETE',
		    data: {_token:task_token},
		    success: function(result) {
		    	result_arr = JSON.parse(result);
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					_jm.remove_node(selected_id);
				}
		    }
		});
    }

    function toggle(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info('please select a node first.');return;}

        _jm.toggle_node(selected_id);
    }

    function get_selected_nodeid(){
        var selected_node = _jm.get_selected_node();
        if(!!selected_node){
            return selected_node.id;
        }else{
            return null;
        }
    }
    
    function show_selected(){
        var selected_node = _jm.get_selected_node();
        if(!!selected_node){
            prompt_info(selected_node.topic);
        }else{
            prompt_info('nothing');
        }
    }
    
    function prompt_info(msg){
        alert(msg);
    }
    
    function screen_shot(){
        _jm.screenshot.shootDownload();
    }

    function modify_node(){
        var selected_id = get_selected_nodeid();
        if(!selected_id){prompt_info('please select a node first.');return;}
        
        var selected_node = _jm.get_selected_node();

        var name = prompt("Please enter content!",selected_node.topic)
        if (name != null && name != "")
        {
        	task_token = "{{ csrf_token() }}";
        	
        	$.ajax({
    		    url: "{{ url('mind') }}"+"/"+selected_id,
    		    type: 'POST',
    		    data: {_token:task_token,name:name},
    		    success: function(result) {
    		    	result_arr = JSON.parse(result);
    				if(result_arr.code != 9999){
    					alert('处理失败，请稍后再试');
    				} else {
    					// modify the topic
    		            _jm.update_node(selected_id, name);
    				}
    		    }
    		});
        }
    }

    function show_selected(){
        var selected_node = _jm.get_selected_node();
        if(!!selected_node){
            prompt_info(selected_node.topic);
        }else{
            prompt_info('nothing');
        }
    }
</script>
@endsection
