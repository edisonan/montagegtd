<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmChatAttachment extends Model
{
    protected $table = 'llm_chat_attachments';

    protected $fillable = [
        'uuid',
        'user_id',
        'session_id',
        'conversation_id',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'storage_path',
        'extracted_text',
        'status',
        'error_message',
    ];

    protected $hidden = [
        'storage_path',
        'extracted_text',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'session_id' => 'integer',
        'conversation_id' => 'integer',
        'size' => 'integer',
    ];
}
