<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <a href="{{ route('notes.create') }}" class="btn btn-primary mb-4">创建笔记</a>
                    
                    <!-- 询问AI的模态框 -->
                    <div class="modal fade" id="askAiModal" tabindex="-1" aria-labelledby="askAiModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="askAiModalLabel">问问AI</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="aiQuestion" class="form-label">问题</label>
                                        <textarea class="form-control" id="aiQuestion" rows="3" placeholder="请输入您想问AI的问题..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="aiAnswer" class="form-label">AI回答</label>
                                        <div id="aiAnswer" class="form-control" style="min-height: 200px; max-height: 400px; overflow-y: auto; white-space: pre-wrap;" readonly></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                                    <button type="button" class="btn btn-success" onclick="useAiAnswer()">采用回答</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>标题</th>
                                    <th>内容</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notes as $note)
                                <tr>
                                    <td>{{ $note->id }}</td>
                                    <td>{{ $note->title }}</td>
                                    <td>{{ strip_tags($note->content) }}</td>
                                    <td>{{ $note->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('notes.show', $note->id) }}" class="btn btn-sm btn-info">查看</a>
                                        <a href="{{ route('notes.edit', $note->id) }}" class="btn btn-sm btn-warning">编辑</a>
                                        <form method="POST" action="{{ route('notes.destroy', $note->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗？')">删除</button>
                                        </form>
                                        
                                        <!-- AI润色功能按钮 -->
                                        <button class="btn btn-sm btn-secondary" onclick="openPolishModal({{ $note->id }})">AI润色</button>
                                        
                                        <!-- 问问AI功能按钮 -->
                                        <button class="btn btn-sm btn-info" onclick="openAskAiModal({{ $note->id }})">问问AI</button>
                                        
                                        <a href="{{ route('notes.share', $note->id) }}" class="btn btn-sm btn-success">分享</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 分页 -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI润色模态框 -->
    <div class="modal fade" id="polishModal" tabindex="-1" aria-labelledby="polishModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="polishModalLabel">AI润色</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="originalText" class="form-label">原文内容</label>
                        <textarea class="form-control" id="originalText" rows="4" readonly></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="polishInstruction" class="form-label">润色要求</label>
                        <textarea class="form-control" id="polishInstruction" rows="2">请帮我润色这段文字</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="polishResult" class="form-label">润色结果</label>
                        <div id="polishResult" class="form-control" style="min-height: 150px; max-height: 300px; overflow-y: auto; white-space: pre-wrap;" readonly></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="startPolishBtn" onclick="startPolish()">开始润色</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-success" onclick="usePolishResult()">采用润色结果</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentNoteId = null;
        
        function openPolishModal(noteId) {
            currentNoteId = noteId;
            // 获取笔记内容
            fetch(`/notes/${noteId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('originalText').value = stripHtml(data.note.content);
                    $('#polishModal').modal('show');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('获取笔记内容失败');
                });
        }
        
        function openAskAiModal(noteId) {
            currentNoteId = noteId;
            // 可以预先填入笔记内容作为上下文
            fetch(`/notes/${noteId}`)
                .then(response => response.json())
                .then(data => {
                    const noteTitle = data.note.title;
                    const noteContent = stripHtml(data.note.content);
                    document.getElementById('aiQuestion').value = `关于"${noteTitle}"这篇笔记，${noteContent.substring(0, 200)}...，请就此进行讨论或回答相关问题`;
                    document.getElementById('aiAnswer').innerText = '';
                    $('#askAiModal').modal('show');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('获取笔记内容失败');
                    // 即使获取失败，也打开模态框让用户手动输入问题
                    document.getElementById('aiQuestion').value = '';
                    document.getElementById('aiAnswer').innerText = '';
                    $('#askAiModal').modal('show');
                });
        }
        
        function stripHtml(html) {
            let tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }
        
        async function getCsrfToken() {
            // 尝试从 meta 标签获取
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta && tokenMeta.getAttribute('content')) {
                return tokenMeta.getAttribute('content');
            }
            
            // 尝试从 cookie 获取
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'XSRF-TOKEN') {
                    return decodeURIComponent(value);
                }
            }
            
            // 如果以上方法都失败，尝试从服务器获取
            try {
                const response = await fetch('/sanctum/csrf-cookie');
                const csrfToken = document.cookie
                    .split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1];
                return csrfToken ? decodeURIComponent(csrfToken) : null;
            } catch (error) {
                console.error('获取CSRF令牌失败:', error);
                return null;
            }
        }
        
        async function startPolish() {
            const originalText = document.getElementById('originalText').value;
            const instruction = document.getElementById('polishInstruction').value;
            const resultDiv = document.getElementById('polishResult');
            const startBtn = document.getElementById('startPolishBtn');
            
            resultDiv.innerText = '正在润色中...';
            startBtn.disabled = true;
            
            try {
                const token = await getCsrfToken();
                if (!token) {
                    throw new Error('无法获取CSRF令牌');
                }
                
                const response = await fetch('/llm/polish-text', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'text/plain'
                    },
                    body: JSON.stringify({
                        text: originalText,
                        instruction: instruction
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // 处理流式响应
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                
                resultDiv.innerText = '';
                
                while(true) {
                    const {done, value} = await reader.read();
                    
                    if (done) break;
                    
                    buffer += decoder.decode(value, {stream: true});
                    
                    // 按行分割处理SSE响应
                    const lines = buffer.split('\n');
                    buffer = lines.pop(); // 保留不完整的行
                    
                    for(const line of lines) {
                        if(line.startsWith('data: ') && line !== 'data: [DONE]') {
                            const content = line.slice(6); // 移除 'data: ' 前缀
                            if(content.trim()) {
                                resultDiv.appendChild(document.createTextNode(content));
                            }
                        }
                    }
                }
                
                // 处理缓冲区剩余内容
                if(buffer && buffer.startsWith('data: ') && buffer !== 'data: [DONE]') {
                    const content = buffer.slice(6);
                    if(content.trim()) {
                        resultDiv.appendChild(document.createTextNode(content));
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                resultDiv.innerText = '润色过程中出现错误: ' + error.message;
            } finally {
                startBtn.disabled = false;
            }
        }
        
        async function askAiQuestion() {
            const question = document.getElementById('aiQuestion').value;
            const answerDiv = document.getElementById('aiAnswer');
            const submitBtn = document.getElementById('askAiSubmitBtn');
            
            if(!question.trim()) {
                alert('请输入问题');
                return;
            }
            
            answerDiv.innerText = 'AI正在回答中...';
            if(submitBtn) submitBtn.disabled = true;
            
            try {
                const token = await getCsrfToken();
                if (!token) {
                    throw new Error('无法获取CSRF令牌');
                }
                
                const response = await fetch('/llm/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'text/plain'
                    },
                    body: JSON.stringify({
                        question: question
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // 处理流式响应
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                
                answerDiv.innerText = '';
                
                while(true) {
                    const {done, value} = await reader.read();
                    
                    if (done) break;
                    
                    buffer += decoder.decode(value, {stream: true});
                    
                    // 按行分割处理SSE响应
                    const lines = buffer.split('\n');
                    buffer = lines.pop(); // 保留不完整的行
                    
                    for(const line of lines) {
                        if(line.startsWith('data: ') && line !== 'data: [DONE]') {
                            const content = line.slice(6); // 移除 'data: ' 前缀
                            if(content.trim()) {
                                answerDiv.appendChild(document.createTextNode(content));
                            }
                        }
                    }
                }
                
                // 处理缓冲区剩余内容
                if(buffer && buffer.startsWith('data: ') && buffer !== 'data: [DONE]') {
                    const content = buffer.slice(6);
                    if(content.trim()) {
                        answerDiv.appendChild(document.createTextNode(content));
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                answerDiv.innerText = 'AI回答过程中出现错误: ' + error.message;
            } finally {
                if(submitBtn) submitBtn.disabled = false;
            }
        }
        
        function usePolishResult() {
            if (!currentNoteId) {
                alert('当前没有选中的笔记');
                return;
            }
            
            const polishedContent = document.getElementById('polishResult').innerText;
            if (!polishedContent || polishedContent === '正在润色中...' || polishedContent.includes('错误')) {
                alert('没有有效的润色结果');
                return;
            }
            
            // 获取CSRF令牌并更新笔记
            getCsrfToken().then(token => {
                fetch(`/notes/${currentNoteId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        content: polishedContent
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('润色结果已保存');
                        location.reload(); // 刷新页面以显示更新的内容
                    } else {
                        alert('保存失败: ' + (data.message || '未知错误'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('保存时出现错误');
                });
            });
        }
        
        function useAiAnswer() {
            if (!currentNoteId) {
                alert('当前没有选中的笔记');
                return;
            }
            
            const aiAnswer = document.getElementById('aiAnswer').innerText;
            if (!aiAnswer || aiAnswer === 'AI正在回答中...' || aiAnswer.includes('错误')) {
                alert('没有有效的AI回答');
                return;
            }
            
            // 这里可以根据需求决定如何处理AI回答
            // 比如添加到笔记内容中，或者作为评论等
            const updatedContent = `原内容：

${document.getElementById('originalText')?.value || ''}

AI回答：

${aiAnswer}`;
            
            // 获取CSRF令牌并更新笔记
            getCsrfToken().then(token => {
                fetch(`/notes/${currentNoteId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        content: updatedContent
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('AI回答已保存到笔记');
                        location.reload(); // 刷新页面以显示更新的内容
                    } else {
                        alert('保存失败: ' + (data.message || '未知错误'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('保存时出现错误');
                });
            });
        }
        
        // 添加回车键支持到AI问题输入框
        document.addEventListener('DOMContentLoaded', function() {
            const aiQuestionInput = document.getElementById('aiQuestion');
            if(aiQuestionInput) {
                aiQuestionInput.addEventListener('keydown', function(e) {
                    if(e.key === 'Enter' && e.ctrlKey) {
                        askAiQuestion();
                    }
                });
            }
            
            // 为AI问题输入框添加提示
            const askAiBtns = document.querySelectorAll('.btn-info[onclick^="openAskAiModal"]');
            askAiBtns.forEach(btn => {
                if(!btn.title) {
                    btn.title = '向AI提问关于此笔记的问题';
                }
            });
        });
    </script>
</x-app-layout>