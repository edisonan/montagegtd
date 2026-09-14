<!-- 测验编辑器弹窗 - 为 quiz 类型章节配置题目/选项/解析/及格分 -->
<div class="modal hidden" id="quizEditorModal" role="dialog" aria-labelledby="quizEditorModalLabel" aria-hidden="true">
    <div class="modal-content w-full max-w-4xl" style="max-width: 860px;">
        <!-- 模态框头部 -->
        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-900" id="quizEditorModalLabel">配置章节测验</h3>
                <p class="text-sm text-gray-500 mt-1">为学员配置测验，支持单选/多选与答案解析</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition" onclick="closeQuizEditorModal()" aria-label="关闭">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="mb-6 space-y-4">
            <!-- 及格分与作答次数 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quizPassingScore" class="block text-sm font-medium text-gray-700 mb-2">及格分（%）</label>
                    <input type="number" id="quizPassingScore" class="input w-full" value="70" min="0" max="100">
                </div>
                <div>
                    <label for="quizAttemptsAllowed" class="block text-sm font-medium text-gray-700 mb-2">允许作答次数（留空不限）</label>
                    <input type="number" id="quizAttemptsAllowed" class="input w-full" placeholder="不限" min="1">
                </div>
            </div>

            <!-- 题目列表 -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-medium text-gray-700">题目列表</label>
                    <button type="button" class="btn btn-sm btn-outline" onclick="quizAddQuestion()">
                        <i class="fas fa-plus mr-1"></i>添加题目
                    </button>
                </div>
                <div id="quizQuestionsWrap" class="space-y-4"></div>
                <p class="text-xs text-gray-400 mt-2" id="quizEmptyTip">还没有题目，点击"添加题目"开始创建</p>
            </div>
        </div>

        <!-- 模态框底部 -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
            <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                保存后学员即可在课程详情页作答
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" class="btn btn-secondary" onclick="closeQuizEditorModal()">
                    <i class="fas fa-times mr-2"></i>取消
                </button>
                <button type="button" class="btn btn-primary" id="saveQuizBtn" onclick="saveQuizEditor()">
                    <i class="fas fa-save mr-2"></i>保存测验
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
        ? window.TaskApiBridge.requestWithFallback
        : null;

    // ---------- 弹窗开关 ----------
    function openQuizEditorModal(itemId, itemTitle) {
        var modal = document.getElementById('quizEditorModal');
        modal.__quizItemId = Number(itemId || 0);
        document.getElementById('quizEditorModalLabel').textContent = (itemTitle ? itemTitle + ' - ' : '') + '配置测验';
        modal.classList.remove('hidden');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        loadQuizForEditor(modal.__quizItemId);
    }

    function closeQuizEditorModal() {
        var modal = document.getElementById('quizEditorModal');
        modal.classList.remove('show');
        setTimeout(function() {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }

    // ---------- 数据加载 ----------
    function loadQuizForEditor(itemId) {
        var wrap = document.getElementById('quizQuestionsWrap');
        wrap.innerHTML = '';
        document.getElementById('quizEmptyTip').style.display = 'block';
        document.getElementById('quizPassingScore').value = 70;
        document.getElementById('quizAttemptsAllowed').value = '';
        if (!apiRequest) return;

        apiRequest('GET', '/course-items/' + itemId + '/quiz', {}).then(function(resp) {
            if (!resp || resp.code !== 9999 || !resp.result || !resp.result.quiz) {
                return; // 无现有测验，保持空模板
            }
            var quiz = resp.result.quiz;
            document.getElementById('quizPassingScore').value = quiz.passing_score !== undefined && quiz.passing_score !== null ? quiz.passing_score : 70;
            document.getElementById('quizAttemptsAllowed').value = quiz.attempts_allowed !== undefined && quiz.attempts_allowed !== null ? quiz.attempts_allowed : '';
            document.getElementById('quizEmptyTip').style.display = 'none';
            (quiz.questions || []).forEach(function(q) {
                quizAddQuestion({
                    question_type: q.question_type || 'single',
                    question: q.question || '',
                    explanation: q.explanation || '',
                    points: q.points || 1,
                    options: (q.options || []).map(function(o) {
                        return { content: o.content || '', is_correct: !!o.is_correct };
                    })
                });
            });
            if (!(quiz.questions || []).length) {
                document.getElementById('quizEmptyTip').style.display = 'block';
            }
        }).catch(function() {
            // 读取失败不影响新建
        });
    }

    // ---------- 题目/选项动态增删 ----------
    function quizAddQuestion(data) {
        data = data || {};
        var wrap = document.getElementById('quizQuestionsWrap');
        var idx = wrap.children.length;
        document.getElementById('quizEmptyTip').style.display = 'none';

        var div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
        div.id = 'quizQ-' + idx;
        div.innerHTML = ''
            + '<div class="flex items-start justify-between mb-3">'
            + '<div class="flex items-center gap-2">'
            + '<span class="font-medium text-gray-900">第 ' + (idx + 1) + ' 题</span>'
            + '<select class="input text-sm py-1 px-2 q-type" style="width:auto">'
            + '<option value="single"' + (data.question_type === 'multiple' ? '' : ' selected') + '>单选</option>'
            + '<option value="multiple"' + (data.question_type === 'multiple' ? ' selected' : '') + '>多选</option>'
            + '</select></div>'
            + '<button type="button" class="text-red-500 hover:text-red-700" onclick="quizRemoveQuestion(' + idx + ')"><i class="fas fa-trash-alt"></i></button>'
            + '</div>'
            + '<textarea class="input w-full mb-2 q-question" rows="2" placeholder="请输入题干">' + escapeHtmlForTextarea(data.question || '') + '</textarea>'
            + '<input class="input w-full mb-2 q-explanation" type="text" placeholder="答案解析（可选，学员作答后可见）" value="' + escapeHtmlAttr(data.explanation || '') + '">'
            + '<div class="q-options space-y-2"></div>'
            + '<button type="button" class="btn btn-sm btn-outline mt-2" onclick="quizAddOption(' + idx + ')"><i class="fas fa-plus mr-1"></i>添加选项</button>';

        wrap.appendChild(div);

        (data.options && data.options.length ? data.options : [null, null]).forEach(function(opt) {
            quizAddOption(idx, opt);
        });
    }

    function quizRemoveQuestion(idx) {
        var wrap = document.getElementById('quizQuestionsWrap');
        var div = document.getElementById('quizQ-' + idx);
        if (div) div.remove();
        // 重新编号
        Array.prototype.forEach.call(wrap.children, function(el, i) {
            el.id = 'quizQ-' + i;
            el.querySelector('.font-medium.text-gray-900').textContent = '第 ' + (i + 1) + ' 题';
        });
        if (!wrap.children.length) document.getElementById('quizEmptyTip').style.display = 'block';
    }

    function quizAddOption(qIdx, opt) {
        opt = opt || {};
        var qDiv = document.getElementById('quizQ-' + qIdx);
        if (!qDiv) return;
        var optsWrap = qDiv.querySelector('.q-options');
        var oIdx = optsWrap.children.length;
        var row = document.createElement('div');
        row.className = 'flex items-center gap-2 q-option';
        row.innerHTML = ''
            + '<input type="checkbox" class="o-correct" title="正确答案" ' + (opt.is_correct ? 'checked' : '') + '>'
            + '<input type="text" class="input flex-1 o-content" placeholder="选项内容（勾选左侧为正确答案）" value="' + escapeHtmlAttr(opt.content || '') + '">'
            + '<button type="button" class="text-red-400 hover:text-red-600" onclick="quizRemoveOption(' + qIdx + ',' + oIdx + ')"><i class="fas fa-times"></i></button>';
        optsWrap.appendChild(row);
    }

    function quizRemoveOption(qIdx, oIdx) {
        var qDiv = document.getElementById('quizQ-' + qIdx);
        if (!qDiv) return;
        var rows = qDiv.querySelectorAll('.q-option');
        if (rows[oIdx]) rows[oIdx].remove();
    }

    // ---------- 保存 ----------
    function saveQuizEditor() {
        var modal = document.getElementById('quizEditorModal');
        var itemId = modal.__quizItemId || 0;
        if (!itemId) return;
        if (!apiRequest) {
            showToast('API客户端未初始化', 'error');
            return;
        }

        var questions = [];
        var wrap = document.getElementById('quizQuestionsWrap');
        Array.prototype.forEach.call(wrap.children, function(qDiv, idx) {
            var options = [];
            qDiv.querySelectorAll('.q-option').forEach(function(row) {
                var content = row.querySelector('.o-content').value.trim();
                if (!content) return;
                options.push({
                    content: content,
                    is_correct: row.querySelector('.o-correct').checked
                });
            });
            var question = qDiv.querySelector('.q-question').value.trim();
            if (!question) return;
            questions.push({
                question: question,
                question_type: qDiv.querySelector('.q-type').value,
                explanation: qDiv.querySelector('.q-explanation').value.trim(),
                points: 1,
                options: options
            });
        });

        if (!questions.length) {
            showToast('请至少填写一道题目', 'error');
            return;
        }
        // 每道题至少一个正确选项
        for (var i = 0; i < questions.length; i++) {
            var hasCorrect = questions[i].options.some(function(o) { return o.is_correct; });
            if (!hasCorrect) {
                showToast('第 ' + (i + 1) + ' 题请勾选至少一个正确答案', 'error');
                return;
            }
        }

        var btn = document.getElementById('saveQuizBtn');
        btn.disabled = true;
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>保存中...';

        apiRequest('PUT', '/course-items/' + itemId + '/quiz', {
            passing_score: Number(document.getElementById('quizPassingScore').value || 70),
            attempts_allowed: document.getElementById('quizAttemptsAllowed').value ? Number(document.getElementById('quizAttemptsAllowed').value) : null,
            status: 'published',
            questions: questions
        }).then(function(resp) {
            if (resp && resp.code === 9999) {
                closeQuizEditorModal();
                showToast('测验已保存', 'success');
                if (window.refreshQuizStatus) window.refreshQuizStatus();
                return;
            }
            showToast((resp && resp.msg) ? resp.msg : '保存失败', 'error');
        }).catch(function() {
            showToast('网络错误，请稍后重试', 'error');
        }).finally(function() {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    }

    // ---------- 工具 ----------
    function escapeHtmlAttr(str) {
        return String(str || '').replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }

    function escapeHtmlForTextarea(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function showToast(message, type) {
        type = type || 'info';
        var existing = document.getElementById('global-toast');
        if (existing) existing.remove();
        var colors = {
            success: 'bg-green-100 text-green-800 border-green-200',
            error: 'bg-red-100 text-red-800 border-red-200',
            warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            info: 'bg-blue-100 text-blue-800 border-blue-200'
        };
        var icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        var toast = document.createElement('div');
        toast.id = 'global-toast';
        toast.className = 'fixed top-6 right-6 ' + (colors[type] || colors.info) + ' border rounded-lg px-4 py-3 shadow-lg z-[10000] flex items-center space-x-3 max-w-sm fade-in';
        toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + ' text-lg"></i><span>' + message + '</span>';
        document.body.appendChild(toast);
        setTimeout(function() {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    if (toast.parentNode) toast.remove();
                }, 300);
            }
        }, 3000);
    }

    // ---------- 全局交互 ----------
    document.addEventListener('click', function(e) {
        var modal = document.getElementById('quizEditorModal');
        if (modal && modal.classList.contains('show') && e.target === modal) {
            closeQuizEditorModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuizEditorModal();
        }
    });
</script>