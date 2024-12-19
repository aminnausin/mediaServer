<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Recorders\SlowRequests;
use Laravel\Pulse\Recorders\UserJobs;
use Laravel\Pulse\Recorders\UserRequests;
use Livewire\Attributes\Url;

class UsageResource extends PulseResource {
    #[Url]
    public string $usage = 'requests';

    public function toArray(Request $request): array {
        $type = $this->type ?? $this->usage;

        [$userRequestCounts, $time, $runAt] = $this->remember(
            function () use ($type) {
                $counts = $this->aggregate(
                    match ($type) {
                        'requests' => 'user_request',
                        'slow_requests' => 'slow_user_request',
                        'jobs' => 'user_job',
                        default => null,
                    },
                    'count',
                    limit: 10,
                );

                $users = Pulse::resolveUsers($counts->pluck('key'));

                return $counts->map(fn ($row) => (object) [
                    'key' => $row->key,
                    'user' => $users->find($row->key),
                    'count' => (int) $row->count,
                ]);
            },
            $type
        );

        return [
            'usage' => [
                'time' => $time,
                'runAt' => $runAt,
                $this->mergeWhen(config('pulse-api.include_config'), [
                    'userRequestsConfig' => Config::get('pulse.recorders.' . UserRequests::class),
                    'slowRequestsConfig' => Config::get('pulse.recorders.' . SlowRequests::class),
                    'jobsConfig' => Config::get('pulse.recorders.' . UserJobs::class),
                ]),
                'userRequestCounts' => $userRequestCounts,
            ],
        ];
    }
}
