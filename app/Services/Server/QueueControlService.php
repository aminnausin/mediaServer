<?php

namespace App\Services\Server;

use Illuminate\Support\Facades\Artisan;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class QueueControlService {
    public function restart(): void {
        if (config('queue.default') === 'redis' && $this->horizonIsRunning()) {
            Artisan::call('horizon:terminate');
        }
    }

    private function horizonIsRunning(): bool {
        return count(app(MasterSupervisorRepository::class)->all()) > 0;
    }
}
