<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ 
			'pomo_config',
			'kindle_email',
			'is_start_kindle',
			'with_image_push',
			'cal_token'
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ 
			'user_id' => 'int',
			'pomo_config' => 'array',
			'notify_channels' => 'array'
	];

	public static function defaultPomoConfig() {
		return [
				'day_goal' => 8,
				'week_goal' => 40,
				'month_goal' => 160,
				'focus_minutes' => 25,
				'rest_minutes' => 5
		];
	}

	public static function normalizePomoConfig($config) {
		if (is_string($config)) {
			$decoded = json_decode($config, true);
			$config = is_array($decoded) ? $decoded : [];
		}
		$config = is_array($config) ? $config : [];
		$defaults = self::defaultPomoConfig();
		foreach ($defaults as $key => $value) {
			$defaults[$key] = isset($config[$key]) ? (int)$config[$key] : $value;
		}
		return $defaults;
	}

	public function getPomoConfigValues() {
		return self::normalizePomoConfig($this->pomo_config);
	}
	
	/**
	 * Get the user that owns the task.
	 */
	public function user() {
		return $this->belongsTo ( User::class );
	}
}
