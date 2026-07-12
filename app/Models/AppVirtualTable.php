<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVirtualTable extends Model
{
    use SoftDeletes;

    protected $table = 'app_virtual_tables';

    protected $fillable = array(
        'app_id',
        'name',
        'slug',
        'physical_table',
        'description',
        'status',
    );

    protected $casts = array(
        'app_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    );

    public function application()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function fields()
    {
        return $this->hasMany(AppVirtualTableField::class, 'virtual_table_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
