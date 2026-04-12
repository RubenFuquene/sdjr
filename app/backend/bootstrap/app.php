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
        // Mitigación DDOS: Habilita confianza en proxy inverso Docker/Nginx.
        // Registra el middleware TrustProxies personalizado
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // API middleware group
        $middleware->group('api', [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\AuditMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Personalización de respuesta 429 Too Many Requests
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            $maxAttempts = $e->getHeaders()['X-RateLimit-Limit'] ?? null;
            $remaining = $e->getHeaders()['X-RateLimit-Remaining'] ?? null;
            $reset = $e->getHeaders()['X-RateLimit-Reset'] ?? null;

            $response = [
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'code' => 429,
            ];

            return response()->json($response, 429)
                ->header('Retry-After', $retryAfter)
                ->header('X-RateLimit-Limit', $maxAttempts)
                ->header('X-RateLimit-Remaining', $remaining)
                ->header('X-RateLimit-Reset', $reset);
        });
    })->create();
