<?php

namespace App\Providers;

use App\Services\Server\ServerConfigService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ServerConfigServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(ServerConfigService $config): void {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            $env = app()->environment();

            config([
                "horizon.environments.{$env}.supervisor-default.maxProcesses" => $config->get('max_scan_workers', 10),
                "horizon.environments.{$env}.supervisor-high.maxProcesses" => $config->get('max_event_workers', 5),
            ]);
        } catch (\Exception $e) {
            Log::error('Unable to load worker limits from DB server config.', ['error' => $e->getMessage()]);
        }
    }
}
