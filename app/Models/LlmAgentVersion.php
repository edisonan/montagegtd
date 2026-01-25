<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlmAgentVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agent_id',
        'version_name',
        'version_number',
        'model_id',
        'system_prompt',
        'temperature',
        'top_p',
        'max_tokens',
        'context_length',
        'tools_config',
        'is_default',
        'is_active',
        'usage_count',
        'created_by',
        'change_log'
    ];

    protected $casts = [
        'agent_id' => 'integer',
        'version_number' => 'integer',
        'model_id' => 'integer',
        'temperature' => 'decimal:2',
        'top_p' => 'decimal:3',
        'max_tokens' => 'integer',
        'context_length' => 'integer',
        'tools_config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function agent()
    {
        return $this->belongsTo(LlmAgent::class, 'agent_id');
    }

    public function model()
    {
        return $this->belongsTo(LlmModel::class, 'model_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}