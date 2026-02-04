<?php

use App\Http\Controllers\admin\AdminAccountManagementController;
use App\Http\Controllers\admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\RoomController;
use App\Http\Controllers\SuperAdmin\RoomTypeController;
use App\Http\Controllers\SuperAdmin\AmenitiesController;

use App\Http\Controllers\FrontpageController;


use App\Http\Controllers\pages\{
    AccountSettingsAccount,
    MiscError,
    MiscUnderMaintenance
};


use App\Http\Controllers\authentications\{
    LoginBasic,
    RegisterBasic,
    ForgotPasswordBasic
};
use App\Http\Controllers\GoogleAuthController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/landing', [FrontpageController::class, 'index'])->name('landing');


Route::middleware(['guest'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/login', [LoginBasic::class, 'index'])->name('login');
        Route::post('/login', [LoginBasic::class, 'login'])->name('login.post');
        Route::get('/register', [RegisterBasic::class, 'index'])->name('register');
        Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('forgot-password');
    });

    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
});


Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');


    Route::get('/', function() {
        $user = auth()->user();


        if ($user->isSuperAdmin()) {
            return redirect()->route('super_admin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }


        return redirect()->route('guest.dashboard');
    })->name('dashboard');


    Route::prefix('account')->group(function () {
        Route::get('/settings', [AccountSettingsAccount::class, 'index'])->name('account.settings');
    });
});


Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');

});


Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');



});


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

     Route::get('/account-management', [AdminAccountManagementController::class, 'index'])->name('accounts-management');




});


Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

      Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index'); // This is the GET route for listing/filtering
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{id}', [RoomController::class, 'show'])->name('show');
        Route::put('/{id}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });

   Route::prefix('room-types')->name('room-types.')->group(function () {
    Route::get('/', [RoomTypeController::class, 'index'])->name('index');
    Route::get('/{id}', [RoomTypeController::class, 'show'])->name('show');
    Route::post('/', [RoomTypeController::class, 'store'])->name('store');
    Route::put('/{id}', [RoomTypeController::class, 'update'])->name('update');
    Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('amenities')->name('amenities.')->group(function () {
        Route::get('/', [AmenitiesController::class, 'index'])->name('index');
        Route::post('/', [AmenitiesController::class, 'store'])->name('store');
        Route::get('/{id}', [AmenitiesController::class, 'show'])->name('show');
        Route::put('/{id}', [AmenitiesController::class, 'update'])->name('update');
        Route::delete('/{id}', [AmenitiesController::class, 'destroy'])->name('destroy');
    });
});


Route::prefix('system')->group(function () {
    Route::get('/error', [MiscError::class, 'index'])->name('system.error');
    Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
