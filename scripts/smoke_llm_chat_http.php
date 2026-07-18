<?php

use App\Models\LlmConversation;
use App\Models\LlmChatAttachment;
use App\Models\LlmSession;
use App\Models\PersonalAccessToken;
use App\Services\PersonalAccessTokenService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__) . '/bootstrap/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$baseUrl = rtrim(isset($argv[1]) ? $argv[1] : 'http://testtask.congcong.us', '/');
$tokenId = null;
$sessionId = null;
$sourceSessionId = null;
$attachmentSessionId = null;
$attachmentBranchSessionId = null;
$attachmentId = null;
$pdfAttachmentId = null;
$plainToken = null;
$exitCode = 0;

function llmSmokeRequest($baseUrl, $path, $token, $method, array $payload = null)
{
    $curl = curl_init($baseUrl . $path);
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
        'User-Agent: MontageGTD-LLM-Smoke/1.0',
    );

    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    );

    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($body === false || $error !== '') {
        throw new RuntimeException('HTTP request failed: ' . $error);
    }

    return array('status' => $status, 'body' => $body);
}

function llmSmokeJson(array $response, $label)
{
    $data = json_decode($response['body'], true);
    if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($data)) {
        throw new RuntimeException($label . ' failed with HTTP ' . $response['status'] . ': ' . mb_substr($response['body'], 0, 500));
    }

    return $data;
}

function llmSmokeUpload($baseUrl, $path, $token, $filePath, $fileName, $mimeType = 'text/plain')
{
    $curl = curl_init($baseUrl . $path);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: MontageGTD-LLM-Smoke/1.0',
        ),
        CURLOPT_POSTFIELDS => array('file' => new CURLFile($filePath, $mimeType, $fileName)),
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ));
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($body === false || $error !== '') {
        throw new RuntimeException('Attachment upload failed: ' . $error);
    }
    return array('status' => $status, 'body' => $body);
}

function llmSmokePdf($text)
{
    $escaped = str_replace(array('\\', '(', ')'), array('\\\\', '\\(', '\\)'), $text);
    $stream = "BT /F1 18 Tf 72 720 Td ({$escaped}) Tj ET";
    $objects = array(
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        5 => "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
    );
    $pdf = "%PDF-1.4\n";
    $offsets = array(0);
    foreach ($objects as $number => $object) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for ($number = 1; $number <= 5; $number++) {
        $pdf .= sprintf('%010d 00000 n ', $offsets[$number]) . "\n";
    }
    return $pdf . "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
}

function llmSmokeParseStream($stream)
{
    $answer = '';
    $conversationId = null;
    $stopped = false;
    $usage = array();
    $error = '';
    foreach (preg_split('/\r?\n/', (string)$stream) as $line) {
        if (strpos($line, 'data:') !== 0) {
            continue;
        }
        $raw = trim(substr($line, 5));
        if ($raw === '' || $raw === '[DONE]') {
            continue;
        }
        $event = json_decode($raw, true);
        if (!is_array($event)) {
            continue;
        }
        if (isset($event['type']) && $event['type'] === 'error') {
            $error = isset($event['message']) ? (string)$event['message'] : 'stream error';
        }
        if (!empty($event['conversation_id'])) {
            $conversationId = (int)$event['conversation_id'];
        }
        if (isset($event['stopped'])) {
            $stopped = (bool)$event['stopped'];
        }
        if (!empty($event['usage']) && is_array($event['usage'])) {
            $usage = $event['usage'];
        }
        if (isset($event['choices'][0]['delta']['content'])) {
            $answer .= (string)$event['choices'][0]['delta']['content'];
        }
    }

    return array(
        'answer' => trim($answer),
        'conversation_id' => $conversationId,
        'stopped' => $stopped,
        'usage' => $usage,
        'error' => $error,
    );
}

