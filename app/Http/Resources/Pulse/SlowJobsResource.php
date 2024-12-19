<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Recorders\Concerns\Thresholds;
use Laravel\Pulse\Recorders\SlowJobs as SlowJobsRecorder;
use Livewire\Attributes\Url;

class SlowJobsResource extends PulseResource {
    use Thresholds;

    /**
     * Ordering.
     *
     * @var 'slowest'|'count'
     */
    #[Url(as: 'slow-jobs')]
    public string $orderBy = 'slowest';

    public function toArray(Request $request): array {
        [$slowJobs, $time, $runAt] = $this->remember(
            fn () => $this->aggregate(
                'slow_job',
                ['max', 'count'],
                match ($this->orderBy) {
                    'count' => 'count',
                    default => 'max',
                },
            )->map(fn ($row) => (object) [
                'job' => $row->key,
                'slowest' => $row->max,
                'count' => $row->count,
                'threshold' => $this->threshold($row->key, SlowJobsRecorder::class),
            ]),
            $this->orderBy,
        );

        return [
            'slow_jobs' => [
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . SlowJobsRecorder::class)]
                ),
                'slowJobs' => $slowJobs,
            ],
        ];
    }
}
