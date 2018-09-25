<?php

namespace App\Models;

use App\User;
use App\Article;
use Illuminate\Database\Eloquent\Model;

class ArticleMark extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','article_id','content'];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'article_id' => 'int',
    ];

    /**
     * Get the user that owns the task.
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    
    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
