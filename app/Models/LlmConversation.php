<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlmConversation extends Model
{

    protected $fillable = [
        'user_id',
        'session_id',
        'model_id', 
        'credential_id',
        'question',
        'answer',
        'request_data',
        'response_data',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost',
        'answered_at'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'answered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function llmModel(): BelongsTo
    {
        return $this->belongsTo(LlmModel::class, 'model_id');
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(LlmProviderCredential::class, 'credential_id');
    }
}
