<!-- 修改手账记录模态框 -->
<div id="journalEditModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <div class="fixed inset-0 overflow-y-auto py-4 sm:py-8 px-4">
        <div class="min-h-full flex items-start justify-center">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl transform transition-all m-auto">
                <div class="sticky top-0 z-10 bg-white rounded-t-lg flex items-center justify-between px-5 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-pen-to-square"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">修改手账</h3>
                            <p class="text-xs text-gray-500 mt-0.5">修改手账内容与记录时间</p>
                        </div>
                    </div>
                    <button type="button" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
                            onclick="hideJournalEditModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="overflow-y-auto max-h-[calc(100vh-180px)] p-5">
                    <form id="journalEditForm" action="javascript:void(0)" method="POST" class="space-y-5">
                        <input type="hidden" id="journalEditId" value="">

                        <div>
                            <label for="journalEditName" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-pen mr-2 text-amber-500"></i>手账内容
                            </label>
                            <textarea name="name" id="journalEditName"
                                      class="input w-full min-h-[92px] resize-y text-sm"
                                      placeholder="请输入手账内容"
                                      required></textarea>
                            <p class="text-xs text-gray-500 mt-1">简要描述您完成的手账</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="journalEditStart" class="block text-xs font-medium text-gray-600 mb-1">
                                    <i class="fas fa-play mr-1 text-green-500"></i>开始时间
                                </label>
                                <div class="space-y-2">
                                    <input type="text" name="start_time" id="journalEditStart"
                                           class="input w-full text-sm"
                                           onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d %H:%m:%s',onpicked:updateJournalEditPreview})"
                                           placeholder="选择开始时间">
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-outline" onclick="changeJournalEditTime('journalEditStart', -5)">-5m</button>
                                        <button type="button" class="btn btn-sm btn-outline" onclick="changeJournalEditTime('journalEditStart', 5)">+5m</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="journalEditEnd" class="block text-xs font-medium text-gray-600 mb-1">
                                    <i class="fas fa-stop mr-1 text-red-500"></i>结束时间
                                </label>
                                <div class="space-y-2">
                                    <input type="text" name="end_time" id="journalEditEnd"
                                           class="input w-full text-sm"
                                           onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:00',maxDate:'%y-%M-%d %H:%m:%s',onpicked:updateJournalEditPreview})"
                                           placeholder="选择结束时间">
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-outline" onclick="changeJournalEditTime('journalEditEnd', -5)">-5m</button>
                                        <button type="button" class="btn btn-sm btn-outline" onclick="changeJournalEditTime('journalEditEnd', 5)">+5m</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="journalEditPreview" class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <span>请先设置开始和结束时间</span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="sticky bottom-0 bg-white rounded-b-lg flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200">
                    <button type="button" class="btn btn-outline px-4 py-2 text-sm" onclick="hideJournalEditModal()">
                        取消
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 text-sm" onclick="submitJournalEditForm()">
                        <i class="fas fa-check mr-1"></i>保存修改
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var isJournalEditModalOpen = false;

        function apiRequest() {
            if (window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function') {
                return window.TaskApiBridge.requestWithFallback;
            }
            return window.apiRequest || null;
        }

        function parseTime(value) {
            if (!value) {
                return null;
            }
            var normalized = String(value).trim().replace(' ', 'T');
            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(normalized)) {
                normalized += ':00';
            }
            var date = new Date(normalized);
            return isNaN(date.getTime()) ? null : date;
        }

        function formatTime(date) {
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            var hours = String(date.getHours()).padStart(2, '0');
            var minutes = String(date.getMinutes()).padStart(2, '0');
            return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':00';
        }

        window.openJournalEdit = function (journalId) {
            var journal = window.indexJournalsById ? window.indexJournalsById[journalId] : null;
            if (!journal) {
                if (typeof showNotification === 'function') {
                    showNotification('error', '未找到手账数据');
                }
                return;
            }

            document.getElementById('journalEditId').value = journal.id;
            document.getElementById('journalEditName').value = journal.name || '';
            document.getElementById('journalEditStart').value = journal.start_time || '';
            document.getElementById('journalEditEnd').value = journal.end_time || '';

            var modal = document.getElementById('journalEditModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            isJournalEditModalOpen = true;

            updateJournalEditPreview();

            setTimeout(function () {
                var nameInput = document.getElementById('journalEditName');
                if (nameInput) {
                    nameInput.focus();
                }
            }, 200);
        };

        window.hideJournalEditModal = function () {
            var modal = document.getElementById('journalEditModal');
            if (!modal) {
                return;
            }
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            isJournalEditModalOpen = false;
        };

        window.changeJournalEditTime = function (inputId, minutes) {
            var input = document.getElementById(inputId);
            if (!input || !input.value) {
                return;
            }
            var date = parseTime(input.value);
            if (!date) {
                return;
            }
            date.setMinutes(date.getMinutes() + minutes);
            input.value = formatTime(date);
            updateJournalEditPreview();
        };

        window.updateJournalEditPreview = function () {
            var startValue = document.getElementById('journalEditStart').value;
            var endValue = document.getElementById('journalEditEnd').value;
            var preview = document.getElementById('journalEditPreview');

            if (!startValue || !endValue) {
                preview.innerHTML = '<div class="flex items-center text-xs text-gray-500">' +
                    '<i class="fas fa-info-circle text-blue-500 mr-2"></i>' +
                    '<span>请先设置开始和结束时间</span></div>';
                return;
            }

            var start = parseTime(startValue);
            var end = parseTime(endValue);
            if (!start || !end || end <= start) {
                preview.innerHTML = '<div class="flex items-center text-xs text-red-600">' +
                    '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                    '<span>结束时间必须晚于开始时间</span></div>';
                return;
            }

            var totalMinutes = Math.round((end.getTime() - start.getTime()) / 60000);
            var hours = Math.floor(totalMinutes / 60);
            var minutes = totalMinutes % 60;
            var durationText = (hours > 0 ? hours + '小时' : '') + (minutes > 0 || hours === 0 ? minutes + '分钟' : '');

            preview.innerHTML = '<div class="flex items-center justify-between text-sm">' +
                '<span class="text-gray-600">耗时时长</span>' +
                '<span class="font-semibold text-gray-900">' + durationText + '</span>' +
                '</div>';
        };

        function showEditError(message) {
            if (typeof showNotification === 'function') {
                showNotification('error', message);
            } else {
                alert(message);
            }
        }

        window.submitJournalEditForm = function () {
            var request = apiRequest();
            if (!request) {
                showEditError('API客户端未初始化');
                return;
            }

            var id = document.getElementById('journalEditId').value;
            var nameInput = document.getElementById('journalEditName');
            var startInput = document.getElementById('journalEditStart');
            var endInput = document.getElementById('journalEditEnd');

            if (!id) {
                showEditError('无法识别手账ID');
                return;
            }
            if (!nameInput.value.trim()) {
                showEditError('请输入手账内容');
                nameInput.focus();
                return;
            }
            if (!startInput.value) {
                showEditError('请选择开始时间');
                return;
            }
            if (!endInput.value) {
                showEditError('请选择结束时间');
                return;
            }

            var start = parseTime(startInput.value);
            var end = parseTime(endInput.value);
            if (!start || !end || end <= start) {
                showEditError('结束时间必须晚于开始时间');
                return;
            }

            var submitBtn = document.querySelector('button[onclick="submitJournalEditForm()"]');
            var original = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>保存中...';
            }

            request('PUT', '/journals/' + id, {
                name: nameInput.value.trim(),
                start_time: startInput.value,
                end_time: endInput.value
            }).then(function (resp) {
                if (resp && resp.code === 9999) {
                    hideJournalEditModal();
                    if (typeof window.afterJournalUpdate === 'function') {
                        window.afterJournalUpdate();
                    } else if (typeof showfocuss === 'function') {
                        showfocuss();
                    }
                    if (typeof showNotification === 'function') {
                        showNotification('success', '手账已更新');
                    }
                    return;
                }
                showEditError((resp && resp.msg) ? resp.msg : '保存失败');
            }).catch(function () {
                showEditError('保存失败，请稍后重试');
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = original;
                }
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('journalEditModal');
            if (!modal) {
                return;
            }

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    hideJournalEditModal();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && isJournalEditModalOpen) {
                    hideJournalEditModal();
                }
            });

            document.getElementById('journalEditStart').addEventListener('change', updateJournalEditPreview);
            document.getElementById('journalEditEnd').addEventListener('change', updateJournalEditPreview);
        });
    })();
</script>
