<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAllowedUser extends Model
{
    protected $table = 'application_allowed_users';

    protected $fillable = array('application_id', 'user_id');
}
