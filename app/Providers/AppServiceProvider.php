<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        if (Request::is('admin/*')) {
            config([
                'session.cookie' => 'admin_session',
                'session.table' => 'admin_sessions',
            ]);
        } else {
            config([
                'session.cookie' => 'pelanggan_session',
                'session.table' => 'pelanggan_sessions',
            ]);
        }
    }
}
