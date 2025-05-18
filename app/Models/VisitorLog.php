<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    public $table = 'visitor_logs';
    public $timestamps = true;

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

    public static function groupByPeriod(string $period)
    {
        switch ($period) {
            case 'hourly':
                return self::selectRaw('HOUR(created_at) as label, COUNT(*) as count')
                    ->whereDate('created_at', now()->toDateString())
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();
            case 'daily':
                return self::selectRaw('DATE(created_at) as label, COUNT(*) as count')
                    ->whereBetween('created_at', [now()->subDays(30), now()])
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();
            case 'monthly':
                return self::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as label, COUNT(*) as count')
                    ->whereBetween('created_at', [now()->subMonths(12), now()])
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();
            default:
                return collect();
        }
    }
}