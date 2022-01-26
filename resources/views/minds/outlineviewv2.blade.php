@extends('layouts.app')

@section('content')
    <script src="https://cdn.bootcss.com/showdown/1.3.0/showdown.min.js"></script>
    <script src="/js/jquery.cookie.js"></script>



    <script type="text/javascript">
        jQuery(document).ready(function ($) {

        });

        $(document).ready(function () {
            $('#work_mode').click(function () {
                $('.container').css('max-width', '1980px');
            });
            $("#unable_desc").on('click', function () {
                if ($("#unable_desc").is(':checked')) {
                    $.cookie('unable_desc', true);
                } else {
                    $.cookie('unable_desc', false);
                }
                location.href = "";
            });


            $("#mind_container").on('click', '.mindli', function () {
                $next = $(this).next()
                if ($next != null && $next.hasClass('mindlicontent')) {
                    $next.toggle();
                }

            });


            if ($.cookie("unable_desc") != null && $.cookie("unable_desc") == "true") {
                $("#unable_desc").prop('checked', true);
            }
        });
    </script>


    <div class="container">

        <div class=" col-md-12">
            @include('common.success')
            <div class="card">
                <div class="card-header">
                    	想法-{{$mind->name}} 
                    	
                    	<div style="float:right">
				<input type="checkbox" value="" id="unable_desc" />禁用详情	
                    		<a href="javascript:void(0)" id="work_mode">[大屏]</a>
                    		<a href="{{'/mind/'.$mind->id}}">[编辑]</a>
                    		<a href="{{'/minds'}}">[返回]</a>
                    	</div>
                </div>

                <div class="card-body row">
                    <div id="mind_container" class=" col-md-12">
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">

        task_token = "{{ csrf_token() }}";
        var mind;
        $.ajax({
            url: "{{ url('/mindajaxget') }}" + "/" +<?php echo $mind->id;?>,
            type: 'GET',
            data: {_token: task_token},
            success: function (result_arr) {
                if (result_arr.code != 9999) {
                    alert('处理失败，请稍后再试');
                } else {
                    mind = JSON.parse(result_arr.result.jsmind_datas);

                    $htmlData = processMind(mind.data, 0);

                    $('#mind_container').html($htmlData);

                }
            }
        });

        function processMind($mind, $level) {
            $htmlData = '';
            if ($level != 0) {
                $content = '';
                $contentTip = '';
                if (null != $mind.content && '' != $mind.content) {
                    $contentTip = '<i class="bi-file-text" style="font-size: 1rem;"></i>';
                    if ($.cookie("unable_desc") == null || $.cookie("unable_desc") == "false") {
                        $content = '<div class="mindlicontent"><small>' + $mind.content.replace(/\r\n/g, "<br/>") + '</small></div>';
                    } else {
                        $content = '<div class="mindlicontent" style="display:none"><small>' + $mind.content.replace(/\r\n/g, "<br/>") + '</small></div>';
                    }
                }
                $htmlData = '<li class="mindli"><strong>' + $contentTip + $mind.topic + '</strong>';
                $htmlData += '</li>';
                $htmlData += $content;
            }
            if (null != $mind.children && Object.getOwnPropertyNames($mind.children).length > 0) {
                $htmlData += '<ul style="padding-inline-start: 20px;">';
                $.each($mind.children, function ($index, $child) {
                    $htmlData += processMind($child, $level + 1);
                });
                $htmlData += '</ul>';
            }
            return $htmlData;


        }

    </script>
@endsection
