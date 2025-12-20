<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'achievement';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'category',
        'point_value',
        'visible',
        'grant_start_at',
        'grant_end_at',
        'expire_at',
        'enabled',
    ];

    protected $casts = [
        'point_value' => 'int',
        'visible'     => 'int',
        'enabled'     => 'int',
    ];

    /**
     * 成就 -> 用户成就记录
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class, 'achievement_code', 'code');
    }
}
