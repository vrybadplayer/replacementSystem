<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'api.auth' => \App\Http\Middleware\ApiAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 403 Forbidden error - redirect to login
        $exceptions->render(function (Throwable $e, $request) {
            // Prevent redirect loop - don't redirect if already on login page
            if ($request->path() === 'login') {
                return;
            }
            
            if ($e instanceof HttpException && $e->getStatusCode() === 403) {
                return redirect()->route('login')->with('error', 'You do not have permission to access this resource.');
            }
            
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return redirect()->route('login')->with('error', 'You do not have permission to access this resource.');
            }
        });
    })->create();
