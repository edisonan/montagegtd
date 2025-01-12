<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model {
	protected $table = 'daily_summarys';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'summary_date',
			'work_content',
			'life_content',
			'status' 
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
	 * Get the user that owns the report.
	 */
	public function user() {
		return $this->belongsTo ( User::class );
	}
}
