<?php

use App\Http\Middleware\InjectAdminApiToken;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            InjectAdminApiToken::class,
            SecurityHeadersMiddleware::class,
        ]);
        $middleware->replace(VerifyCsrfToken::class, VerifyCsrfToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\ViewServiceProvider::class,
    ])->create();
