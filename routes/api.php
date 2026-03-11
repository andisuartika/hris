<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Routing\Route;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        // Route::prefix('attendance')->group(function () {
        //     Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        //     Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        //     Route::get('/today', [AttendanceController::class, 'today']);
        // });
    });
});
