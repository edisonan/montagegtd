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

        'is_public',
        'is_active',
        'usage_count',
        'favorite_count',
        'last_used_at',
        'builtin_slug'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'current_version_id' => 'integer',
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
        return $this->currentVersion ? $this->currentVersion->model : null;
    }

    public function versions()
    {
        return $this->hasMany(LlmAgentVersion::class, 'agent_id');
    }

    public function currentVersion()
    {
        return $this->belongsTo(LlmAgentVersion::class, 'current_version_id');
    }
}