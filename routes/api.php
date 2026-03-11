<?php

use App\Http\Controllers\Api\ApiAttendanceController;
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

        Route::post('/checkin', [ApiAttendanceController::class, 'checkin']);
        Route::post('/checkout', [ApiAttendanceController::class, 'checkout']);
        Route::get('/today', [ApiAttendanceController::class, 'today']);
        Route::get('/history', [ApiAttendanceController::class, 'history']);
    });

    Route::get('/test', function () {
        return response()->json([
            'message' => 'API v1 is working!'
        ]);
    });
});
