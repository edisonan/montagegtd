<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVirtualTableField extends Model
{
    use SoftDeletes;

    protected $table = 'app_virtual_table_fields';

    protected $fillable = array(
        'virtual_table_id',
        'name',
        'slug',
        'physical_name',
        'type',
        'length',
        'nullable',
        'default_enabled',
        'default_value',
        'indexed',
        'description',
        'sort_order',
        'status',
    );

    protected $casts = array(
        'virtual_table_id' => 'integer',
        'length' => 'integer',
        'nullable' => 'integer',
        'default_enabled' => 'integer',
        'indexed' => 'integer',
        'sort_order' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    );

    public function virtualTable()
    {
        return $this->belongsTo(AppVirtualTable::class, 'virtual_table_id');
    }
}
