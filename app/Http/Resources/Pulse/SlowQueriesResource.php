<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Recorders\Concerns\Thresholds;
use Laravel\Pulse\Recorders\SlowQueries as SlowQueriesRecorder;
use Livewire\Attributes\Url;

class SlowQueriesResource extends PulseResource {
    use Thresholds;

    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-queries')]
    public string $orderBy = 'slowest';

    /**
     * Indicates that SQL highlighting should be disabled.
     */
    public bool $withoutHighlighting = false;

    /**
     * Indicates that SQL highlighting should be disabled.
     *
     * @deprecated
     */
    public bool $disableHighlighting = false;

    /**
     * Determine if the view should highlight SQL queries.
     */
    protected function wantsHighlighting(): bool {
        return ! ($this->withoutHighlighting || $this->disableHighlighting);
    }

    public function toArray(Request $request): array {
        [$slowQueries, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_query',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(function ($row) {
                [$sql, $location] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);

                return (object) [
                    'sql' => $sql,
                    'location' => $location,
                    'slowest' => $row->max,
                    'count' => $row->count,
                    'threshold' => $this->threshold($sql, SlowQueriesRecorder::class),
                ];
            }),
            $this->orderBy,
        );

        return [
            'slow_queries' => [
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . SlowQueriesRecorder::class)]
                ),
                'slowQueries' => $slowQueries,
            ],
        ];
    }
}
