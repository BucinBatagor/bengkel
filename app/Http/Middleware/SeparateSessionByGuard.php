<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class SeparateSessionByGuard
{
    public function handle($request, Closure $next)
    {
        if ($request->is('admin/*')) {
            // Jika URL diawali 'admin/', berarti ini admin
            config([
                'session.cookie' => Str::slug(config('app.name'), '_') . '_admin_session',
                'session.table' => 'admin_sessions', // GUNAKAN TABEL INI untuk admin
            ]);
        } else {
            // Selain itu anggap sebagai pelanggan
            config([
                'session.cookie' => Str::slug(config('app.name'), '_') . '_user_session',
                'session.table' => 'sessions', // TABEL DEFAULT untuk pelanggan
            ]);
        }

        return $next($request);
    }
}
