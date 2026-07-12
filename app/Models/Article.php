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
			'word_count',
			'estimated_read_minutes',
			'published' 
	];
	protected $table = 'articles';
	protected $appends = array ();
	protected $casts = [ 
			'user_id' => 'int',
			'word_count' => 'int',
			'estimated_read_minutes' => 'int'
	];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($article) {
            $metrics = self::calculateReadingMetrics($article->content);
            $article->word_count = $metrics['word_count'];
            $article->estimated_read_minutes = $metrics['estimated_read_minutes'];
        });
    }

    public static function calculateReadingMetrics($content)
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$content)));
        $wordCount = function_exists('mb_strlen')
            ? mb_strlen($plainText, 'UTF-8')
            : strlen($plainText);

        return array(
            'word_count' => (int)$wordCount,
            'estimated_read_minutes' => max(1, (int)ceil($wordCount / 320)),
        );
    }

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
