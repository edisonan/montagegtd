<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Focus extends Model {
	const DEFAULT_INTERVAL = 1500; // 25min
	const DEFAULT_REST_INTERVAL = 300; // 5min
	const STATUS_INIT = 1;
	const STATUS_PROCESSING = 2;
	const STATUS_FINISHED = 3;
	const STATUS_RESTING = 4;

	/**
	 * Legacy table keeps singular name.
	 *
	 * @var string
	 */
	protected $table = 'focus';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'name',
			'rating',
			'review_note',
			'status',
			'user_id',
			'start_time',
			'end_time',
			'rest_start_time',
			'rest_end_time',
			'rest_status' 
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ 
			'user_id' => 'int' 
	];
	
	/**
	 * Get the user that owns the focuss.
	 */
	public function user() {
		return $this->belongsTo ( User::class );
	}
}
