<?php

namespace App\Providers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        if (config('services.plausible.token') && config('services.plausible.domain')) {
            $this->app->singleton('plausible.client', function () {
                return new Client([
                    'base_uri' => config('services.plausible.domain'),
                    'timeout' => 5, // Seconds
                    'http_errors' => false,
                ]);
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        //
        Pulse::user(fn ($user) => [
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

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $email = urlencode($user->getEmailForPasswordReset());

            return url("/reset-password/{$token}?email={$email}");
        });
    }
}
