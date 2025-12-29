<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LlmModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'provider_id',
        'name',
        'display_name',
        'model_type',
        'context_length',
        'max_tokens',
        'input_price_per_1k',
        'output_price_per_1k',
        'is_active',
        'capabilities',
        'sort_order'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'context_length' => 'integer',
        'max_tokens' => 'integer',
        'input_price_per_1k' => 'decimal:6',
        'output_price_per_1k' => 'decimal:6',
        'sort_order' => 'integer'
    ];

    public function provider()
    {
        return $this->belongsTo(LlmProvider::class, 'provider_id');
    }

    public function usageLogs()
    {
        return $this->hasMany(LlmUsageLog::class, 'model_id');
    }
}