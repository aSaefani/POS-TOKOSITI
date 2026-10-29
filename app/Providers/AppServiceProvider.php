<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Login: batas umum per IP (lapisan kedua di atas limiter per email+IP).
        RateLimiter::for('login', fn ($request) => Limit::perMinute(10)->by($request->ip()));

        // Checkout: mencegah spam request dari perangkat kasir yang rusak/bermasalah.
        RateLimiter::for('checkout', fn ($request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        Vite::prefetch(concurrency: 3);
    }
}
