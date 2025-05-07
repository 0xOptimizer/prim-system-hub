<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'created_by'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_user', 'room_uuid', 'user_uuid', 'uuid', 'uuid');
    }
}