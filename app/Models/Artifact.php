<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artifact extends Model
{
    protected $table = 'artifacts';

    const FILE_HTML = 'html';
    const FILE_JSON = 'json';
    const FILE_MARKDOWN = 'markdown';
    const FILE_TEXT = 'text';

    const TYPE_VISUAL_READING = 'visual_reading';
    const TYPE_MIND_MAP = 'mind_map';
    const TYPE_BRIEFING_LATEST = 'briefing_latest';
    const TYPE_BRIEFING_FOLLOWED = 'briefing_followed';
    const TYPE_NOTE_MIND_MAP = 'note_mind_map';

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';

    protected $fillable = array(
        'user_id',
        'name',
        'file_type',
        'artifact_type',
        'related_type',
        'related_id',
        'content',
        'status',
        'model_name',
        'prompt_version',
        'generated_at',
        'error_message',
        'attempt_count',
    );

    protected $casts = array(
        'user_id' => 'integer',
        'related_id' => 'integer',
        'attempt_count' => 'integer',
        'generated_at' => 'datetime',
    );

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}