<?php

use Illuminate\Support\Facades\Route;

// Dashboard
use App\Http\Controllers\dashboard\Analytics;

// Frontpage
use App\Http\Controllers\FrontpageController;

// Pages
use App\Http\Controllers\pages\{
    AccountSettingsAccount,
    MiscError,
    MiscUnderMaintenance
};

// Authentication
use App\Http\Controllers\authentications\{
    LoginBasic,
    RegisterBasic,
    ForgotPasswordBasic
};
use App\Http\Controllers\GoogleAuthController;



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
Route::post('/login', [LoginBasic::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterBasic::class, 'index'])->name('register');
    Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('forgot-password');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
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
