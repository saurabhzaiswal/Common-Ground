<?php

use App\Http\Middleware\ApiResponseEnvelope;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [ApiResponseEnvelope::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if (! $request->is('api/*') || ! $response instanceof JsonResponse) {
                return $response;
            }

            $payload = $response->getData(true);
            $payload = is_array($payload) ? $payload : ['data' => $payload];
            $payload['error'] = true;
            $payload['message'] ??= 'Something went wrong. Please try again later.';

            $response->setData($payload);

            return $response;
        });
    })->create();
