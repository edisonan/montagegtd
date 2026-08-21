@extends('layouts.app')

@section('content')
<script src="{{ asset('vendor_local/lib/showdown.min.js') }}"></script>


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
                    想法-<span id="outline_legacy_name">思维导图</span>

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

        var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
            ? window.TaskApiBridge.requestWithFallback
            : null;
        function resolveMindId() {
            const parts = (window.location.pathname || '').split('/').filter(Boolean);
            const last = parts.length ? parts[parts.length - 1] : '';
            const id = parseInt(last, 10);
            return Number.isFinite(id) ? String(id) : '';
        }
        var mindId = resolveMindId();
        if (!apiRequest) {
            alert('API客户端未初始化');
        } else if (!mindId) {
            alert('无效的导图ID');
        } else {
            apiRequest('GET', '/minds/' + mindId + '/outline', {}).then(function(result_arr) {
                if (result_arr.code != 9999) {
                    alert('处理失败，请稍后再试');
                } else {
                    var result = result_arr.result || result_arr.data || {};
                    var $datas = result.datas;
                    console.log($datas);
//				var converter = new showdown.Converter();
//				var html = converter.makeHtml($datas);
//				$('#mind_container').html(html);
                    $('#mind_container').html('<ul>' + $datas + '</ul>');

                }
            }).catch(function() {
                alert('网络错误，请稍后再试');
            });

            apiRequest('GET', '/minds/' + mindId, {}).then(function(res) {
                var result = res.result || res.data || {};
                if (res.code == 9999 && result.mind && result.mind.name) {
                    $('#outline_legacy_name').text(result.mind.name);
                }
            });
        }

    </script>
@endsection
