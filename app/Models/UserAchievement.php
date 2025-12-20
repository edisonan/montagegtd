<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    protected $table = 'user_achievement';

    protected $fillable = [
        'user_id',
        'achievement_code',
        'achieved_at',
    ];

    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * 用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 成就
     */
    public function achievement()
    {
        return $this->belongsTo(
            Achievement::class,
            'achievement_code',
            'code'
        );
    }
}
