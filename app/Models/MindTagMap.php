<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MindTagMap extends Model {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'tag_id',
			'mind_id'
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ 
			'tag_id' => 'int',
			'mind_id' => 'int'
	];
	
	/**
	 * Get the user that owns the task.
	 */
	public function tag() {
		return $this->belongsTo ( Tag::class );
	}
	
	/**
	 * Get all of the tags for the user.
	 */
	public function mind() {
		return $this->belongsTo ( Mind::class );
	}
}
