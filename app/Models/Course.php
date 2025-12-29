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
        'is_public',
        'created_by',
        'difficulty',
        'estimated_hours',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_public' => 'boolean',
        'estimated_hours' => 'integer',
        'created_by' => 'integer',
        'user_id' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courseItems()
    {
        return $this->hasMany(CourseItem::class, 'course_id');
    }

    public function userCourses()
    {
        return $this->hasMany(UserCourse::class, 'course_id');
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