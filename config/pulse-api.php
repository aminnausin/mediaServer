<?php

use Laravel\Pulse\Http\Middleware\Authorize;

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

    'include_config' => env('PULSE_API_INCLUDE_CONFIG', fake()),

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

    'resources' => \Robertogallea\PulseApi\Services\PulseAPI::getDefaultResources()->merge([
        // Add your custom resources
    ]),

];
