<?php

namespace App\Providers;

use App\Services\PathResolverService;
use Illuminate\Support\ServiceProvider;

class PathResolverServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(PathResolverService::class, function ($app) {
            return new PathResolverService;
        });
    }

    public function boot(): void {
        //
    }
}
