<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Recorders\Concerns\Thresholds;
use Laravel\Pulse\Recorders\SlowOutgoingRequests as SlowOutgoingRequestsRecorder;
use Livewire\Attributes\Url;

class SlowOutgoingRequestsResource extends PulseResource {
    use Thresholds;

    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-outgoing-requests')]
    public string $orderBy = 'slowest';

    public function toArray(Request $request): array {
        [$slowOutgoingRequests, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_outgoing_request',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$method, $uri] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'method' => $method,
                    'uri' => $uri,
                    'slowest' => $row->max,
                    'count' => $row->count,
                    'threshold' => $this->threshold($uri, SlowOutgoingRequestsRecorder::class),
                ];
            }),
            $this->orderBy,
        );

        return [
            'slow_outgoing_requests' => [
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . SlowOutgoingRequestsRecorder::class)]
                ),
                'slowOutgoingRequests' => $slowOutgoingRequests,
            ],
        ];
    }
}
