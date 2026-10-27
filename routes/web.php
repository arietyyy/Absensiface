<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\AttendanceReportController;

// Login dan logout tidak dibatasi oleh middleware role.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Halaman absensi untuk Admin dan Karyawan.
Route::middleware(['auth', 'role:admin,karyawan'])->group(function () {
    Route::get('/absensi', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/absensi/check-in', [AttendanceController::class, 'checkIn'])
        ->name('attendance.check-in');

    Route::post('/absensi/check-out', [AttendanceController::class, 'checkOut'])
        ->name('attendance.check-out');

    Route::get('/dashboard-karyawan', [EmployeeDashboardController::class, 'index'])
        ->middleware('role:karyawan')
        ->name('employee.dashboard');
});

// Halaman khusus Admin.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/karyawan', [EmployeeController::class, 'index'])
        ->name('employees.index');

    Route::post('/karyawan', [EmployeeController::class, 'store'])
        ->name('employees.store');

    Route::get('/karyawan/{employee}/daftar-wajah', [EmployeeController::class, 'registerFace'])
        ->name('employees.face');

    Route::post('/karyawan/{employee}/daftar-wajah', [EmployeeController::class, 'saveFace'])
        ->name('employees.face.save');
    Route::get('/laporan-absensi', [AttendanceReportController::class, 'index'])
    ->name('reports.attendance');
});

