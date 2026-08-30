<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtifactVersion extends Model
{
    protected $table = 'artifact_versions';

    protected $fillable = array(
        'artifact_id',
        'version',
        'content',
        'status',
        'model_name',
        'prompt_version',
        'generated_at',
        'error_message',
        'custom_prompt',
    );

    protected $casts = array(
        'artifact_id' => 'integer',
        'version' => 'integer',
        'generated_at' => 'datetime',
    );

    public function artifact()
    {
        return $this->belongsTo(Artifact::class);
    }
}