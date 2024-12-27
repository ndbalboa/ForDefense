<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

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
        // Define a rate limiter for login attempts
        RateLimiter::for('login', function () {
            return Limit::perMinute(5)->by(request()->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in 30 seconds.'
                ], 429);
            });
        });
    }
}
