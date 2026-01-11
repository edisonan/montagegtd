<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $table = 'codes';
    
    protected $fillable = [
        'name',
        'type',
        'content',
        'status',
        'path',
        'app_id'
    ];
    
    /**
     * 获取此代码所属的应用
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }
}