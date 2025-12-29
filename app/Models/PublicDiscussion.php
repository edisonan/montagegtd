<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicDiscussion extends Model
{
    protected $fillable = [
        'course_id',
        'course_item_id',
        'user_id',
        'title',
        'content',
        'type',
        'is_resolved',
        'is_pinned',
        'vote_count',
        'view_count',
        'reply_count'
    ];

    protected $casts = [
        'course_id' => 'integer',
        'course_item_id' => 'integer',
        'user_id' => 'integer',
        'is_resolved' => 'boolean',
        'is_pinned' => 'boolean',
        'vote_count' => 'integer',
        'view_count' => 'integer',
        'reply_count' => 'integer'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseItem()
    {
        return $this->belongsTo(CourseItem::class, 'course_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(DiscussionReply::class, 'discussion_id');
    }
}