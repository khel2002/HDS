<?php

use Illuminate\Support\Facades\Route;

// Dashboard
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\RoomController;
use App\Http\Controllers\SuperAdmin\RoomTypeController;
use App\Http\Controllers\SuperAdmin\AmenitiesController;
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
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/landing', [FrontpageController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated users only)
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| Authenticated Routes - Dashboard Router
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Logout route
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

    // Root dashboard - redirects based on role
    Route::get('/', function() {
        $user = auth()->user();

        // Redirect based on role_id
        if ($user->isSuperAdmin()) {
            return redirect()->route('super_admin.dashboard');
        } elseif ($user->isAdmin() || $user->isManager()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }

        // Default fallback
        return redirect()->route('guest.dashboard');
    })->name('dashboard');

    // Account Settings
    Route::prefix('account')->group(function () {
        Route::get('/settings', [AccountSettingsAccount::class, 'index'])->name('account.settings');
    });
});

/*
|--------------------------------------------------------------------------
| Guest User Routes (Role ID 4)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');
    // Add other guest routes here
});

/*
|--------------------------------------------------------------------------
| Staff Routes (Role ID 2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');

    // Add staff-specific routes here
    // Route::get('/reservations', [StaffReservationController::class, 'index'])->name('reservations');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Role ID 3 - Manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');

    // Add admin-specific routes here
    // Route::resource('/users', AdminUserController::class);
    // Route::get('/reports', [AdminReportController::class, 'index'])->name('reports');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Role ID 1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/rooms/all', [RoomController::class, 'index'])->name('super_admin.rooms.index');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');

    Route::get('/room-types/all', [RoomTypeController::class, 'index'])->name('room-types.index');
    Route::get('/room-types/{id}', [RoomTypeController::class, 'show'])->name('room-types.show');

    Route::get('/amenities/all', [AmenitiesController::class, 'index'])->name('amenities.index');
    route::get('/amenities/{id}', [AmenitiesController::class, 'show'])->name('amenities.show');
});

/*
|--------------------------------------------------------------------------
| System Pages (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('system')->group(function () {
    Route::get('/error', [MiscError::class, 'index'])->name('system.error');
    Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
