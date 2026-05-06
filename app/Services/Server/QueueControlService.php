<?php

namespace App\Services\Server;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class QueueControlService {
    public function restart(): void {
        try {
            if (config('queue.default') === 'redis' && $this->horizonIsRunning()) {
                Artisan::call('horizon:terminate');
            }
        } catch (\Throwable $th) {
            Log::error("Unable to restart Horizon", ["error" => $th->getMessage()]);
        }
    }

    private function horizonIsRunning(): bool {
        return count(app(MasterSupervisorRepository::class)->all()) > 0;
    }
}
