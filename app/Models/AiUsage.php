<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiUsage extends Model
{
    protected $fillable = ['ai_response_id', 'ip', 'user_agent', 'action', 'created_by'];

    public $table = 'ai_usages';

    public function response()
    {
        return $this->belongsTo(AiResponse::class, 'ai_response_id');
    }
}