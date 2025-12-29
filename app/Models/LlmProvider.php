<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlmProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'base_url',
        'api_type',
        'is_active',
        'priority',
        'config_schema',
        'rate_limit_per_minute',
        'concurrent_limit'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'config_schema' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'rate_limit_per_minute' => 'integer',
        'concurrent_limit' => 'integer'
    ];

    public function models()
    {
        return $this->hasMany(LlmModel::class, 'provider_id');
    }

    public function credentials()
    {
        return $this->hasMany(LlmProviderCredential::class, 'provider_id');
    }

    public function usageLogs()
    {
        return $this->hasMany(LlmUsageLog::class, 'provider_id');
    }
}