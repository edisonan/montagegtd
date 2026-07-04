<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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

    public function getPlainApiKey()
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            if (preg_match('/^\$2y\$/', $this->api_key) || preg_match('/^\$2a\$/', $this->api_key) || preg_match('/^\$2b\$/', $this->api_key)) {
                return null;
            }

            return $this->api_key;
        }
    }
}
