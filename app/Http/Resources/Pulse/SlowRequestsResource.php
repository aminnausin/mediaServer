<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Recorders\Concerns\Thresholds;
use Laravel\Pulse\Recorders\SlowRequests as SlowRequestsRecorder;
use Livewire\Attributes\Url;

class SlowRequestsResource extends PulseResource {
    use Thresholds;

    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-requests')]
    public string $orderBy = 'slowest';

    public function toArray(Request $request): array {
        [$slowRequests, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_request',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$method, $uri, $action] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'uri' => $uri,
                    'method' => $method,
                    'action' => $action,
                    'count' => $row->count,
                    'slowest' => $row->max,
                    'threshold' => $this->threshold($uri, SlowRequestsRecorder::class),
                ];
            }),
            $this->orderBy,
        );

        return [
            'slow_requests' => [
                'time' => $time,
                'runAt' => $runAt,
                'slowRequests' => $slowRequests,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    [
                        'config' => [
                            'threshold' => Config::get('pulse.recorders.' . SlowRequestsRecorder::class . '.threshold'),
                            'sample_rate' => Config::get('pulse.recorders.' . SlowRequestsRecorder::class . '.sample_rate'),
                        ],
                    ]
                ),
            ],
        ];
    }
}
