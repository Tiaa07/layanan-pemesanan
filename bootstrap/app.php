<?php

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://layanan-pemesanan.test/problem/validasi-gagal',
                'title' => 'Data permintaan tidak valid',
                'status' => 422,
                'detail' => 'Satu atau lebih field tidak memenuhi kontrak.',
                'instance' => '/' . $request->path(),
                'errors' => $exception->errors(),
            ], 422, [
                'Content-Type' => 'application/problem+json',
            ]);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'type' => 'https://layanan-pemesanan.test/problem/tidak-ditemukan',
                'title' => 'Pesanan tidak ditemukan',
                'status' => 404,
                'detail' => 'Pesanan dengan kode tersebut tidak ditemukan.',
                'instance' => '/' . $request->path(),
            ], 404, [
                'Content-Type' => 'application/problem+json',
            ]);
        });
    })->create();
