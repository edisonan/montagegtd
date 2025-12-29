<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionReply extends Model
{
    protected $fillable = [
        'discussion_id',
        'user_id',
        'content',
        'is_solution',
        'vote_count'
    ];

    protected $casts = [
        'discussion_id' => 'integer',
        'user_id' => 'integer',
        'is_solution' => 'boolean',
        'vote_count' => 'integer'
    ];

    public function discussion()
    {
        return $this->belongsTo(PublicDiscussion::class, 'discussion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}