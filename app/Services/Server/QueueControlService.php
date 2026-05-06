<?php

namespace App\Services\Server;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class QueueControlService {
    public function restart(): void {
        try {
            if (config('queue.default') === 'redis' && $this->horizonIsRunning()) {
                dispatch(function () {
                    Artisan::call('horizon:terminate');
                })->onQueue('high');
            }
        } catch (\Throwable $th) {
            Log::error("Unable to restart Horizon", ["error" => $th->getMessage()]);
            throw $th;
        }
    }

    private function horizonIsRunning(): bool {
        return count(app(MasterSupervisorRepository::class)->all()) > 0;
    }
}
