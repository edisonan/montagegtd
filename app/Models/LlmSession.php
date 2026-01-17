<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmSession extends Model
{

    protected $table = 'llm_sessions';

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'agent_id',
        'message_count',
        'token_count',
        'is_active',
        'is_pinned',
        'last_message_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'agent_id' => 'integer',
        'message_count' => 'integer',
        'token_count' => 'integer',
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联智能体
     */
    public function agent()
    {
        return $this->belongsTo(LlmAgent::class, 'agent_id');
    }

    /**
     * 获取会话的消息列表
     */
    public function messages()
    {
        return $this->hasMany(LlmConversation::class, 'session_id', 'id');
    }
}