<?php

namespace App\Services;

use App\Http\Resources\Pulse\CacheResource;
use Illuminate\Support\Collection;
use Robertogallea\PulseApi\Enum\PulseResourcesEnum;
use App\Http\Resources\Pulse\ExceptionsResource;
use App\Http\Resources\Pulse\QueueResource;
use App\Http\Resources\Pulse\ServersResource;
use App\Http\Resources\Pulse\SlowJobsResource;
use App\Http\Resources\Pulse\SlowOutgoingRequestsResource;
use App\Http\Resources\Pulse\SlowQueriesResource;
use App\Http\Resources\Pulse\SlowRequestsResource;
use App\Http\Resources\Pulse\UsageResource;

class PulseAPI {
    public static function getDefaultResources(): Collection {
        return collect([
            PulseResourcesEnum::SERVERS->value => ServersResource::class,
            PulseResourcesEnum::USAGE->value => UsageResource::class,
            PulseResourcesEnum::QUEUES->value => QueueResource::class,
            PulseResourcesEnum::CACHE->value => CacheResource::class,
            PulseResourcesEnum::SLOW_QUERIES->value => SlowQueriesResource::class,
            PulseResourcesEnum::EXCEPTIONS->value => ExceptionsResource::class,
            PulseResourcesEnum::SLOW_REQUESTS->value => SlowRequestsResource::class,
            PulseResourcesEnum::SLOW_JOBS->value => SlowJobsResource::class,
            PulseResourcesEnum::SLOW_OUTGOING_REQUESTS->value => SlowOutgoingRequestsResource::class,
        ]);
    }
}
