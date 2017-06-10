<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Category extends Model
{
	protected $fillable = ['name','user_id'];
	protected $table = 'categories';
	protected $appends = array('unread_count', 'read_count', 'total_count');
	protected $casts = [
			'user_id' => 'int',
	];

	public function feeds()
	{
		return $this->hasMany(Feed::class)->orderBy('created_at');
	}

	public function getUnreadCountAttribute()
	{
		return DB::table('feeds')->join('articles', 'feeds.id', '=', 'articles.feed_id')->where('feeds.category_id', $this->id)->where('articles.status', 'unread')->count();
	}

	public function getReadCountAttribute()
	{
		return DB::table('feeds')->join('articles', 'feeds.id', '=', 'articles.feed_id')->where('feeds.category_id', $this->id)->where('articles.status', 'unread')->count();
	}

	public function getTotalCountAttribute()
	{
		return DB::table('feeds')->join('articles', 'feeds.id', '=', 'articles.feed_id')->where('feeds.category_id', $this->id)->count();
	}
	
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
