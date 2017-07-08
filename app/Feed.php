<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
	protected $fillable = ['feed_name','feed_desc','url','favicon','user_id','category_id','type','orders','status'];
	protected $table = 'feeds';
	protected $appends = array('unread_count', 'read_count', 'total_count');
	protected $casts = [
			'user_id' => 'int',
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function articles()
	{
		return $this->hasMany(Article::class);
	}

	public function getUnreadCountAttribute()
	{
		return $this->articles->where('status','unread')->count();
	}

	public function getReadCountAttribute()
	{
		return $this->articles->where('status','read')->count();
	}

	public function getTotalCountAttribute()
	{
		return $this->articles->count();
	}
	
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
