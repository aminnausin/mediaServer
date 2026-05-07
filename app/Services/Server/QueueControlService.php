<?php

namespace App\Services\Server;

use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

class QueueControlService {
    public function restart(): void {
        try {
            if (config('queue.default') === 'redis' && $this->horizonIsRunning()) {
                $php = PHP_BINARY; // uses the same PHP binary the app is running on
                $artisan = base_path('artisan');

                exec("$php $artisan horizon:terminate > /dev/null 2>&1 &");
            }
        } catch (\Throwable $th) {
            Log::error('Unable to restart Horizon', ['error' => $th->getMessage()]);
            throw $th;
        }
    }

    private function horizonIsRunning(): bool {
        return count(app(MasterSupervisorRepository::class)->all()) > 0;
    }
}
