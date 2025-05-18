<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\RouteMatched;
use App\Models\VisitorLog;

class VisitorLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(RouteMatched::class, function ($event) {
            $request = $event->request;

            try {
                VisitorLog::create([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->headers->get('referer'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'query_string' => $request->getQueryString(),
                    'session_id' => session()->getId(),
                    'languages' => implode(',', $request->getLanguages()),
                    'platform' => php_uname('s'),
                    'device' => $request->header('sec-ch-ua-platform'),
                    'browser' => $request->header('sec-ch-ua'),
                    'headers' => json_encode($request->headers->all())
                ]);
            } catch (\Throwable $e) {
                \Log::error('Visitor log error: ' . $e->getMessage());
            }
        });
    }
}