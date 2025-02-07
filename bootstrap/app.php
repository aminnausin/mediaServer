<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $middleware->trustHosts(at: [env('APP_HOST', 'app.test')]);
        $middleware->statefulApi();
        $middleware->trustProxies(at: [
            '192.168.1.1',
            '127.0.0.1',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\UserLastActive::class,
        ]);
        $middleware->api(prepend: []);
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
