<?php

use App\Http\Middleware\AppendCompanyToJson;
use App\Http\Middleware\EnsureCompanySelected;
use App\Http\Middleware\EnsureOperationSelected;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', [
            EnsureFrontendRequestsAreStateful::class,
            AppendCompanyToJson::class,
        ]);
        $middleware->alias([
            'company.selected' => EnsureCompanySelected::class,
            'operation.selected' => EnsureOperationSelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
