<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pomo extends Model
{
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * Get the user that owns the pomos.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
