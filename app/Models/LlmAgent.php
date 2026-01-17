<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlmAgent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'avatar',
        'model_id',
        'system_prompt',
        'temperature',
        'top_p',
        'max_tokens',
        'context_length',
        'tools_config',
        'is_public',
        'is_active',
        'usage_count',
        'favorite_count',
        'last_used_at',
        'builtin_slug'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'model_id' => 'integer',
        'temperature' => 'decimal:2',
        'top_p' => 'decimal:3',
        'max_tokens' => 'integer',
        'context_length' => 'integer',
        'tools_config' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'favorite_count' => 'integer',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'builtin_slug' => 'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->belongsTo(LlmModel::class, 'model_id');
    }
}