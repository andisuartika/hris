<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DepartementController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\HolidayController;
use App\Http\Controllers\Web\OfficeLocationController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


// ==========================================
// ROUTE GUEST (Hanya bisa diakses jika BELUM login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// ROUTE AUTH (Hanya bisa diakses jika SUDAH login)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //DASHBOARD
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    });

    // Route CRUD Kepegawaian
    Route::resource('employees', EmployeeController::class);

    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('companies', CompanyController::class);
        Route::resource('departments', DepartementController::class);
        Route::resource('positions', PositionController::class);
        Route::resource('office-locations', OfficeLocationController::class);
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('work-schedules', WorkScheduleController::class);
        Route::resource('holidays', HolidayController::class);
        Route::post('/holidays/generate/{year}', [HolidayController::class, 'generate']);
    });
});
