<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyToolMessage extends Model
{
    protected $fillable = array(
        'room_id',
        'sender_user_id',
        'peer_id',
        'type',
        'payload',
        'created_at',
    );

    public $timestamps = false;

    public function payloadArray()
    {
        if (!$this->payload) {
            return null;
        }
        $decoded = json_decode((string)$this->payload, true);
        return is_array($decoded) ? $decoded : null;
    }
}