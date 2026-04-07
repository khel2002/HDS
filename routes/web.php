<?php

use Illuminate\Support\Facades\Route;

// ─── Controllers ──────────────────────────────────────────────────────────────

use App\Http\Controllers\FrontpageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\authentications\{
    LoginBasic,
    RegisterBasic,
    ForgotPasswordBasic
};

use App\Http\Controllers\pages\{
    AccountSettingsAccount,
    MiscError,
    MiscUnderMaintenance
};

use App\Http\Controllers\dashboard\GuestPortalController;
use App\Http\Controllers\guest\BreakfastController as GuestBreakfastController;
use App\Http\Controllers\Guest\CheckoutController as GuestCheckoutController;
use App\Http\Controllers\Guest\RoomServiceController as GuestRoomServiceController;

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\accounts\AdminAccountManagementController;
use App\Http\Controllers\admin\accounts\GuestAccountManagementController;

use App\Http\Controllers\SuperAdmin\{
    SuperAdminDashboardController,
    RoomController,
    RoomTypeController,
    AmenitiesController,
    ReservationController as SuperAdminReservationController,
    BreakfastController,
    RegistrationController,
    WalkInReservationController,
    CheckoutRequestController
};


// ══════════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES
// ══════════════════════════════════════════════════════════════════════════════

// ─── Frontpage ────────────────────────────────────────────────────────────────
Route::prefix('')->name('frontpage.')->group(function () {
    Route::get('/landing',        [FrontpageController::class, 'index'])->name('index');
    Route::get('/rooms',          [FrontpageController::class, 'availableRooms'])->name('available-rooms');
    Route::get('/room/{room_id}', [FrontpageController::class, 'roomDetails'])->name('room-details');
    Route::get('/sample-landing', [FrontpageController::class, 'sampleLanding'])->name('sample');
});

// ─── Reservations (Public) ────────────────────────────────────────────────────
Route::prefix('reservation')->name('reservation.')->group(function () {
    Route::get('/create/{room_id}',       [ReservationController::class, 'showReservationForm'])->name('create');
    Route::post('/store',                 [ReservationController::class, 'store'])->name('store');
    Route::get('/confirmation',           [ReservationController::class, 'confirmation'])->name('confirmation');
    Route::post('/check-availability',    [ReservationController::class, 'checkAvailability'])->name('check-availability');
    Route::get('/booked-dates/{room_id}', [ReservationController::class, 'getBookedDates'])->name('booked-dates');
    Route::post('/get-available-rooms',   [ReservationController::class, 'getAvailableRooms'])->name('get-available-rooms');
});

// ─── Payments (Public) ────────────────────────────────────────────────────────
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession'])->name('create-checkout');
    Route::get('/success',                  [PaymentController::class, 'paymentSuccess'])->name('success');
    Route::get('/cancel',                   [PaymentController::class, 'paymentCancel'])->name('cancel');
});

// ─── System Pages ─────────────────────────────────────────────────────────────
Route::prefix('system')->name('system.')->group(function () {
    Route::get('/error',       [MiscError::class, 'index'])->name('error');
    Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('maintenance');
});


