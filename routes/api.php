<?php

use App\Http\Controllers\Api\ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/login', [ApiAuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::get('/profile', [ApiAuthController::class, 'profile']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('attendance')->group(function () {

        // Route::post('/checkin', [AttendanceController::class, 'checkin']);
        // Route::post('/checkout', [AttendanceController::class, 'checkout']);
        // Route::get('/today', [AttendanceController::class, 'today']);
        // Route::get('/history', [AttendanceController::class, 'history']);
    });

    Route::get('/test', function () {
        return response()->json([
            'message' => 'API v1 is working!'
        ]);
    });
});
