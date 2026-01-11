<!-- AI问答模态框 -->
<div class="modal fade" id="askAIModal" tabindex="-1" role="dialog" aria-labelledby="askAIModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askAIModalLabel">AI问答</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <input type="hidden" id="refer_text_id" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="query">问答要求</label>
                    <input type="text" class="form-control" id="query" value="请帮我润色这段文字">
                    <button type="button" class="btn btn-info" onclick="triggerAskAI()">开始</button>
                </div>
                <div class="form-group">
                    <label for="askAIResult">问答结果</label>
                    <textarea class="form-control" id="askAIResult" rows="10" readonly></textarea>
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="useAskAIText()">使用问答结果</button>
                        <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="clearAskAIResult()">清空</button>
                    </div>
                </div>
                <div id="askAILoading" class="text-center" style="display: none;">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // AI问答功能
    function openAskAIModal($referTextId='') {
        document.getElementById('refer_text_id').value = $referTextId;

        // 设置默认润色要求
        document.getElementById('query').value = '请帮我润色这段文字';
        document.getElementById('askAIResult').value = '';
        $('#askAIModal').modal('show');
    }

    // 处理AI问答
    function startAskAIProcess(referText, query) {

        // 显示加载状态
        document.getElementById('askAILoading').style.display = 'block';
        document.getElementById('askAIResult').value = '';

        // 获取CSRF令牌 - 修复可能找不到meta标签的问题
        let csrfToken = '';
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const inputToken = document.querySelector('input[name="_token"]');

        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        } else if (inputToken) {
            csrfToken = inputToken.value;
        } else {
            // 如果都没找到，尝试从页面其他地方获取
            csrfToken = '{{ csrf_token() }}';
        }

        // 发送请求到后端进行AI问答
        fetch('/llm/ask-ai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                referText: referText,
                query: query
            })
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.error || `HTTP error! status: ${response.status}`);
                    });
                }

                // 检查是否为流式响应
                if (response.headers.get('content-type')?.includes('text/event-stream')) {
                    // 处理流式响应
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';
                    let completeResult = '';

                    return new Promise((resolve, reject) => {
                        function readStream() {
                            reader.read().then(({done, value}) => {
                                if (done) {
                                    resolve(completeResult);
                                    return;
                                }

                                buffer += decoder.decode(value, {stream: true});

                                // 按行分割处理SSE响应
                                const lines = buffer.split('\n');
                                buffer = lines.pop(); // 保留不完整的行

                                for(const line of lines) {
                                    if(line.startsWith('data: ') && line !== 'data: [DONE]') {
                                        const dataStr = line.slice(6); // 移除 'data: ' 前缀
                                        try {
                                            const parsed = JSON.parse(dataStr);
                                            // 检查是否为OpenAI格式的流式响应
                                            if (parsed.choices && parsed.choices[0] && parsed.choices[0].delta) {
                                                const content = parsed.choices[0].delta.content;
                                                if (content) {
                                                    completeResult += content;
                                                    // 实时更新结果显示
                                                    document.getElementById('askAIResult').value = completeResult;
                                                }
                                            } else {
                                                // 处理简单文本响应
                                                if(dataStr.trim()) {
                                                    completeResult += dataStr;
                                                    document.getElementById('askAIResult').value = completeResult;
                                                }
                                            }
                                        } catch (e) {
                                            // 如果解析JSON失败，直接当作文本处理
                                            if(dataStr.trim()) {
                                                completeResult += dataStr;
                                                document.getElementById('askAIResult').value = completeResult;
                                            }
                                        }
                                    }
                                }

                                readStream();
                            }).catch(reject);
                        }
                        readStream();
                    });
                } else {
                    return response.text();
                }
            })
            .then(data => {
                document.getElementById('askAIResult').value = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('askAIResult').value = '润色失败: ' + error.message +
                    '\n\n请检查：\n1. 是否已配置LLM提供商\n2. 是否有可用的模型\n3. 凭据是否有效';
            })
            .finally(() => {
                document.getElementById('askAILoading').style.display = 'none';
            });
    }

    // 触发润色按钮的函数
    function triggerAskAI() {
        const referTextId = document.getElementById('refer_text_id').value;
        let referText = "";

        if (referTextId != '') {
            const element = document.getElementById(referTextId);
            if (element) {
                // 尝试获取value属性，如果没有则获取textContent
                referText = element.value !== undefined ? element.value :
                    (element.textContent || element.innerText || "");
            }
        }
        const query = document.getElementById('query').value;
        startAskAIProcess(referText, query);
    }

    function useAskAIText() {
        const askAIText = document.getElementById('askAIResult').value;
        if (askAIText.trim()) {
            const referTextId = document.getElementById('refer_text_id').value;
            if (referTextId != '') {
                document.getElementById(referTextId).value = askAIText;
            }
            $('#askAIModal').modal('hide');
        } else {
            alert('没有润色结果可以使用');
        }
    }

    function clearAskAIResult() {
        document.getElementById('askAIResult').value = '';
    }
</script>