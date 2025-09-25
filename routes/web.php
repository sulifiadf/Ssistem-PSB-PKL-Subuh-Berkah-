<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\Admin\LapakController;
use App\Http\Controllers\Admin\PerpindahanController;
use App\Http\Controllers\Admin\WaitingListController;
use App\Http\Controllers\Admin\AdminKehadiranController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\RombongController;
use App\Http\Controllers\User\KehadiranController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LupaPasswordController;

use App\Http\Controllers\HelpersController;


Route::get('/', function () {
    return view('welcome');
});

// =================== CRON JOB URL ===================
Route::get('/cron/kehadiran-reminder', function () {
    Artisan::call('kehadiran:reminder');
    return 'Reminder executed';
});

Route::get('/cron/auto-set-libur', function () {
    Artisan::call('kehadiran:auto-libur');
    return 'Auto set libur executed';
});

Route::get('/helpers/testWa', [HelpersController::class, 'testWa'] );

// =================== ADMIN ===================
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard → hanya bisa diakses oleh admin yang login
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('auth.custom:admin')
        ->name('dashboard');

    Route::post('/perpindahan', [PerpindahanController::class, 'store'])->name('perpindahan.store');

    // Register
    Route::get('/register', [RegisterController::class, 'createAdmin'])->name('register.create');
    Route::post('/register', [RegisterController::class, 'storeAdmin'])->name('register.store');

    // Login
    Route::get('/login', function () {
        return view('admin.login');
    })->name('login');
    Route::post('/login', [LoginController::class, 'authenticateAdmin'])->name('login.submit');

    // Waiting List → hanya admin
        //approve user
    Route::middleware('auth.custom:admin')->group(function () {
        Route::get('/waitinglist', [WaitingListController::class, 'waitinglist'])->name('waitinglist');
        Route::post('/approve/{user}', [WaitingListController::class, 'approveUser'])->name('approve');
        Route::post('/reject/{user}', [WaitingListController::class, 'rejectUser'])->name('reject');

        //approve anggota
        Route::get('/anggota/waitinglist', [WaitingListController::class, 'anggota'])->name('anggota.waitinglist');
        Route::post('/anggota/{id}/approve', [WaitingListController::class, 'approveAnggota'])->name('anggota.approve');
        Route::post('/anggota/{id}/reject', [WaitingListController::class, 'rejectAnggota'])->name('anggota.reject');

        
    // CRUD User → khusus user
    Route::resource('/user', UserController::class)->except(['show']);

    });


    //lapak
    Route::resource('/lapak', LapakController::class)->except(['show']);
    
    //keuangan
    Route::resource('/keuangan', KeuanganController::class)->except(['show', 'create']);

    Route::get('/kehadiran/status', [AdminKehadiranController::class, 'getStatusKehadiran'])->name('kehadiran.status');
    Route::get('/kehadiran/detail/{userId}', [AdminKehadiranController::class, 'getDetailKehadiran'])->name('kehadiran.detail');
    
});

// =================== USER ===================
Route::prefix('user')->name('user.')->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'createUser'])->name('register.create');
    Route::post('/register', [RegisterController::class, 'storeUser'])->name('register.store');

    // Login
    Route::get('/login', function () {
        return view('user.login');
    })->name('login');
    Route::post('/login', [LoginController::class, 'authenticateUser'])->name('login.submit');

    // Routes yang membutuhkan autentikasi user
    Route::middleware('auth.custom:user')->group(function () {
        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // =================== ROUTES YANG DIPERBAIKI ===================
        
        // Routes untuk kehadiran - PERBAIKAN
        Route::post('/kehadiran/konfirmasi', [KehadiranController::class, 'konfirmasi'])
            ->name('kehadiran.konfirmasi');
            
        Route::get('/api/kehadiran/status', [KehadiranController::class, 'getStatusKehadiran'])
            ->name('api.kehadiran.status');

        // Routes untuk rombong - PERBAIKAN
        Route::post('/rombong/store', [RombongController::class, 'store'])
            ->name('rombong.store');

        // =================== PROFILE ROUTES ===================
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Image Management (AJAX Routes)
        Route::delete('/profile/delete-image', [ProfileController::class, 'deleteProfileImage'])->name('profile.delete-image');
        Route::delete('/profile/delete-neighbor-image', [ProfileController::class, 'deleteNeighborImage'])->name('profile.delete-neighbor-image');

        // Location Management (AJAX Routes)
        Route::get('/profile/location', [ProfileController::class, 'getLocation'])->name('profile.location');
        Route::post('/profile/update-location', [ProfileController::class, 'updateLocation'])->name('profile.update-location');

        // Additional Profile Routes (Optional)
        Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
        Route::post('/profile/upload-bulk-images', [ProfileController::class, 'uploadBulkImages'])->name('profile.upload-bulk-images');

        // =================== KEHADIRAN (User) - ROUTES LAMA (backup) ===================
        Route::get('/kehadiran/status', [KehadiranController::class, 'getStatusKehadiran'])->name('kehadiran.status');
        Route::get('/kehadiran/detail/{userId}', [KehadiranController::class, 'getDetailKehadiran'])->name('kehadiran.detail');
        
        // Route konfirmasi lama (backup) - bisa dihapus jika yang baru sudah berfungsi
        Route::post('/kehadiran/konfirmasi-lama', [KehadiranController::class, 'konfirmasiViaLogin'])
            ->name('kehadiran.konfirmasi-lama');
    });

    
});

// =================== LOGOUT ROUTE ===================
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// lupa password 
Route::get('/lupa-password', [LupaPasswordController::class, 'lupaPassword'])
    ->name('password.request');

Route::post('/lupa-password', [LupaPasswordController::class, 'kirimLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [LupaPasswordController::class, 'resetForm'])
    ->name('password.reset');

Route::post('/reset-password', [LupaPasswordController::class, 'resetPassword'])
    ->name('password.update');


//konfirmasi kehadiran via wa
Route::get('/konfirmasi-kehadiran/{userId}/{token}', [KehadiranController::class, 'showKonfirmasiForm'])
    ->name('kehadiran.form');
Route::post('/konfirmasi-kehadiran/{userId}/{token}', [KehadiranController::class, 'konfirmasiViaWA'])
    ->name('kehadiran.konfirmasi-wa');

// =================== API ROUTES UNTUK FRONTEND ===================
// Routes API yang bisa diakses tanpa prefix user (untuk AJAX calls)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/kehadiran/status', [KehadiranController::class, 'getStatusKehadiran'])
        ->name('kehadiran.status');
        
    // Route untuk dashboard data (jika diperlukan)
    Route::get('/dashboard/data', [UserDashboardController::class, 'getDashboardData'])
        ->name('dashboard.data')
        ->middleware('auth.custom:user');
});