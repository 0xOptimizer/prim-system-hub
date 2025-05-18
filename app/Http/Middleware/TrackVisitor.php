<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VisitorLog;

use Illuminate\Support\Facades\Log;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        try {
            VisitorLog::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_string' => $request->getQueryString(),
                'session_id' => $request->session()->getId(),
                'languages' => implode(',', $request->getLanguages()),
                'platform' => php_uname('s'),
                'device' => $request->header('sec-ch-ua-platform'),
                'browser' => $request->header('sec-ch-ua'),
                'headers' => json_encode($request->headers->all())
            ]);
        } catch (\Throwable $e) {
            Log::error('Visitor log failed: ' . $e->getMessage());
        }


        return $next($request);
    }
}