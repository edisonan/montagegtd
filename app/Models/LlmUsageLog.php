<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmUsageLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'provider_id',
        'model_id',
        'credential_id',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost',
        'request_time',
        'status',
        'error_message',
        'request_data',
        'response_data'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'request_data' => 'array',
        'response_data' => 'array',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost' => 'decimal:6',
        'request_time' => 'decimal:3'
    ];

    public function provider()
    {
        return $this->belongsTo(LlmProvider::class, 'provider_id');
    }

    public function model()
    {
        return $this->belongsTo(LlmModel::class, 'model_id');
    }

    public function credential()
    {
        return $this->belongsTo(LlmProviderCredential::class, 'credential_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
