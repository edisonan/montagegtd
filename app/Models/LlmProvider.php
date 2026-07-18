<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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
        'api_key',
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

    protected $hidden = array('api_key');

    public function setApiKeyAttribute($value)
    {
        $value = trim((string) $value);
        if ($value === '' && array_key_exists('api_key', $this->attributes)) {
            return;
        }
        $this->attributes['api_key'] = $value === '' ? null : Crypt::encryptString($value);
    }

    public function getPlainApiKey()
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            return $this->api_key;
        }
    }

    public function models()
    {
        return $this->hasMany(LlmModel::class, 'provider_id');
    }

    public function usageLogs()
    {
        return $this->hasMany(LlmUsageLog::class, 'provider_id');
    }
}
