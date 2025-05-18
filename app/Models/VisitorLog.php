<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    public $table = 'visitor_logs';
    
    protected $fillable = [
        'ip_address',
        'user_agent',
        'referer',
        'url',
        'method',
        'query_string',
        'session_id',
        'languages',
        'platform',
        'device',
        'browser',
        'headers'
    ];

    public $timestamps = true;
}