<?php

namespace App\Http\Resources\Pulse;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Recorders\Exceptions as ExceptionsRecorder;
use Livewire\Attributes\Url;

class ExceptionsResource extends PulseResource {
    #[Url(as: 'exceptions')]
    public string $orderBy = 'count';

    public function toArray(Request $request): array {
        [$exceptions, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'exception',
                ['max', 'count'],
                match ($this->orderBy) {
                    'latest' => 'max',
                    default => 'count'
                },
            )->map(function ($row) {
                [$class, $location] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'class' => $class,
                    'location' => $location,
                    'latest' => CarbonImmutable::createFromTimestamp($row->max),
                    'count' => $row->count,
                ];
            }),
            $this->orderBy
        );

        return [
            'exceptions' => [
                'time' => $time,
                'runAt' => $runAt,
                'exceptions' => $exceptions,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . ExceptionsRecorder::class)]
                ),
            ],
        ];
    }
}
