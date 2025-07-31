<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class SeparateSessionByGuard
{
    public function handle($request, Closure $next)
    {
        if ($request->is('admin/*')) {
            config([
                'session.cookie' => Str::slug(config('app.name'), '_') . '_admin_session',
                'session.table' => 'admin_sessions',
            ]);
        } else {
            config([
                'session.cookie' => Str::slug(config('app.name'), '_') . '_user_session',
                'session.table' => 'sessions',
            ]);
        }

        return $next($request);
    }
}
