<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Article extends Model {
	protected $fillable = [ 
			'status',
			'subject',
			'url',
			'image_url',
			'content',
			'published' 
	];
	protected $table = 'articles';
	protected $appends = array ();
	protected $casts = [ 
			'user_id' => 'int' 
	];

    /**
     * @return Feed
     */
	public function feed() {
		return $this->belongsTo ( Feed::class );
	}

    /**
     * @return mixed
     */
	public function getCategoryIdAttribute() {
		return DB::table ( 'articles' )->join ( 'feeds', 'articles.feed_id', '=', 'feeds.id' )->where ( 'articles.id', $this->id )->max ( 'feeds.category_id' );
	}

    /**
     * @return mixed
     */
	public function user() {
		return $this->belongsTo ( User::class );
	}

    public function aiProfile()
    {
        return $this->hasOne(ArticleAiProfile::class);
    }

    public function aiTasks()
    {
        return $this->hasMany(ArticleAiTask::class);
    }

    public function aiRender()
    {
        return $this->hasOne(ArticleAiRender::class);
    }
}
