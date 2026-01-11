<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;
    
    protected $table = 'applications';
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * 获取与应用关联的代码
     */
    public function codes()
    {
        return $this->hasMany(Code::class, 'app_id');
    }
}