<?php

namespace App;

use App\Task;
use App\Pomo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get all of the tasks for the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    /**
     * Get all of the tasks for the user.
     */
    public function pomos()
    {
    	return $this->hasMany(Pomo::class);
    }
    
    /**
     * Get all of the tasks for the user.
     */
    public function notes()
    {
    	return $this->hasMany(Note::class);
    }
    
    /**
     * Get all of the thirds for the user.
     */
    public function thirds()
    {
    	return $this->hasMany(Third::class);
    }
    
    /**
     * Get all of the tags for the user.
     */
    public function goals()
    {
    	return $this->hasMany(Goal::class);
    }
}
