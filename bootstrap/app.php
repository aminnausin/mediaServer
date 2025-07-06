<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES') === '*' ? '*' : explode(',', env('TRUSTED_PROXIES', '192.168.1.1,127.0.0.1')),
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB
        );
        $middleware->statefulApi();
        $middleware->trustHosts(at: [
            '^localhost(:[0-9]+)?$',
            '^127\.0\.0\.1(:[0-9]+)?$',
            '^app\.test(:[0-9]+)?$',
            env('APP_HOST', 'app.test'),
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\UserLastActive::class,
        ]);
        $middleware->api(prepend: []);
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $_) {
        //
    })->create();
