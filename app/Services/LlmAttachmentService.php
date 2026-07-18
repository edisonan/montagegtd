<?php

namespace App\Services;

use App\Models\LlmChatAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;

class LlmAttachmentService
{
    const MAX_EXTRACTED_CHARACTERS = 80000;

    protected $allowedExtensions = [
        'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'html', 'htm', 'log',
        'yaml', 'yml', 'php', 'js', 'jsx', 'ts', 'tsx', 'css', 'scss', 'less',
        'py', 'java', 'go', 'rs', 'sql', 'sh', 'bash', 'zsh', 'ini', 'conf',
        'vue', 'blade', 'env', 'docx', 'pdf',
    ];

    public function create(UploadedFile $file, $userId)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new RuntimeException('暂不支持该文件类型，请上传文本、代码或 DOCX 文件');
        }

        $uuid = bin2hex(random_bytes(16));
        $directory = 'llm-attachments/' . (int)$userId . '/' . date('Y/m');
        $absoluteDirectory = storage_path('app/' . $directory);
        if (!File::isDirectory($absoluteDirectory)) {
            File::makeDirectory($absoluteDirectory, 0750, true);
        }

        $storedName = $uuid . '.' . $extension;
        $file->move($absoluteDirectory, $storedName);
        $relativePath = $directory . '/' . $storedName;
        $absolutePath = storage_path('app/' . $relativePath);

        try {
            $extractedText = $extension === 'pdf'
                ? $this->validatePdf($absolutePath)
                : $this->extractText($absolutePath, $extension);
            if (trim($extractedText) === '') {
                throw new RuntimeException('文件中没有可读取的文本内容');
            }

            return LlmChatAttachment::create([
                'uuid' => $uuid,
                'user_id' => (int)$userId,
                'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                'mime_type' => mb_substr((string)$file->getClientMimeType(), 0, 120),
                'extension' => $extension,
                'size' => (int)File::size($absolutePath),
                'storage_path' => $relativePath,
                'extracted_text' => $extractedText,
                'status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            File::delete($absolutePath);
            throw $e;
        }
    }

    public function delete(LlmChatAttachment $attachment)
    {
        if ($attachment->conversation_id) {
            throw new RuntimeException('已发送的附件不能单独删除');
        }
        File::delete(storage_path('app/' . $attachment->storage_path));
        return $attachment->delete();
    }

    public function deleteForSession($sessionId)
    {
        $attachments = LlmChatAttachment::where('session_id', $sessionId)->get();
        foreach ($attachments as $attachment) {
            File::delete(storage_path('app/' . $attachment->storage_path));
        }
        if (!$attachments->isEmpty()) {
            LlmChatAttachment::whereIn('id', $attachments->pluck('id')->all())->delete();
        }
    }

    public function prepareBranch($sessionId, $conversationId)
    {
        $targetAttachments = LlmChatAttachment::where('session_id', $sessionId)
            ->where('conversation_id', $conversationId)
            ->get();
        $futureAttachments = LlmChatAttachment::where('session_id', $sessionId)
            ->where('conversation_id', '>', $conversationId)
            ->get();

        foreach ($futureAttachments as $attachment) {
            File::delete(storage_path('app/' . $attachment->storage_path));
        }
        if (!$futureAttachments->isEmpty()) {
            LlmChatAttachment::whereIn('id', $futureAttachments->pluck('id')->all())->delete();
        }
        if (!$targetAttachments->isEmpty()) {
            LlmChatAttachment::whereIn('id', $targetAttachments->pluck('id')->all())->update([
                'session_id' => null,
                'conversation_id' => null,
                'updated_at' => now(),
            ]);
        }

        return $targetAttachments;
    }

    public function cloneForConversation($sourceConversationId, $sessionId, $conversationId)
    {
        $clones = collect();
        $attachments = LlmChatAttachment::where('conversation_id', $sourceConversationId)->get();
        foreach ($attachments as $attachment) {
            $clones->push($this->cloneAttachment($attachment, $sessionId, $conversationId));
        }
        return $clones;
    }

    public function cloneAsPending($sourceConversationId, $sessionId = null)
    {
        $clones = collect();
        $attachments = LlmChatAttachment::where('conversation_id', $sourceConversationId)->get();
        try {
            foreach ($attachments as $attachment) {
                $clones->push($this->cloneAttachment($attachment, $sessionId, null));
            }
        } catch (\Throwable $e) {
            foreach ($clones as $clone) {
                File::delete(storage_path('app/' . $clone->storage_path));
                $clone->delete();
            }
            throw $e;
        }
        return $clones;
    }

    public function serialize(LlmChatAttachment $attachment, $withPreview = false)
    {
        $data = [
            'id' => $attachment->id,
            'uuid' => $attachment->uuid,
            'name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'extension' => $attachment->extension,
            'size' => $attachment->size,
            'status' => $attachment->status,
        ];
        if ($withPreview) {
            $data['preview'] = mb_substr($attachment->extracted_text, 0, 600);
            $data['characters'] = mb_strlen($attachment->extracted_text);
        }
        return $data;
    }

    protected function extractText($path, $extension)
    {
        if ($extension === 'docx') {
            $command = '/usr/bin/unzip -p ' . escapeshellarg($path) . ' word/document.xml 2>/dev/null';
            $xml = shell_exec($command);
            if (!$xml) {
                throw new RuntimeException('DOCX 文件结构无法读取');
            }
            $xml = str_replace(['</w:p>', '</w:tr>', '<w:tab/>'], ["\n", "\n", "\t"], $xml);
            $content = html_entity_decode(strip_tags($xml), ENT_QUOTES, 'UTF-8');
        } else {
            $content = File::get($path);
            if (in_array($extension, ['html', 'htm', 'xml'], true)) {
                $content = html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8');
            }
        }

        $encoding = mb_detect_encoding($content, ['UTF-8', 'GB18030', 'GBK', 'BIG-5', 'ISO-8859-1'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }
        $content = str_replace("\0", '', $content);
        $content = preg_replace("/\r\n?/", "\n", $content);
        $content = preg_replace("/[ \t]+\n/", "\n", $content);
        $content = preg_replace("/\n{4,}/", "\n\n\n", $content);

        return trim(mb_substr($content, 0, self::MAX_EXTRACTED_CHARACTERS));
    }

    protected function validatePdf($path)
    {
        $handle = fopen($path, 'rb');
        $signature = $handle ? fread($handle, 5) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($signature !== '%PDF-') {
            throw new RuntimeException('PDF 文件格式无效');
        }
        return '[PDF 文件将在发送时由模型解析]';
    }

    protected function cloneAttachment(LlmChatAttachment $attachment, $sessionId, $conversationId)
    {
        $uuid = bin2hex(random_bytes(16));
        $sourcePath = storage_path('app/' . $attachment->storage_path);
        $relativePath = dirname($attachment->storage_path) . '/' . $uuid . '.' . $attachment->extension;
        $targetPath = storage_path('app/' . $relativePath);
        if (!File::exists($sourcePath) || !File::copy($sourcePath, $targetPath)) {
            throw new RuntimeException('复制分支附件失败');
        }

        try {
            return LlmChatAttachment::create([
                'uuid' => $uuid,
                'user_id' => $attachment->user_id,
                'session_id' => $sessionId,
                'conversation_id' => $conversationId,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'extension' => $attachment->extension,
                'size' => $attachment->size,
                'storage_path' => $relativePath,
                'extracted_text' => $attachment->extracted_text,
                'status' => $attachment->status,
            ]);
        } catch (\Throwable $e) {
            File::delete($targetPath);
            throw $e;
        }
    }
}
