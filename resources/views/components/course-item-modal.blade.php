<div class="modal fade" id="courseItemModal" tabindex="-1" aria-labelledby="courseItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseItemModalLabel">添加/编辑章节</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="courseItemErrors" class="alert alert-danger" style="display: none;">
                    <ul id="courseItemErrorList"></ul>
                </div>

                <form id="courseItemFormModal" method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" id="course_id_modal" name="course_id" value="">
                    <input type="hidden" id="item_id_modal" name="item_id" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_id_modal">父级章节</label>
                                <select class="form-control" id="parent_id_modal" name="parent_id">
                                    <option value="">无父级（顶级章节）</option>
                                    <!-- 选项将通过JavaScript动态加载 -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_type_modal">章节类型 *</label>
                                <select class="form-control" id="item_type_modal" name="item_type" required>
                                    <option value="module">模块</option>
                                    <option value="chapter">章节</option>
                                    <option value="video">视频</option>
                                    <option value="assignment">作业</option>
                                    <option value="quiz">测验</option>
                                    <option value="reading">阅读材料</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title_modal">章节标题 *</label>
                        <input type="text" class="form-control" id="title_modal" name="title" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_modal">预计时长（分钟）</label>
                                <input type="number" class="form-control" id="duration_modal" name="duration" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="order_index_modal">排序</label>
                                <input type="number" class="form-control" id="order_index_modal" name="order_index" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="external_url_modal">外部链接</label>
                        <input type="url" class="form-control" id="external_url_modal" name="external_url">
                    </div>

                    <div class="form-group">
                        <label for="description_modal">描述</label>
                        <textarea class="form-control" id="description_modal" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitCourseItemForm()">保存章节</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 提交课程章节表单
    function submitCourseItemForm() {
        var form = document.getElementById('courseItemFormModal');
        var formData = new FormData(form);
        var itemId = $('#item_id_modal').val();
        var courseId = $('#course_id_modal').val();
        
        var url = itemId ? '/course-items/' + itemId : '/course-items';
        var method = itemId ? 'POST' : 'POST'; // 使用POST方法，通过_method参数指定PUT或保持POST
        
        // 如果是更新操作，添加_method参数
        if (itemId) {
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(response.code == 9999) {
                    $('#courseItemModal').modal('hide');
                    // 显示成功消息
                    alert(response.msg || '操作成功');
                    // 刷新页面或重新加载课程结构
                    location.reload();
                } else {
                    // 显示错误信息
                    $('#courseItemErrorList').empty();
                    if(response.msg) {
                        $('#courseItemErrorList').append('<li>' + response.msg + '</li>');
                    } else {
                        $('#courseItemErrorList').append('<li>操作失败</li>');
                    }
                    $('#courseItemErrors').show();
                }
            },
            error: function(xhr) {
                // 显示验证错误
                $('#courseItemErrorList').empty();
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#courseItemErrorList').append('<li>' + value[0] + '</li>');
                    });
                } else {
                    $('#courseItemErrorList').append('<li>操作失败，请稍后重试</li>');
                }
                $('#courseItemErrors').show();
            }
        });
    }
    
    // 加载章节结构到下拉框
    function loadCourseStructure(courseId, excludeItemId = null, currentParentId = null) {
        $.ajax({
            url: '/course-items/structure/' + courseId,
            type: 'GET',
            success: function(response) {
                if(response.code == 9999) {
                    var selectElement = $('#parent_id_modal');
                    selectElement.empty();
                    selectElement.append('<option value="">无父级（顶级章节）</option>');
                    
                    // 递归构建选项
                    function buildOptions(items, level = 0) {
                        var prefix = level > 0 ? '--'.repeat(level) + ' ' : '';
                        $.each(items, function(index, item) {
                            // 如果当前项是要排除的项，则跳过
                            if(excludeItemId && item.id == excludeItemId) {
                                return true; // continue
                            }
                            
                            selectElement.append('<option value="' + item.id + '">' + prefix + item.title + '</option>');
                            
                            if(item.children && item.children.length > 0) {
                                buildOptions(item.children, level + 1);
                            }
                        });
                    }
                    
                    buildOptions(response.result);
                    
                    // 如果提供了当前父级ID，则选中该项
                    if(currentParentId) {
                        selectElement.val(currentParentId);
                    }
                }
            },
            error: function(xhr) {
                console.log('加载章节结构失败');
                console.log(xhr.responseText);
            }
        });
    }
    
    // 打开课程章节编辑模态框
    function openCourseItemModal(courseId, itemData = null) {
        // 清除错误信息
        $('#courseItemErrors').hide();
        $('#courseItemErrorList').empty();
        
        // 设置课程ID
        $('#course_id_modal').val(courseId);
        
        // 如果是编辑模式，填充数据
        if(itemData) {
            $('#courseItemModalLabel').text('编辑章节');
            $('#item_id_modal').val(itemData.id);
            $('#title_modal').val(itemData.title);
            $('#parent_id_modal').val(itemData.parent_id || '');
            $('#item_type_modal').val(itemData.item_type);
            $('#duration_modal').val(itemData.duration || 0);
            $('#external_url_modal').val(itemData.external_url || '');
            $('#description_modal').val(itemData.description || '');
            $('#order_index_modal').val(itemData.order_index || 0);
            
            // 加载章节结构，排除当前项及其子项，以避免循环引用
            loadCourseStructure(courseId, itemData.id, itemData.parent_id);
        } else {
            // 如果是添加模式，清空表单
            $('#courseItemModalLabel').text('添加章节');
            $('#item_id_modal').val('');
            $('#title_modal').val('');
            $('#parent_id_modal').val('');
            $('#item_type_modal').val('chapter');
            $('#duration_modal').val(0);
            $('#external_url_modal').val('');
            $('#description_modal').val('');
            $('#order_index_modal').val(0);
            
            // 加载章节结构
            loadCourseStructure(courseId);
        }
        
        // 显示模态框
        $('#courseItemModal').modal('show');
    }
    
    // 取消编辑
    function cancelEdit() {
        $('#courseItemModal').modal('hide');
    }
</script>