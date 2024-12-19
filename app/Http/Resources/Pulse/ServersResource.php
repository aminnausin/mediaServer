<?php

namespace App\Http\Resources\Pulse;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;

class ServersResource extends PulseResource {
    public int|string|null $ignoreAfter = null;

    public function toArray(Request $request): array {
        [$servers, $time, $runAt] = $this->remember(function () {
            $graphs = $this->graph(['cpu', 'memory'], 'avg');

            return $this->values('system')
                ->map(function ($system, $slug) use ($graphs) {
                    if ($this->ignoreSystem($system)) {
                        return null;
                    }

                    $values = json_decode($system->value, flags: JSON_THROW_ON_ERROR);

                    return (object) [
                        'name' => (string) $values->name,
                        'cpu_current' => (int) $values->cpu,
                        'cpu' => $graphs->get($slug)?->get('cpu') ?? collect(),
                        'memory_current' => (int) $values->memory_used,
                        'memory_total' => (int) $values->memory_total,
                        'memory' => $graphs->get($slug)?->get('memory') ?? collect(),
                        'storage' => collect($values->storage),
                        'updated_at' => $updatedAt = CarbonImmutable::createFromTimestamp($system->timestamp),
                        'recently_reported' => $updatedAt->isAfter(now()->subSeconds(30)),
                    ];
                })
                ->filter()
                ->sortBy('name');
        });

        return [
            'servers' => [
                'servers' => $servers,
                'time' => $time,
                'runAt' => $runAt,
            ],
        ];
    }

    protected function ignoreSystem(object $system): bool {
        if ($this->ignoreAfter === null) {
            return false;
        }

        $ignoreAfter = is_numeric($this->ignoreAfter)
            ? (int) $this->ignoreAfter
            : CarbonInterval::createFromDateString($this->ignoreAfter)->totalSeconds;

        return CarbonImmutable::createFromTimestamp($system->timestamp)->addSeconds($ignoreAfter)->isPast();
    }
}
