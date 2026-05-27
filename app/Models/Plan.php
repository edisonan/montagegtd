<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model {
	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'name',
			'priority',
			'remindtime',
			'deadline',
			'status',
			'plan_type',
			'content',
			'start_time',
			'repeat_type',
			'repeat_days',
			'repeat_meta',
			'sp_points',
			'last_generated_date' 
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ 
				'user_id' => 'int',
				'deleted_at' => 'datetime'
	];
	
	/**
	 * Get the user that owns the task.
	 */
	public function user() {
		return $this->belongsTo ( User::class );
	}
	public function tasks() {
		return $this->hasMany ( Task::class );
	}
}