function llmSmokeStopStreamingChat($baseUrl, $token, $agentId, $sessionId)
{
    $generationId = 'smoke-stop-' . bin2hex(random_bytes(8));
    $chatBody = '';
    $chatHasContent = false;
    $stopBody = '';
    $stopAdded = false;
    $stopHandle = null;

    $chatHandle = curl_init($baseUrl . '/api/v2/llm/chat');
    curl_setopt_array($chatHandle, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Accept: text/event-stream',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: MontageGTD-LLM-Smoke/1.0',
        ),
        CURLOPT_POSTFIELDS => json_encode(array(
            'agent_id' => $agentId,
            'session_id' => $sessionId,
            'generation_id' => $generationId,
            'query' => '请从1开始逐行输出到300，每行只输出一个数字，不要解释、不要省略。',
        ), JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 180,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$chatBody, &$chatHasContent) {
            $chatBody .= $chunk;
            if (preg_match('/"content"\s*:\s*"(?!")/', $chatBody)) {
                $chatHasContent = true;
            }
            return strlen($chunk);
        },
    ));

    $multi = curl_multi_init();
    curl_multi_add_handle($multi, $chatHandle);
    $running = null;
    $startedAt = microtime(true);

    do {
        do {
            $multiStatus = curl_multi_exec($multi, $running);
        } while ($multiStatus === CURLM_CALL_MULTI_PERFORM);

        if (!$stopAdded && $chatHasContent && $running > 0) {
            $stopHandle = curl_init($baseUrl . '/api/v2/llm/chat/stop');
            curl_setopt_array($stopHandle, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                    'User-Agent: MontageGTD-LLM-Smoke/1.0',
                ),
                CURLOPT_POSTFIELDS => json_encode(array('generation_id' => $generationId)),
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ));
            curl_multi_add_handle($multi, $stopHandle);
            $stopAdded = true;
        }

        if ((microtime(true) - $startedAt) > 170) {
            throw new RuntimeException('stop streaming smoke timed out');
        }
        if ($running > 0) {
            curl_multi_select($multi, 0.1);
        }
    } while ($running > 0);

    $chatStatus = (int)curl_getinfo($chatHandle, CURLINFO_HTTP_CODE);
    $chatError = curl_error($chatHandle);
    $stopStatus = 0;
    if ($stopHandle) {
        $stopStatus = (int)curl_getinfo($stopHandle, CURLINFO_HTTP_CODE);
        $stopBody = curl_multi_getcontent($stopHandle);
        curl_multi_remove_handle($multi, $stopHandle);
        curl_close($stopHandle);
    }
    curl_multi_remove_handle($multi, $chatHandle);
    curl_close($chatHandle);
    curl_multi_close($multi);

    if ($chatError !== '') {
        throw new RuntimeException('stopped chat cURL failed: ' . $chatError);
    }

    return array(
        'chat_status' => $chatStatus,
        'stop_status' => $stopStatus,
        'stop_added' => $stopAdded,
        'stop_response' => json_decode($stopBody, true),
        'stream' => llmSmokeParseStream($chatBody),
    );
}

