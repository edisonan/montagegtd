@extends('layouts.app')

@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#check_url").unbind("click").click(function () {
// 		$("#processTips").text("处理中");

// 		url = $("#url").val();
// 		$.get("{{ url('mind/checkFeedUrl') }}",{url:url},function(result_arr){
// 			if(result_arr.code != 9999){
// 				alert('该url未检测到内容，请确认！');
// 			}
// 			$("#mind_name").val(result_arr.result.title);
// 			$("#processTips").text("处理完成");
// 		});
            });

            $(".delete_mind").click(function () {
                mind_value = $(this).attr("mind_value");
                mind_token = $(this).attr("mind_token");
                mind_type = $(this).attr("mind_type");

                if (mind_type == 'delete' && !confirm("确认要删除此目标咩？")) {
                    return false;
                }

                $.ajax({
                    url: "{{ url('mind') }}" + "/" + mind_value,
                    type: 'DELETE',
                    data: {type: mind_type, _token: mind_token},
                    success: function (result_arr) {
                        if (result_arr.code != 9999) {
                            alert('处理失败，请稍后再试');
                        } else {
                            $('#' + mind_value).remove();
                        }
                    }
                });
            });
	
		$(".add_tag").click(function(){
		$add_tag = $(this);
		mind_value = $(this).attr("mind_value");
		mind_token = $(this).attr("mind_token");

		var name = prompt("添加标签","");
		if (name == null) {
			return true;
		}

		$.ajax({
			url: "{{ url('mindaddtag') }}"+"/"+mind_value,
			type: 'POST',
			data: {tag_name:name,_token:mind_token},
			success: function(result_arr) {
				if(result_arr.code != 9999){
					alert('处理失败，请稍后再试');
				} else {
					$add_tag.before('&nbsp;<a href="/minds?tag_id=' + result_arr.result.tag.id + '">#' + name + '#</a>');
				}
			}
		});
	});
        });
    </script>
    <div class="container">

        <div class=" col-md-12">
            @include('common.success')
            <div class="card">
                <div class="card-header">
                    思维导图
                </div>

                <div class="card-body">
                    <!-- Display Validation Errors -->
                @include('common.errors')

                <!-- New mind Form -->
                    <form action="{{ url('mind') }}" method="POST" class="form-horizontal">
                    {{ csrf_field() }}

                    <!-- mind Name -->
                        <div class="form-group row" id="mind_form_div1">
                            <div class="col-md-9" style="display: -webkit-inline-box;width: 75%;">
                                <input type="text" name="name" id="name" class="form-control" value="">
                            </div>
                            <div class="col-md-3" style="display: -webkit-inline-box;width: 25%;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i>新想法！
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    导图列表
                </div>

                <div class="card-body">

                    @if (count($minds) > 0)
                        <table class="table table-hover mind-table">
                            <thead>
                            <th>思维导图</th>
                            <th>操作</th>
                            </thead>
                            <tbody>
                            <?php $lastDate = '';?>
                            @foreach ($minds as $mind)
                                <tr id="{{ $mind->id }}">
                                    <td class="table-text" width="80%">
                                        <div class="rowone">
                                            <?php
                                            $currentDate = date('m-d', strtotime($mind->updated_at));
                                            if ($currentDate != $lastDate) {
                                                $lastDate = $currentDate;
                                                $style = "";
                                            } else {
                                                $style = "color:rgb(0,0,0,0);";
                                            }
                                            ?>
                                            <span style="{{ $style}}">
{{ $currentDate }}
</span>
                                            <small>{{ date('H:i', strtotime($mind->updated_at)) }} </small>

                                            <a href="{{url('mindoutlineviewv2/' . $mind->id)}}" title="大纲预览">
                                                <i class="bi-file-text" style="font-size: 1.3rem;"></i>
                                            </a>
                                            <span title="{{ $mind->content }}">
                                        		<a href="{{url('mind/' . $mind->id)}}" title="">
														{{ $mind->name }}
													</a>
												</span>
				            <small>
					    @foreach($mind->mindTagMaps as $map)
						<a href="/minds?tag_id={{$map->tag->id}}" style="color: #af3939d4;"> #{{ $map->tag->name }}# </a>&nbsp;
					    @endforeach
					    <a href="javascript:void(0);" class="add_tag" style="color: #af3939d4;" mind_value="{{ $mind->id }}" mind_token="{{ csrf_token() }}">&nbsp;+</a>
					    </small>
                                        </div>
                                    </td>

                                    <td width="20%" align="right">
                                        <a href="{{ url('mind/'.$mind->id)}}">
                                            <i class="fa fa-edit" style="font-size: 1.5rem;"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="delete_mind" mind_type="delete"
                                           mind_value="{{ $mind->id }}" mind_token="{{ csrf_token() }}"
                                           style="cursor:pointer;" class="text-right">
                                            <i class="fa fa-trash-o" style="font-size: 1.5rem;"></i>
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $minds->links() !!}
                    @else
                        <div>
                            <img src="/img/new/love.png" width="200px">
                            暂时还没有思维导图哦，快去开始创建第一个导图吧！
                        </div>
                    @endif
                </div>


            </div>

        </div>
    </div>
@endsection
