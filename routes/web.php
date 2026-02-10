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
use App\Http\Controllers\PaymentController;

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
use App\Http\Controllers\ReservationController;

// Landing pages
Route::get('/landing', [FrontpageController::class, 'index'])->name('frontpage.index');
Route::get('/room/{room_id}', [FrontpageController::class, 'roomDetails'])->name('frontpage.room-details');
Route::get('/sample-landing', [FrontpageController::class, 'sampleLanding'])->name('frontpage.sample');

// Reservation routes
Route::prefix('reservation')->name('reservation.')->group(function () {
    Route::get('/create/{room_id}', [ReservationController::class, 'showReservationForm'])->name('create');
    Route::post('/store', [ReservationController::class, 'store'])->name('store');

    // New confirmation route
    Route::get('/confirmation', [ReservationController::class, 'confirmation'])->name('confirmation');

    // Legacy success route (can be removed if not used elsewhere)
    Route::get('/success', [ReservationController::class, 'success'])->name('success');

    Route::post('/check-availability', [ReservationController::class, 'checkAvailability'])->name('check-availability');
    Route::get('/booked-dates/{room_id}', [ReservationController::class, 'getBookedDates'])->name('booked-dates');
});

// Payment routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession'])->name('create-checkout');
    Route::get('/success', [PaymentController::class, 'paymentSuccess'])->name('success');
    Route::get('/cancel', [PaymentController::class, 'paymentCancel'])->name('cancel');
});

// Route::get('/debug-session', function() {
//     return [
//         'all_session_data' => session()->all(),
//         'has_pending_reservation' => session()->has('pending_reservation'),
//         'pending_reservation' => session('pending_reservation'),
//         'has_temp_credentials' => session()->has('temp_credentials'),
//         'temp_credentials' => session('temp_credentials'),
//         'has_reservation_data' => session()->has('reservation_data'),
//         'reservation_data' => session('reservation_data'),
//         'payment_success' => session('payment_success'),
//         'payment_method' => session('payment_method'),
//     ];
// })->name('debug.session');

// // Test route to simulate payment success
// Route::get('/test-payment-success', function() {
//     // Simulate reservation data
//     session([
//         'pending_reservation' => [
//             'room_id' => 1,
//             'first_name' => 'Test',
//             'middle_name' => '',
//             'last_name' => 'User',
//             'email' => 'test@example.com',
//             'contact_number' => '+639123456789',
//             'dob' => '1990-01-01',
//             'arrival_date' => '2026-02-15',
//             'departure_date' => '2026-02-17',
//             'adults' => 2,
//             'children' => 0,
//             'purpose' => 'Vacation',
//             'room_type_name' => 'Standard Double'
//         ]
//     ]);

//     return 'Session data set. Now visit: /payment/success?session_id=test_session_id';
// })->name('test.payment');

// Authenticated reservation actions
Route::middleware(['auth'])->prefix('reservation')->name('reservation.')->group(function () {
    Route::post('/cancel/{reservation_id}', [ReservationController::class, 'cancel'])->name('cancel');
});

// Guest authentication routes
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

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Logout
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

    // Dashboard redirect based on role
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

    // Account settings
    Route::prefix('account')->group(function () {
        Route::get('/settings', [AccountSettingsAccount::class, 'index'])->name('account.settings');
    });
});

// Guest dashboard
Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');
});

// Staff dashboard
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');
});

// Admin dashboard
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/account-management', [AdminAccountManagementController::class, 'index'])->name('accounts-management');
});

// Super Admin dashboard
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // Rooms management
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{id}', [RoomController::class, 'show'])->name('show');
        Route::put('/{id}', [RoomController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [RoomController::class, 'updateStatus'])->name('update-status');
    });

    // Room types management
    Route::prefix('room-types')->name('room-types.')->group(function () {
        Route::get('/', [RoomTypeController::class, 'index'])->name('index');
        Route::get('/{id}', [RoomTypeController::class, 'show'])->name('show');
        Route::post('/', [RoomTypeController::class, 'store'])->name('store');
        Route::put('/{id}', [RoomTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
    });

    // Amenities management
    Route::prefix('amenities')->name('amenities.')->group(function () {
        Route::get('/', [AmenitiesController::class, 'index'])->name('index');
        Route::post('/', [AmenitiesController::class, 'store'])->name('store');
        Route::get('/{id}', [AmenitiesController::class, 'show'])->name('show');
        Route::put('/{id}', [AmenitiesController::class, 'update'])->name('update');
        Route::delete('/{id}', [AmenitiesController::class, 'destroy'])->name('destroy');
    });
});

// System routes
Route::prefix('system')->group(function () {
    Route::get('/error', [MiscError::class, 'index'])->name('system.error');
    Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
