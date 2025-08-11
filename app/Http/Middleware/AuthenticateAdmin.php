<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateAdmin extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = ['admin'];
        return parent::handle($request, $next, ...$guards);
    }

    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            $next = urlencode($request->fullUrl());
            return route('admin.login') . '?next=' . $next;
        }

        return null;
    }
}
