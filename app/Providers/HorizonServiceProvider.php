<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider {
    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void {
        Gate::define('viewHorizon', function (User $user) {
            return config('app.env') === 'demo' ? $user->email === config('demo.auth_email') : Gate::forUser($user)->allows('admin');
        });
    }
}
