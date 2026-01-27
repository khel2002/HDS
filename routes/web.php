<?php

use Illuminate\Support\Facades\Route;

// Dashboard
use App\Http\Controllers\dashboard\Analytics;

// Frontpage
use App\Http\Controllers\FrontpageController;

// Pages
use App\Http\Controllers\pages\{
    AccountSettingsAccount,
};

// Authentication
use App\Http\Controllers\authentications\{
    LoginBasic,
    RegisterBasic,
    ForgotPasswordBasic
};

/*
|--------------------------------------------------------------------------
| Main Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [Analytics::class, 'index'])->name('dashboard');
Route::get('/landing', [FrontpageController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::get('/login', [LoginBasic::class, 'index'])->name('login');
    Route::get('/register', [RegisterBasic::class, 'index'])->name('register');
    Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('forgot-password');
});

/*
|--------------------------------------------------------------------------
| Account Settings
|--------------------------------------------------------------------------
*/
Route::prefix('account')->group(function () {
    Route::get('/settings', [AccountSettingsAccount::class, 'index'])->name('account.settings');
});



/*
|--------------------------------------------------------------------------
| System Pages
|--------------------------------------------------------------------------
*/
Route::prefix('system')->group(function () {
    Route::get('/error', [MiscError::class, 'index'])->name('system.error');
    Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
