@extends('layouts.app')

@section('content')
    <script src="https://cdn.bootcss.com/showdown/1.3.0/showdown.min.js"></script>


    <script type="text/javascript">
        jQuery(document).ready(function ($) {

        });

        $(document).ready(function () {
            $('#work_mode').click(function () {
                $('.container').css('max-width', '1980px');
            });
        });
    </script>


    <div class="container">

        <div class=" col-md-12">
            @include('common.success')
            <div class="card">
                <div class="card-header">
                    想法-{{$mind->name}}

                    <div style="float:right">
                        <a href="javascript:void(0)" id="work_mode">[工作模式]</a>
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
            url: "{{ url('/mindajaxoutlineget') }}" + "/" +<?php echo $mind->id;?>,
            type: 'GET',
            data: {_token: task_token},
            success: function (result_arr) {
                if (result_arr.code != 9999) {
                    alert('处理失败，请稍后再试');
                } else {
                    $datas = result_arr.result.datas;
                    console.log($datas);
//				var converter = new showdown.Converter();
//				var html = converter.makeHtml($datas);
//				$('#mind_container').html(html);
                    $('#mind_container').html('<ul>' + $datas + '</ul>');

                }
            }
        });

    </script>
@endsection
