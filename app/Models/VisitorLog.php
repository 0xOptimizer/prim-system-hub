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
        $now = now();
        $data = [];

        switch ($period) {
            case 'hourly':
                $end = $now->copy()->addHour()->startOfHour();
                $start = $end->copy()->subHours(6);
                $range = [];

                for ($t = $start->copy(); $t < $end; $t->addHour()) {
                    $range[$t->format('H:00')] = 0;
                }

                $rows = self::selectRaw('HOUR(created_at) as h, COUNT(DISTINCT ip_address) as count')
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('h')
                    ->pluck('count', 'h');

                foreach ($range as $hour => $val) {
                    $h = intval(substr($hour, 0, 2));
                    $range[$hour] = $rows[$h] ?? 0;
                }

                foreach ($range as $label => $count) {
                    $data[] = ['label' => $label, 'count' => $count];
                }
                break;

            case 'daily':
                $start = $now->copy()->startOfDay();
                $range = [];

                for ($h = 0; $h < 24; $h++) {
                    $label = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                    $range[$label] = 0;
                }

                $rows = self::selectRaw('HOUR(created_at) as h, COUNT(DISTINCT ip_address) as count')
                    ->whereBetween('created_at', [$start, $start->copy()->addDay()])
                    ->groupBy('h')
                    ->pluck('count', 'h');

                foreach ($range as $label => $val) {
                    $h = intval(substr($label, 0, 2));
                    $range[$label] = $rows[$h] ?? 0;
                }

                foreach ($range as $label => $count) {
                    $data[] = ['label' => $label, 'count' => $count];
                }
                break;

            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end = $start->copy()->addDays(7);
                $range = [];

                for ($d = $start->copy(); $d < $end; $d->addDay()) {
                    $label = $d->format('Y-m-d');
                    $range[$label] = 0;
                }

                $rows = self::selectRaw('DATE(created_at) as d, COUNT(DISTINCT ip_address) as count')
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('d')
                    ->pluck('count', 'd');

                foreach ($range as $label => $val) {
                    $range[$label] = $rows[$label] ?? 0;
                }

                foreach ($range as $label => $count) {
                    $data[] = ['label' => $label, 'count' => $count];
                }
                break;

            case 'monthly':
                $start = $now->copy()->startOfMonth()->subMonths(11);
                $range = [];

                for ($m = 0; $m < 12; $m++) {
                    $label = $start->copy()->addMonths($m)->format('Y-m');
                    $range[$label] = 0;
                }

                $rows = self::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as m, COUNT(DISTINCT ip_address) as count')
                    ->whereBetween('created_at', [$start, $now])
                    ->groupBy('m')
                    ->pluck('count', 'm');

                foreach ($range as $label => $val) {
                    $range[$label] = $rows[$label] ?? 0;
                }

                foreach ($range as $label => $count) {
                    $data[] = ['label' => $label, 'count' => $count];
                }
                break;
        }

        return collect($data);
    }
}