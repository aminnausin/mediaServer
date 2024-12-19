<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Pulse\Recorders\Queues as QueuesRecorder;

class QueueResource extends PulseResource {
    public function toArray(Request $request): array {
        [$queues, $time, $runAt] = $this->remember(fn () => $this->graph(
            ['queued', 'processing', 'processed', 'released', 'failed'],
            'count',
        ));

        return [
            'queues' => [
                'queues' => $queues,
                'showConnection' => $queues->keys()->map(fn ($queue) => Str::before($queue, ':'))->unique()->count() > 1,
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . QueuesRecorder::class)]
                ),
            ],
        ];
    }
}
