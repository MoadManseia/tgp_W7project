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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {

        // Let Laravel handle non-API requests
        if (! $request->expectsJson()) {
            return null;
        }

        // ✅ Validation errors (422)
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        }

        // ✅ HTTP exceptions (401, 403, 404, etc.)
        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'success'   => false,
                'exception' => class_basename($e),
                'message'   => $e->getMessage(),
            ], $e->getStatusCode());
        }

        // ✅ Fallback (500)
        return response()->json([
            'success'   => false,
            'exception' => class_basename($e),
            'message'   => config('app.debug')
                ? $e->getMessage()
                : 'Internal server error',
        ], 500);
    });
    })->create();
