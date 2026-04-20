<?php

namespace App\Providers\Api;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void {
        // guest token endpoint
        RateLimiter::for('guest-token', function ($_) {
            return Limit::perMinute(10)->by(request()->ip());
        });

        // playback progress endpoint
        RateLimiter::for('playback-progress', function ($_) {
            // If expecting a request every 5 seconds (12 per minute per stream)
            // this allows for 8 simultaneous streams from a single ip
            return Limit::perMinute(96)->by(request()->ip());
        });

        RateLimiter::for('lrclib', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
