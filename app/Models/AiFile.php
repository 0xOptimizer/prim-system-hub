<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiFile extends Model
{
    protected $fillable = ['ai_response_id', 'filename', 'path', 'type'];

    public $table = 'ai_files';

    public function response()
    {
        return $this->belongsTo(AiResponse::class, 'ai_response_id');
    }
}
