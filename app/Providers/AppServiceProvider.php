<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        //
        Pulse::user(fn($user) => [
            'name' => $user->name,
            'extra' => $user->email,
        ]);

        Gate::define('viewPulse', function (?User $user) {
            return $user?->id == 1;
        });

        LogViewer::auth(function ($request) {
            return $request->user()
                && in_array($request->user()->id, [
                    1,
                ]);
        });
    }
}
