<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'name',
			'content',
			'priority',
			'planned_start_time',
			'planned_end_time',
			'remindtime',
			'deadline',
			'status',
			'rating',
			'review_note',
			'parent_task_id',
			'plan_id',
			'is_top',
			'is_doing',
			'mode',
			'study_scheduled_date',
			'study_repeat_type',
			'study_repeat_days',
			'study_repeat_meta',
			'study_sp_points',
			'study_source_task_id' 
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ 
			'user_id' => 'int',
			'parent_task_id' => 'int',
			'plan_id' => 'int' 
	];
	
	/**
	 * Get the user that owns the task.
	 */
	public function user() {
		return $this->belongsTo ( User::class );
	}
	
	/**
	 * Get all of the tags for the user.
	 */
	public function parentTask() {
		return $this->belongsTo ( Task::class, 'parent_task_id' );
	}
	public function childTasks() {
		return $this->hasMany ( Task::class, 'parent_task_id' );
	}
	public function plan() {
		return $this->belongsTo ( Plan::class, 'plan_id' );
	}
}
