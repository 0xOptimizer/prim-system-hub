<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomChat extends Model
{
    use SoftDeletes;

    protected $table = 'room_chat';

    protected $fillable = [
        'room_uuid',
        'user_uuid',
        'chat',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_uuid', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}