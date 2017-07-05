@extends('layouts.app')
<link type="text/css" rel="stylesheet" href="{{ url('/css/jsmind.css')}}" />
<script type="text/javascript" src="{{ url('/js/jsmind.js')}}"></script>

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
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	想法-{{$mind->name}}
                </div>

                <div class="panel-body" id="jsmind_container">
					
                </div>
            </div>

        </div>
    </div>
<script type="text/javascript">
    var mind = eval('(' +' <?php echo $jsmind_datas;?>' + ')');
    var options = {
        container:'jsmind_container',
        editable:true,
        theme:'orange'
    };

    var jm = new jsMind(options);
    // 让 jm 显示这个 mind 即可
    jm.show(mind); 
</script>
@endsection
