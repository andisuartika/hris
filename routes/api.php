<?php

use App\Http\Controllers\Api\ApiAttendanceController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiFaceController;
use App\Http\Controllers\Api\ApiLeaveController;
use App\Http\Controllers\Api\ApiScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        // Maks 5 percobaan login per menit per IP (cegah brute force ke SSO)
        Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:5,1');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/refresh', [ApiAuthController::class, 'refresh']);
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::get('/profile', [ApiAuthController::class, 'profile']);
            Route::post('/profile', [ApiAuthController::class, 'updateProfile']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('attendance')->group(function () {

        Route::post('/checkin', [ApiAttendanceController::class, 'checkin']);
        Route::post('/checkout', [ApiAttendanceController::class, 'checkout']);
        Route::get('/today', [ApiAttendanceController::class, 'today']);
        Route::get('/history', [ApiAttendanceController::class, 'history']);
        Route::get('/summary', [ApiAttendanceController::class, 'summary']);
        Route::get('/settings', [ApiAttendanceController::class, 'settings']);
        Route::post('/corrections', [ApiAttendanceController::class, 'storeCorrection']);
        Route::get('/corrections', [ApiAttendanceController::class, 'corrections']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/schedule', [ApiScheduleController::class, 'schedule']);
        Route::get('/holidays', [ApiScheduleController::class, 'holidays']);
    });

    Route::middleware('auth:sanctum')->prefix('leaves')->group(function () {
        Route::get('/types', [ApiLeaveController::class, 'types']);
        Route::get('/balance', [ApiLeaveController::class, 'balance']);
        Route::get('/', [ApiLeaveController::class, 'index']);
        Route::post('/', [ApiLeaveController::class, 'store']);
        Route::post('/{id}/cancel', [ApiLeaveController::class, 'cancel']);
    });

    Route::middleware('auth:sanctum')->prefix('face')->group(function () {
        Route::post('/register', [ApiFaceController::class, 'register']);
        Route::get('/template', [ApiFaceController::class, 'template']);
        Route::get('/sync', [ApiFaceController::class, 'sync']);
    });

    Route::get('/test', function () {
        return response()->json([
            'message' => 'API v1 is working!'
        ]);
    });
});
