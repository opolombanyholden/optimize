<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'password.current' => \App\Http\Middleware\EnsurePasswordIsCurrent::class,
        ]);
        // Force le changement de mot de passe sur toutes les requêtes web authentifiées
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePasswordIsCurrent::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
