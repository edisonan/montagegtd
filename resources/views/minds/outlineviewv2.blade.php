@extends('layouts.app')

@section('content')
    <style>
        #mind_container ul { list-style: none; padding-left: 20px; margin: 0; border-left: 1px dashed #ccc; }
        .mind-node { margin: 6px 0; padding: 6px 10px; border-radius: 4px; background: #fdfdfd; cursor: pointer; }
        .mind-node:hover { background: #f0f9f5; }
        .mind-remark { margin-top: 4px; padding: 4px 8px; border-left: 3px solid #4CA1D7; background: #f9f9f9; font-size: 0.9rem; display: block; white-space: pre-wrap; word-wrap: break-word; }
        .toggle-remark-btn { font-size: 0.85rem; margin-left: 6px; color: #4CA1D7; cursor: pointer; }
        #toggle_all_remarks { margin-left: 10px; }
    </style>

    <div class="container">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    想法-{{$mind->name}}
                    <div style="float:right">
                        <button id="toggle_all_remarks" class="btn btn-sm btn-outline-primary">折叠/展开所有备注</button>
                        <a href="javascript:void(0)" id="work_mode">[大屏]</a>
                        <a href="{{'/mind/'.$mind->id}}">[编辑]</a>
                        <a href="{{'/minds'}}">[返回]</a>
                    </div>
                </div>
                <div class="card-body row">
                    <div id="mind_container" class="col-md-12"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var mindData;
        var allRemarksVisible = true;

        $(document).ready(function(){
            // 大屏模式
            $('#work_mode').click(function () {
                $('.container').css('max-width', '1980px');
            });

            // 获取数据
            $.ajax({
                url: "{{ url('/mindajaxget') }}/{{$mind->id}}",
                type: 'GET',
                data: {_token: "{{ csrf_token() }}"},
                success: function(res) {
                    if(res.code != 9999){
                        alert('处理失败，请稍后再试');
                    } else {
                        mindData = JSON.parse(res.result.jsmind_datas);
                        renderMind(mindData);
                    }
                }
            });

            // 一键折叠/展开备注
            $('#toggle_all_remarks').click(function(){
                allRemarksVisible = !allRemarksVisible;
                $('.mind-remark').each(function(){
                    $(this).toggle(allRemarksVisible);
                });
            });
        });

        function renderMind(data){
            $('#mind_container').html(processMind(data.data, 0));
        }

        function processMind(node, level){
            var html = '';
            var hasRemark = node.content && node.content.trim() !== '';

            html += '<li>';
            html += '<div class="mind-node">';
            html += '<strong>'+ escapeHtml(node.topic) + '</strong>';
            if(hasRemark){
                html += '<span class="toggle-remark-btn">[折叠/展开]</span>';
                html += '<div class="mind-remark">'+ nl2br(escapeHtml(node.content)) +'</div>';
            }
            html += '</div>';

            if(node.children && Object.keys(node.children).length > 0){
                html += '<ul>';
                $.each(node.children, function(index, child){
                    html += processMind(child, level+1);
                });
                html += '</ul>';
            }
            html += '</li>';
            return html;
        }

        // 点击单个节点折叠/展开备注
        $(document).on('click', '.toggle-remark-btn', function(e){
            e.stopPropagation();
            $(this).siblings('.mind-remark').slideToggle();
        });

        // 转义函数，防止代码执行
        function escapeHtml(text) {
            return $('<div/>').text(text).html();
        }

        // 换行保留
        function nl2br(str) {
            return str.replace(/\r?\n/g, '<br>');
        }
    </script>
@endsection
