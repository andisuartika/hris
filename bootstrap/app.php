<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        // Batas global 60 request/menit per user/IP untuk seluruh API
        $middleware->api(append: [
            'throttle:60,1',
        ]);
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua request ke /api/* selalu dijawab JSON, walau client lupa header Accept
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $envelope = function (string $message, int $status, string $code, array $errors = []) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data'    => null,
                'errors'  => $errors,
                'code'    => $code,
            ], $status);
        };

        $exceptions->renderable(function (AuthenticationException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Tidak terautentikasi. Silakan login.', 401, 'UNAUTHENTICATED');
            }
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Data tidak valid', 422, 'VALIDATION_ERROR', $e->errors());
            }
        });

        $exceptions->renderable(function (UnauthorizedException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Anda tidak memiliki akses untuk aksi ini', 403, 'FORBIDDEN');
            }
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Anda tidak memiliki akses untuk aksi ini', 403, 'FORBIDDEN');
            }
        });

        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Resource tidak ditemukan', 404, 'NOT_FOUND');
            }
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Metode HTTP tidak diizinkan', 405, 'METHOD_NOT_ALLOWED');
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope('Terlalu banyak permintaan. Coba lagi nanti.', 429, 'TOO_MANY_REQUESTS');
            }
        });

        $exceptions->renderable(function (Throwable $e, Request $request) use ($envelope) {
            if ($request->is('api/*')) {
                return $envelope(
                    config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server',
                    500,
                    'SERVER_ERROR'
                );
            }
        });
    })->create();
