<?php

use App\Http\Resources\Pulse\RequestsResource;
use App\Http\Resources\Pulse\ScheduleResource;
use Laravel\Pulse\Http\Middleware\Authorize;
use Robertogallea\PulseApi\Services\PulseAPI;

return [

    /*
    |--------------------------------------------------------------------------
    | Pulse Route Prefix
    |--------------------------------------------------------------------------
    |
    | This label defines the path prefix for the pulse api routes
    |
    */

    'route_prefix' => env('PULSE_API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Pulse Dashboard Path
    |--------------------------------------------------------------------------
    |
    | This label defines the sub-path for the pulse api dashboard route
    |
    */

    'path' => env('PULSE_API_PATH', 'pulse'),

    /*
    |--------------------------------------------------------------------------
    | Include config in resources
    |--------------------------------------------------------------------------
    |
    | If set to true, config for relevant recorders are included in the
    | resources response
    |
    */

    'include_config' => env('PULSE_API_INCLUDE_CONFIG', true),

    /*
    |--------------------------------------------------------------------------
    | Pulse Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Pulse api route, giving you the
    | chance to add your own middleware to this list or change any of the
    | existing middleware. Of course, reasonable defaults are provided.
    |
    */

    'middleware' => [
        'api',
        'auth:sanctum',
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Api Resources
    |--------------------------------------------------------------------------
    |
    | Define the collection including the resources shown in the dashboard api.
    | The default gets the resources from a static list. You are free to
    | add, remove, replace or totally redefine this list.
    |
    */

    'resources' => PulseAPI::getDefaultResources()->merge([
        // Add your custom resources
        'requests' => RequestsResource::class
        // 'schedule' => ScheduleResource::class,
        // PulseResourcesEnum::SERVERS->value => ServersResource::class,
        // PulseResourcesEnum::USAGE->value => UsageResource::class,
        // PulseResourcesEnum::QUEUES->value => QueueResource::class,
        // PulseResourcesEnum::CACHE->value => CacheResource::class,
        // PulseResourcesEnum::SLOW_QUERIES->value => SlowQueriesResource::class,
        // PulseResourcesEnum::EXCEPTIONS->value => ExceptionsResource::class,
        // PulseResourcesEnum::SLOW_REQUESTS->value => SlowRequestsResource::class,
        // PulseResourcesEnum::SLOW_JOBS->value => SlowJobsResource::class,
        // PulseResourcesEnum::SLOW_OUTGOING_REQUESTS->value => SlowOutgoingRequestsResource::class,
    ]),

];
