<?php

namespace App\Http\Resources\Pulse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Recorders\CacheInteractions as CacheInteractionsRecorder;

class CacheResource extends PulseResource {
    public function toArray(Request $request): array {
        [$cacheInteractions, $allTime, $allRunAt] = $this->remember(
            fn () => with(
                $this->aggregateTotal(['cache_hit', 'cache_miss'], 'count'),
                fn ($results) => (object) [
                    'hits' => $results['cache_hit'] ?? 0,
                    'misses' => $results['cache_miss'] ?? 0,
                ]
            ),
            'all'
        );

        [$cacheKeyInteractions, $keyTime, $keyRunAt] = $this->remember(
            fn () => $this->aggregateTypes(['cache_hit', 'cache_miss'], 'count')
                ->map(function ($row) {
                    return (object) [
                        'key' => $row->key,
                        'hits' => $row->cache_hit ?? 0,
                        'misses' => $row->cache_miss ?? 0,
                    ];
                }),
            'keys'
        );

        return [
            'cache' => [
                'allTime' => $allTime,
                'allRunAt' => $allRunAt,
                'allCacheInteractions' => $cacheInteractions,
                'keyTime' => $keyTime,
                'keyRunAt' => $keyRunAt,
                'cacheKeyInteractions' => $cacheKeyInteractions,
                $this->mergeWhen(
                    config('pulse-api.include_config'),
                    ['config' => Config::get('pulse.recorders.' . CacheInteractionsRecorder::class)]
                ),
            ],
        ];
    }
}