try {
    $tokenData = $app->make(PersonalAccessTokenService::class)->createToken(array(
        'user_id' => 1,
        'name' => 'llm-http-smoke-' . date('YmdHis'),
        'scopes' => array('read', 'write'),
        'expires_at' => now()->addMinutes(10),
    ));
    $tokenId = $tokenData['id'];
    $plainToken = $tokenData['token'];

    $agents = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/agents', $plainToken, 'GET'), 'agents');
    $agentRows = isset($agents['result']['agents']) ? $agents['result']['agents'] : array();
    if (empty($agentRows)) {
        throw new RuntimeException('No usable agent returned by API');
    }
    $selectedAgent = null;
    foreach ($agentRows as $agentRow) {
        if (isset($agentRow['builtin_slug']) && $agentRow['builtin_slug'] === 'builtin_common') {
            $selectedAgent = $agentRow;
            break;
        }
    }
    $selectedAgent = $selectedAgent ?: $agentRows[0];
    $agentId = (string)$selectedAgent['id'];

    $attachmentSession = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'title' => 'LLM attachment smoke ' . date('Y-m-d H:i:s'),
    )), 'create attachment session');
    $attachmentSessionId = $attachmentSession['data']['id'];
    $attachmentCode = 'MONTAGE-ATTACH-7429';
    $pdfCode = 'MONTAGE-PDF-7391';
    $temporaryAttachment = tempnam(sys_get_temp_dir(), 'llm-attachment-');
    file_put_contents($temporaryAttachment, "这是附件上下文测试。\n验证口令：{$attachmentCode}\n");
    $upload = llmSmokeJson(llmSmokeUpload(
        $baseUrl,
        '/api/v2/llm/attachments',
        $plainToken,
        $temporaryAttachment,
        'attachment-smoke.txt'
    ), 'upload attachment');
    @unlink($temporaryAttachment);
    $attachmentId = isset($upload['data']['id']) ? (int)$upload['data']['id'] : 0;
    if (!$attachmentId) {
        throw new RuntimeException('attachment id missing after upload');
    }
    $temporaryPdf = tempnam(sys_get_temp_dir(), 'llm-pdf-');
    file_put_contents($temporaryPdf, llmSmokePdf('Verification code: ' . $pdfCode));
    $pdfUpload = llmSmokeJson(llmSmokeUpload(
        $baseUrl,
        '/api/v2/llm/attachments',
        $plainToken,
        $temporaryPdf,
        'verification.pdf',
        'application/pdf'
    ), 'upload PDF attachment');
    @unlink($temporaryPdf);
    $pdfAttachmentId = isset($pdfUpload['data']['id']) ? (int)$pdfUpload['data']['id'] : 0;
    if (!$pdfAttachmentId) {
        throw new RuntimeException('PDF attachment id missing after upload');
    }
    $attachmentChat = llmSmokeRequest($baseUrl, '/api/v2/llm/chat', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'session_id' => $attachmentSessionId,
        'generation_id' => 'smoke-attachment-' . bin2hex(random_bytes(8)),
        'query' => '请读取两个附件并只回复其中的两个验证口令，不要解释。',
        'attachment_ids' => array($attachmentId, $pdfAttachmentId),
    ));
    if ($attachmentChat['status'] < 200 || $attachmentChat['status'] >= 300) {
        throw new RuntimeException('attachment chat failed with HTTP ' . $attachmentChat['status'] . ': ' . mb_substr($attachmentChat['body'], 0, 500));
    }
    $attachmentStream = llmSmokeParseStream($attachmentChat['body']);
    if ($attachmentStream['error'] !== '') {
        throw new RuntimeException('attachment stream failed: ' . $attachmentStream['error']);
    }
    $attachmentDetail = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $attachmentSessionId, $plainToken, 'GET'), 'attachment session detail');
    $attachmentMessages = isset($attachmentDetail['data']['messages']) ? $attachmentDetail['data']['messages'] : array();
    $storedAttachment = LlmChatAttachment::find($attachmentId);
    $storedAttachmentPath = $storedAttachment ? storage_path('app/' . $storedAttachment->storage_path) : '';
    $storedPdfAttachment = LlmChatAttachment::find($pdfAttachmentId);
    $storedPdfAttachmentPath = $storedPdfAttachment ? storage_path('app/' . $storedPdfAttachment->storage_path) : '';
    $attachmentBound = $storedAttachment
        && $storedPdfAttachment
        && (int)$storedAttachment->session_id === (int)$attachmentSessionId
        && (int)$storedPdfAttachment->session_id === (int)$attachmentSessionId
        && !empty($storedAttachment->conversation_id)
        && (int)$storedAttachment->conversation_id === (int)$storedPdfAttachment->conversation_id;
    $attachmentVisible = isset($attachmentMessages[0]['attachments'][0]['id'])
        && count($attachmentMessages[0]['attachments']) === 2
        && in_array($attachmentId, array_column($attachmentMessages[0]['attachments'], 'id'))
        && in_array($pdfAttachmentId, array_column($attachmentMessages[0]['attachments'], 'id'));
    $attachmentConversationId = isset($attachmentMessages[0]['conversation_id']) ? (int)$attachmentMessages[0]['conversation_id'] : 0;
    $attachmentBranch = llmSmokeJson(llmSmokeRequest(
        $baseUrl,
        '/api/v2/llm/sessions/' . $attachmentSessionId . '/messages/' . $attachmentConversationId . '/branch',
        $plainToken,
        'POST',
        array('query' => '请重新读取附件中的验证口令。')
    ), 'create attachment branch');
    $attachmentBranchSessionId = isset($attachmentBranch['data']['session_id']) ? (int)$attachmentBranch['data']['session_id'] : 0;
    $branchAttachmentIds = array_map('intval', array_column($attachmentBranch['data']['attachments'], 'id'));
    $branchAttachments = LlmChatAttachment::whereIn('id', $branchAttachmentIds)->get();
    $branchAttachmentPaths = $branchAttachments->map(function ($attachment) {
        return storage_path('app/' . $attachment->storage_path);
    })->all();
    $attachmentBranchReady = $attachmentBranchSessionId
        && count($branchAttachmentIds) === 2
        && !in_array($attachmentId, $branchAttachmentIds)
        && !in_array($pdfAttachmentId, $branchAttachmentIds)
        && $branchAttachments->every(function ($attachment) use ($attachmentBranchSessionId) {
            return (int)$attachment->session_id === (int)$attachmentBranchSessionId
                && empty($attachment->conversation_id)
                && file_exists(storage_path('app/' . $attachment->storage_path));
        });
    llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $attachmentBranchSessionId, $plainToken, 'DELETE'), 'delete attachment branch');
    $attachmentBranchCleanup = LlmChatAttachment::whereIn('id', $branchAttachmentIds)->count() === 0;
    foreach ($branchAttachmentPaths as $branchAttachmentPath) {
        $attachmentBranchCleanup = $attachmentBranchCleanup && !file_exists($branchAttachmentPath);
    }
    $attachmentBranchSessionId = null;
    llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $attachmentSessionId, $plainToken, 'DELETE'), 'delete attachment session');
    $attachmentDeleted = LlmChatAttachment::where('id', $attachmentId)->count() === 0
        && LlmChatAttachment::where('id', $pdfAttachmentId)->count() === 0
        && ($storedAttachmentPath === '' || !file_exists($storedAttachmentPath))
        && ($storedPdfAttachmentPath === '' || !file_exists($storedPdfAttachmentPath));
    $attachmentSessionId = null;
    $attachmentId = null;
    $pdfAttachmentId = null;

    $session = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'title' => '新对话',
    )), 'create session');
    $sessionId = $session['data']['id'];
    $sourceSessionId = $sessionId;

    $chat = llmSmokeRequest($baseUrl, '/api/v2/llm/chat', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'session_id' => $sessionId,
        'query' => '请只回复：HTTP页面链路成功',
    ));
    if ($chat['status'] < 200 || $chat['status'] >= 300) {
        throw new RuntimeException('chat failed with HTTP ' . $chat['status'] . ': ' . mb_substr($chat['body'], 0, 500));
    }
    $firstStream = llmSmokeParseStream($chat['body']);
    if ($firstStream['error'] !== '') {
        throw new RuntimeException('first stream failed: ' . $firstStream['error']);
    }
    $answer = $firstStream['answer'];

    $detail = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sessionId, $plainToken, 'GET'), 'session detail');
    $messages = isset($detail['data']['messages']) ? $detail['data']['messages'] : array();
    $lastMessage = empty($messages) ? array() : $messages[count($messages) - 1];
    $generatedTitle = isset($detail['data']['title']) ? $detail['data']['title'] : '';
    if (!$firstStream['conversation_id']) {
        throw new RuntimeException('first stream conversation id missing: ' . mb_substr($chat['body'], 0, 1000));
    }
    $feedbackResponse = llmSmokeJson(llmSmokeRequest(
        $baseUrl,
        '/api/v2/llm/sessions/' . $sessionId . '/messages/' . $firstStream['conversation_id'] . '/feedback',
        $plainToken,
        'PUT',
        array('feedback' => 1)
    ), 'message feedback for conversation ' . $firstStream['conversation_id']);
    $feedbackDetail = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sessionId, $plainToken, 'GET'), 'feedback detail');
    $feedbackMessages = isset($feedbackDetail['data']['messages']) ? $feedbackDetail['data']['messages'] : array();
    $persistedFeedback = isset($feedbackMessages[1]['feedback']) ? (int)$feedbackMessages[1]['feedback'] : 0;

    $secondChat = llmSmokeRequest($baseUrl, '/api/v2/llm/chat', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'session_id' => $sessionId,
        'generation_id' => 'smoke-second-' . bin2hex(random_bytes(8)),
        'query' => '请只回复：第二轮成功',
    ));
    if ($secondChat['status'] < 200 || $secondChat['status'] >= 300) {
        throw new RuntimeException('second chat failed with HTTP ' . $secondChat['status']);
    }
    $secondStream = llmSmokeParseStream($secondChat['body']);
    if ($secondStream['error'] !== '') {
        throw new RuntimeException('second stream failed: ' . $secondStream['error']);
    }
    $detailBeforeBranch = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sessionId, $plainToken, 'GET'), 'detail before branch');
    $messagesBeforeBranch = isset($detailBeforeBranch['data']['messages']) ? $detailBeforeBranch['data']['messages'] : array();
    $firstConversationId = isset($messagesBeforeBranch[0]['conversation_id']) ? (int)$messagesBeforeBranch[0]['conversation_id'] : 0;
    if (!$firstConversationId) {
        throw new RuntimeException('conversation_id missing from session messages: ' . json_encode($detailBeforeBranch, JSON_UNESCAPED_UNICODE));
    }

    $branch = llmSmokeJson(llmSmokeRequest(
        $baseUrl,
        '/api/v2/llm/sessions/' . $sessionId . '/messages/' . $firstConversationId . '/branch',
        $plainToken,
        'POST',
        array('query' => '请只回复：编辑分支成功')
    ), 'branch from message');
    $branchSessionId = isset($branch['data']['session_id']) ? (int)$branch['data']['session_id'] : 0;
    if (!$branchSessionId || $branchSessionId === (int)$sourceSessionId) {
        throw new RuntimeException('branch did not create a new session');
    }

    $sourceAfterBranch = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sourceSessionId, $plainToken, 'GET'), 'source after branch');
    $sourceMessagesAfterBranch = isset($sourceAfterBranch['data']['messages']) ? $sourceAfterBranch['data']['messages'] : array();
    $branchNavigation = isset($sourceAfterBranch['data']['branch_navigation']) ? $sourceAfterBranch['data']['branch_navigation'] : array();
    $detailAfterTruncate = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $branchSessionId, $plainToken, 'GET'), 'new branch detail');
    $truncatedMessages = isset($detailAfterTruncate['data']['messages']) ? $detailAfterTruncate['data']['messages'] : array();
    $sessionId = $branchSessionId;

    $branchChat = llmSmokeRequest($baseUrl, '/api/v2/llm/chat', $plainToken, 'POST', array(
        'agent_id' => $agentId,
        'session_id' => $sessionId,
        'generation_id' => 'smoke-branch-' . bin2hex(random_bytes(8)),
        'query' => '请只回复：编辑分支成功',
    ));
    if ($branchChat['status'] < 200 || $branchChat['status'] >= 300) {
        throw new RuntimeException('branch chat failed with HTTP ' . $branchChat['status']);
    }
    $branchStream = llmSmokeParseStream($branchChat['body']);
    $detailAfterBranch = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sessionId, $plainToken, 'GET'), 'detail after branch');
    $branchMessages = isset($detailAfterBranch['data']['messages']) ? $detailAfterBranch['data']['messages'] : array();
    $branchLast = empty($branchMessages) ? array() : $branchMessages[count($branchMessages) - 1];

    $stopResult = llmSmokeStopStreamingChat($baseUrl, $plainToken, $agentId, $sessionId);
    $stoppedConversation = LlmConversation::where('session_id', $sessionId)->orderBy('id', 'desc')->first();
    $stoppedResponseData = $stoppedConversation && is_array($stoppedConversation->response_data)
        ? $stoppedConversation->response_data
        : array();

    $deleted = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sessionId, $plainToken, 'DELETE'), 'delete session');
    $sourceDeleted = llmSmokeJson(llmSmokeRequest($baseUrl, '/api/v2/llm/sessions/' . $sourceSessionId, $plainToken, 'DELETE'), 'delete source session');
    $conversationsAfterDelete = LlmConversation::whereIn('session_id', array($sessionId, $sourceSessionId))->count();
    $sessionId = null;
    $sourceSessionId = null;

    echo json_encode(array(
        'success' => $answer === 'HTTP页面链路成功'
            && strpos($attachmentStream['answer'], $attachmentCode) !== false
            && strpos($attachmentStream['answer'], $pdfCode) !== false
            && count($attachmentMessages) === 2
            && $attachmentBound
            && $attachmentVisible
            && $attachmentBranchReady
            && $attachmentBranchCleanup
            && $attachmentDeleted
            && count($messages) === 2
            && isset($lastMessage['content'])
            && $lastMessage['content'] === $answer
            && $firstStream['conversation_id'] > 0
            && $generatedTitle !== '新对话'
            && !empty($feedbackResponse['success'])
            && $persistedFeedback === 1
            && $secondStream['answer'] === '第二轮成功'
            && count($messagesBeforeBranch) === 4
            && !empty($branch['success'])
            && count($sourceMessagesAfterBranch) === 4
            && count($branchNavigation) === 2
            && count($truncatedMessages) === 0
            && $branchStream['answer'] === '编辑分支成功'
            && count($branchMessages) === 2
            && isset($branchLast['content'])
            && $branchLast['content'] === '编辑分支成功'
            && $stopResult['stop_added']
            && $stopResult['chat_status'] === 200
            && $stopResult['stop_status'] === 200
            && !empty($stopResult['stop_response']['success'])
            && $stopResult['stream']['stopped']
            && mb_strlen($stopResult['stream']['answer']) > 0
            && isset($stoppedResponseData['status'])
            && $stoppedResponseData['status'] === 'stopped'
            && $conversationsAfterDelete === 0
            && !empty($deleted['success'])
            && !empty($sourceDeleted['success']),
        'agents_http' => true,
        'session_create_http' => true,
        'attachment_answer' => $attachmentStream['answer'],
        'attachment_message_count' => count($attachmentMessages),
        'attachment_bound' => $attachmentBound,
        'attachment_visible' => $attachmentVisible,
        'attachment_branch_ready' => $attachmentBranchReady,
        'attachment_branch_cleanup' => $attachmentBranchCleanup,
        'attachment_cleanup' => $attachmentDeleted,
        'chat_http_status' => $chat['status'],
        'stream_answer' => $answer,
        'history_message_count' => count($messages),
        'history_last_content' => isset($lastMessage['content']) ? $lastMessage['content'] : null,
        'stream_conversation_id' => $firstStream['conversation_id'],
        'generated_title' => $generatedTitle,
        'persisted_feedback' => $persistedFeedback,
        'stream_total_tokens' => isset($firstStream['usage']['total_tokens']) ? (int)$firstStream['usage']['total_tokens'] : 0,
        'multi_turn_message_count' => count($messagesBeforeBranch),
        'source_preserved_message_count' => count($sourceMessagesAfterBranch),
        'branch_navigation_count' => count($branchNavigation),
        'new_branch_initial_message_count' => count($truncatedMessages),
        'branch_answer' => $branchStream['answer'],
        'branch_message_count' => count($branchMessages),
        'stop_http_status' => $stopResult['stop_status'],
        'stop_stream_marked' => $stopResult['stream']['stopped'],
        'stop_partial_characters' => mb_strlen($stopResult['stream']['answer']),
        'stop_persisted_status' => isset($stoppedResponseData['status']) ? $stoppedResponseData['status'] : null,
        'conversations_after_delete' => $conversationsAfterDelete,
        'session_delete_http' => !empty($deleted['success']),
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, json_encode(array(
        'success' => false,
        'error' => $e->getMessage(),
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL);
    $exitCode = 1;
} finally {
    if ($attachmentBranchSessionId) {
        $app->make(App\Services\LlmAttachmentService::class)->deleteForSession($attachmentBranchSessionId);
        LlmConversation::where('session_id', $attachmentBranchSessionId)->delete();
        LlmSession::where('id', $attachmentBranchSessionId)->where('user_id', 1)->delete();
    }
    if ($attachmentId) {
        $orphanAttachment = LlmChatAttachment::find($attachmentId);
        if ($orphanAttachment) {
            @unlink(storage_path('app/' . $orphanAttachment->storage_path));
            $orphanAttachment->delete();
        }
    }
    if ($pdfAttachmentId) {
        $orphanPdf = LlmChatAttachment::find($pdfAttachmentId);
        if ($orphanPdf) {
            @unlink(storage_path('app/' . $orphanPdf->storage_path));
            $orphanPdf->delete();
        }
    }
    if ($attachmentSessionId) {
        $app->make(App\Services\LlmAttachmentService::class)->deleteForSession($attachmentSessionId);
        LlmConversation::where('session_id', $attachmentSessionId)->delete();
        LlmSession::where('id', $attachmentSessionId)->where('user_id', 1)->delete();
    }
    if ($sessionId) {
        $app->make(App\Services\LlmAttachmentService::class)->deleteForSession($sessionId);
        LlmConversation::where('session_id', $sessionId)->delete();
        LlmSession::where('id', $sessionId)->where('user_id', 1)->delete();
    }
    if ($sourceSessionId && $sourceSessionId !== $sessionId) {
        $app->make(App\Services\LlmAttachmentService::class)->deleteForSession($sourceSessionId);
        LlmConversation::where('session_id', $sourceSessionId)->delete();
        LlmSession::where('id', $sourceSessionId)->where('user_id', 1)->delete();
    }
    if ($tokenId) {
        PersonalAccessToken::where('id', $tokenId)->delete();
    }
}

exit($exitCode);