// ══════════════════════════════════════════════════════════════════════════════
// AUTHENTICATION ROUTES  (guests only)
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware('guest')->prefix('auth')->group(function () {
    Route::get('/login',           [LoginBasic::class, 'index'])->name('login');
    Route::post('/login',          [LoginBasic::class, 'login'])->name('login.post');
    Route::get('/register',        [RegisterBasic::class, 'index'])->name('register');
    Route::get('/forgot-password', [ForgotPasswordBasic::class, 'index'])->name('forgot-password');

    // Google OAuth
    Route::get('/google',          [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
});


// ══════════════════════════════════════════════════════════════════════════════
// AUTHENTICATED ROUTES
// ══════════════════════════════════════════════════════════════════════════════

Route::middleware('auth')->group(function () {

    // ─── Logout ───────────────────────────────────────────────────────────────
    Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

    // ─── Root Redirect (by role) ──────────────────────────────────────────────
    Route::get('/', function () {
        $user = auth()->user();

        if ($user->isSuperAdmin()) return redirect()->route('super_admin.dashboard');
        if ($user->isAdmin())      return redirect()->route('admin.dashboard');
        if ($user->isStaff())      return redirect()->route('staff.dashboard');

        return redirect()->route('guest.dashboard');
    })->name('dashboard');

    // ─── Account Settings ─────────────────────────────────────────────────────
    Route::get('/account/settings', [AccountSettingsAccount::class, 'index'])->name('account.settings');

    // ─── Reservation Cancel ───────────────────────────────────────────────────
    Route::post('/reservation/cancel/{reservation_id}', [ReservationController::class, 'cancel'])
        ->name('reservation.cancel');


    // ══════════════════════════════════════════════════════════════════════════
    // GUEST PORTAL
    // ══════════════════════════════════════════════════════════════════════════

    Route::prefix('guest')->name('guest.')->group(function () {

        // Dashboard & Services
        Route::get('/dashboard',    [GuestPortalController::class, 'index'])->name('dashboard');
        Route::post('/service',     [GuestPortalController::class, 'requestService'])->name('service');
        Route::get('/notifications',[NotificationController::class, 'guest'])->name('notifications');

        // Breakfast
        Route::prefix('breakfast')->name('breakfast.')->group(function () {
            Route::get('/menu',      [GuestBreakfastController::class, 'index'])->name('menu');
            Route::get('/order',     [GuestBreakfastController::class, 'index'])->name('order');
            Route::post('/order',    [GuestBreakfastController::class, 'store'])->name('store');
            Route::get('/my-orders', [GuestBreakfastController::class, 'myOrders'])->name('my-orders');
            Route::get('/orders',    [GuestBreakfastController::class, 'myOrdersJson'])->name('orders');
        });

        // Room Service
        Route::prefix('room-service')->name('room-service.')->group(function () {
            Route::get('/',              [GuestRoomServiceController::class, 'index'])->name('index');
            Route::post('/request',      [GuestRoomServiceController::class, 'store'])->name('store');
            Route::get('/my-requests',   [GuestRoomServiceController::class, 'myRequests'])->name('my-requests');
            Route::get('/requests-json', [GuestRoomServiceController::class, 'myRequestsJson'])->name('requests-json');
        });

        // Checkout
        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::get('/',             [GuestCheckoutController::class, 'index'])->name('index');
            Route::post('/request',     [GuestCheckoutController::class, 'requestCheckout'])->name('request');
            Route::post('/acknowledge', [GuestCheckoutController::class, 'acknowledgeDamage'])->name('acknowledge');
            Route::get('/status',       [GuestCheckoutController::class, 'status'])->name('status');
        });
    });


    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN PANEL
    // ══════════════════════════════════════════════════════════════════════════

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Staff / Admin Accounts
        Route::prefix('account-management')->name('')->group(function () {
            Route::get('/',                [AdminAccountManagementController::class, 'index'])->name('accounts-management');
            Route::post('/users',          [AdminAccountManagementController::class, 'store'])->name('users.store');
            Route::post('/users/{id}/update', [AdminAccountManagementController::class, 'update'])->name('users.update');
            Route::post('/users/{id}/status', [AdminAccountManagementController::class, 'updateStatus'])->name('users.status');
            Route::post('/users/{id}/delete', [AdminAccountManagementController::class, 'destroy'])->name('users.destroy');
        });

        // Guest Accounts
        Route::get('/guest-accounts', [GuestAccountManagementController::class, 'index'])->name('guest-management');
    });


    // ══════════════════════════════════════════════════════════════════════════
    // SUPER ADMIN PANEL
    // ══════════════════════════════════════════════════════════════════════════

    Route::middleware('super_admin')->prefix('super-admin')->name('super_admin.')->group(function () {

        Route::get('/dashboard',    [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications',[NotificationController::class, 'superadmin'])->name('notifications');

        // Rooms
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/',              [RoomController::class, 'index'])->name('index');
            Route::post('/',             [RoomController::class, 'store'])->name('store');
            Route::get('/{id}',          [RoomController::class, 'show'])->name('show');
            Route::put('/{id}',          [RoomController::class, 'update'])->name('update');
            Route::delete('/{id}',       [RoomController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/status', [RoomController::class, 'updateStatus'])->name('update-status');
        });

        // Room Types
        Route::prefix('room-types')->name('room-types.')->group(function () {
            Route::get('/',        [RoomTypeController::class, 'index'])->name('index');
            Route::post('/',       [RoomTypeController::class, 'store'])->name('store');
            Route::get('/{id}',    [RoomTypeController::class, 'show'])->name('show');
            Route::put('/{id}',    [RoomTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
        });

        // Amenities
        Route::prefix('amenities')->name('amenities.')->group(function () {
            Route::get('/',        [AmenitiesController::class, 'index'])->name('index');
            Route::post('/',       [AmenitiesController::class, 'store'])->name('store');
            Route::get('/{id}',    [AmenitiesController::class, 'show'])->name('show');
            Route::put('/{id}',    [AmenitiesController::class, 'update'])->name('update');
            Route::delete('/{id}', [AmenitiesController::class, 'destroy'])->name('destroy');
        });

        // Reservations
        Route::prefix('reservations')->name('reservations.')->group(function () {
            Route::get('/walk-in', [WalkInReservationController::class, 'index'])->name('walkin');
            Route::get('/',        [SuperAdminReservationController::class, 'index'])->name('index');
            Route::post('/',       [SuperAdminReservationController::class, 'store'])->name('store');
            Route::get('/{id}',    [SuperAdminReservationController::class, 'show'])->name('show');
            Route::put('/{id}',    [SuperAdminReservationController::class, 'update'])->name('update');
            Route::delete('/{id}', [SuperAdminReservationController::class, 'destroy'])->name('destroy');

            // Reservation Status Actions
            Route::post('/{id}/approve', [SuperAdminReservationController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject',  [SuperAdminReservationController::class, 'reject'])->name('reject');
            Route::post('/{id}/cancel',  [SuperAdminReservationController::class, 'cancel'])->name('cancel');

            // Booking (Payment) Actions
            Route::post('/booking/{paymentId}/approve', [SuperAdminReservationController::class, 'approveBooking'])->name('booking.approve');
            Route::post('/booking/{paymentId}/reject',  [SuperAdminReservationController::class, 'rejectBooking'])->name('booking.reject');
            Route::post('/booking/{paymentId}/cancel',  [SuperAdminReservationController::class, 'cancelBooking'])->name('booking.cancel');
        });

        // Registration (Check-in / Check-out)
        Route::prefix('registration')->name('registration.')->group(function () {
            Route::get('/check-in',  [RegistrationController::class, 'checkIn'])->name('check-in');
            Route::get('/check-out', [RegistrationController::class, 'checkOut'])->name('check-out');
            Route::get('/all',       [RegistrationController::class, 'all'])->name('all');
            Route::get('/{id}',      [RegistrationController::class, 'show'])->name('show');

            Route::post('/check-in/{reservation_id}',  [RegistrationController::class, 'processCheckIn'])->name('process-check-in');
            Route::post('/check-out/{registration_id}', [RegistrationController::class, 'processCheckOut'])->name('process-check-out');
        });

        // Checkout Requests (Inspection Workflow)
        Route::prefix('checkout-requests')->name('checkout_requests.')->group(function () {
            Route::get('/',                              [CheckoutRequestController::class, 'index'])->name('index');
            Route::get('/{id}/details',                  [CheckoutRequestController::class, 'details'])->name('details');
            Route::post('/{id}/start-inspection',        [CheckoutRequestController::class, 'startInspection'])->name('start-inspection');
            Route::post('/{id}/add-charge',              [CheckoutRequestController::class, 'addCharge'])->name('add-charge');
            Route::delete('/{id}/charges/{chargeId}',    [CheckoutRequestController::class, 'removeCharge'])->name('remove-charge');
            Route::post('/{id}/mark-cleared',            [CheckoutRequestController::class, 'markCleared'])->name('mark-cleared');
            Route::post('/{id}/mark-has-issues',         [CheckoutRequestController::class, 'markHasIssues'])->name('mark-has-issues');
            Route::post('/{id}/finalize',                [CheckoutRequestController::class, 'finalizeCheckout'])->name('finalize');
        });

        // Breakfast Menu & Orders
        Route::prefix('breakfast')->name('breakfast.')->group(function () {
            Route::get('/menu',                  [BreakfastController::class, 'index'])->name('menu');
            Route::post('/menu',                 [BreakfastController::class, 'store'])->name('menu.store');
            Route::put('/menu/{id}',             [BreakfastController::class, 'update'])->name('menu.update');
            Route::patch('/menu/{id}/toggle',    [BreakfastController::class, 'toggleAvailability'])->name('menu.toggle');
            Route::delete('/menu/{id}',          [BreakfastController::class, 'destroy'])->name('menu.destroy');
            Route::get('/orders',                [BreakfastController::class, 'orders'])->name('orders');
            Route::patch('/orders/{id}/status',  [BreakfastController::class, 'updateOrderStatus'])->name('orders.status');
        });
    });

}); // end auth middleware