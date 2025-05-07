<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiResponse extends Model
{
    protected $fillable = ['uuid', 'prompt', 'language', 'codes', 'test_cases', 'created_by'];

    protected $casts = ['codes' => 'array'];

    public $table = 'ai_responses';

    public function files()
    {
        return $this->hasMany(AiFile::class);
    }

    public function usages()
    {
        return $this->hasMany(AiUsage::class);
    }
}