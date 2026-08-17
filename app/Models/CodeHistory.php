<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeHistory extends Model
{
    protected $table = 'code_histories';
    
    protected $fillable = [
        'code_id',
        'content'
    ];
}