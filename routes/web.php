<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
    });
});
