<?php

// Middleware
use App\Http\Middleware\ClientToken;
use App\Http\Middleware\JwtToken;

// Custom Error Handler
use App\Exceptions\CustomErrorHandler;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
       #apiPrefix: '',#这个会排除掉Api自带前目录
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('auth.client', [ClientToken::class]);
        $middleware->appendToGroup('auth.client.jwt', [
            ClientToken::class,
            JwtToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Exception $e) {
            return CustomErrorHandler::handler($e);
        });
    })->create();
