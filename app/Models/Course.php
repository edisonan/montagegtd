<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'platform',
        'instructor',
        'public_url',
        'description',
        'cover_image_url',
        'public_status',
        'created_by',
        'difficulty',
        'estimated_hours',
        'tags',
        'source_type',
        'source_key',
        'content_hash',
        'generated_at',
        'content_status'
    ];

    protected $casts = [
        'tags' => 'array',
        'estimated_hours' => 'integer',
        'created_by' => 'integer',
        'user_id' => 'integer',
        'public_status' => 'integer',
        'generated_at' => 'datetime'
    ];
    
    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'public_status' => 2, // 默认为待审核状态
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseItems()
    {
        return $this->hasMany(CourseItem::class, 'course_id');
    }

    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'course_id');
    }

    public function discussions()
    {
        return $this->hasMany(PublicDiscussion::class, 'course_id');
    }

    public function activities()
    {
        return $this->hasMany(StudyActivity::class, 'course_id');
    }
}
