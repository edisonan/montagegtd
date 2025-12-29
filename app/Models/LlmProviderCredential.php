<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlmProviderCredential extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'provider_id',
        'name',
        'api_key',
        'config',
        'is_default',
        'usage_count',
        'last_used_at',
        'quota_limit',
        'quota_used',
        'quota_reset_at',
        'is_active'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'quota_limit' => 'integer',
        'quota_used' => 'integer',
        'last_used_at' => 'datetime',
        'quota_reset_at' => 'datetime'
    ];

    public function provider()
    {
        return $this->belongsTo(LlmProvider::class, 'provider_id');
    }

    public function usageLogs()
    {
        return $this->hasMany(LlmUsageLog::class, 'credential_id');
    }
}