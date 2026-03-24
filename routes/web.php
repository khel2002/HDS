<?php

use App\Http\Controllers\admin\accounts\AdminAccountManagementController;
use App\Http\Controllers\admin\accounts\GuestAccountManagementController;
use App\Http\Controllers\admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\guest\BreakfastController as GuestBreakfastController;
use App\Http\Controllers\dashboard\GuestPortalController;
use App\Http\Controllers\Guest\RoomServiceController;
use App\Http\Controllers\SuperAdmin\{
    SuperAdminDashboardController,
    RoomController,
    RoomTypeController,
    AmenitiesController,
    ReservationController as SuperAdminReservationController,
    BreakfastController,
    RegistrationController,
    WalkInReservationController
};

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
Route::get('/rooms', [FrontpageController::class, 'availableRooms'])->name('frontpage.available-rooms');
// Reservation routes
Route::prefix('reservation')->name('reservation.')->group(function () {
    Route::get('/create/{room_id}', [ReservationController::class, 'showReservationForm'])->name('create');
    Route::post('/store',           [ReservationController::class, 'store'])->name('store');

    // Confirmation: reads from DB via signed URL params — no session required
    // Params: uid, rids, token, pm, pw
    Route::get('/confirmation',     [ReservationController::class, 'confirmation'])->name('confirmation');

    Route::post('/check-availability',    [ReservationController::class, 'checkAvailability'])->name('check-availability');
    Route::get('/booked-dates/{room_id}', [ReservationController::class, 'getBookedDates'])->name('booked-dates');
    Route::post('/get-available-rooms',   [ReservationController::class, 'getAvailableRooms'])->name('get-available-rooms');
});

// Payment routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession'])->name('create-checkout');
    Route::get('/success',                  [PaymentController::class, 'paymentSuccess'])->name('success');
    Route::get('/cancel',                   [PaymentController::class, 'paymentCancel'])->name('cancel');
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

Route::get('/debug-session', function () {
  return [
    'all_session_data' => session()->all(),
    'has_pending_reservation' => session()->has('pending_reservation'),
    'pending_reservation' => session('pending_reservation'),
    'has_temp_credentials' => session()->has('temp_credentials'),
    'temp_credentials' => session('temp_credentials'),
    'has_reservation_data' => session()->has('reservation_data'),
    'reservation_data' => session('reservation_data'),
    'payment_success' => session('payment_success'),
    'payment_method' => session('payment_method'),
  ];
})->name('debug.session');

// Test route to simulate payment success
Route::get('/test-payment-success', function () {
  // Simulate reservation data
  session([
    'pending_reservation' => [
      'room_id' => 1,
      'first_name' => 'Test',
      'middle_name' => '',
      'last_name' => 'User',
      'email' => 'test@example.com',
      'contact_number' => '+639123456789',
      'dob' => '1990-01-01',
      'arrival_date' => '2026-02-15',
      'departure_date' => '2026-02-17',
      'adults' => 2,
      'children' => 0,
      'purpose' => 'Vacation',
      'room_type_name' => 'Standard Double'
    ]
  ]);

  return 'Session data set. Now visit: /payment/success?session_id=test_session_id';
})->name('test.payment');


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
  Route::get('/', function () {
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

Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
 
    Route::get('/dashboard', [GuestPortalController::class, 'index'])->name('dashboard');
    Route::post('/service',  [GuestPortalController::class, 'requestService'])->name('service');
 
    Route::prefix('breakfast')->name('breakfast.')->group(function () {
        Route::get('/menu',[GuestBreakfastController::class, 'index'])->name('menu');
        Route::get('/order',[GuestBreakfastController::class, 'index'])->name('order');
        Route::post('/order',[GuestBreakfastController::class, 'store'])->name('store');
        Route::get('/my-orders',[GuestBreakfastController::class, 'myOrders']) ->name('my-orders');
        Route::get('/orders',[GuestBreakfastController::class, 'myOrdersJson'])->name('orders');
    });
});
Route::prefix('guest/room-service')->name('guest.room-service.')->group(function () {
    Route::get('/',              [RoomServiceController::class, 'index'])           ->name('index');
    Route::post('/request',      [RoomServiceController::class, 'store'])           ->name('store');
    Route::get('/my-requests',   [RoomServiceController::class, 'myRequests'])      ->name('my-requests');
    Route::get('/requests-json', [RoomServiceController::class, 'myRequestsJson']) ->name('requests-json');
});
// Staff dashboard
Route::middleware(['auth', 'staff'])->prefix('staff')->name('staff.')->group(function () {
  Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
});

// Admin dashboard
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
  Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

  Route::get('/account-management', [AdminAccountManagementController::class, 'index'])->name('accounts-management');
  Route::post('/users', [AdminAccountManagementController::class, 'store'])->name('users.store');
  Route::post('/users/{id}/update', [AdminAccountManagementController::class, 'update'])->name('users.update');   // Changed
  Route::post('/users/{id}/status', [AdminAccountManagementController::class, 'updateStatus'])->name('users.status'); // Changed to POST
  Route::post('/users/{id}/delete', [AdminAccountManagementController::class, 'destroy'])->name('users.destroy'); // Changed
  // guest
  Route::get('/guest-accounts', [GuestAccountManagementController::class, 'index'])->name('guest-management');
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
Route::prefix('reservations')->name('reservations.')->group(function () {


  //walk-in reservations
    Route::get('/walk-in',[WalkInReservationController::class, 'index'])->name('walkin');

    
    Route::get('/',[SuperAdminReservationController::class, 'index'])->name('index');
    Route::post('/',[SuperAdminReservationController::class, 'store'])->name('store');
    Route::get('/{id}',[SuperAdminReservationController::class, 'show'])->name('show');
    Route::put('/{id}',[SuperAdminReservationController::class, 'update'])->name('update');
    Route::delete('/{id}',[SuperAdminReservationController::class, 'destroy'])->name('destroy');




    // Single-room status actions
    Route::post('/{id}/approve',[SuperAdminReservationController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject',[SuperAdminReservationController::class, 'reject'])->name('reject');
    Route::post('/{id}/cancel',[SuperAdminReservationController::class, 'cancel'])->name('cancel');

    // Bulk booking (all rooms) status actions — keyed by payment_id
    Route::post('/booking/{paymentId}/approve',[SuperAdminReservationController::class, 'approveBooking']) ->name('booking.approve');
    Route::post('/booking/{paymentId}/reject',[SuperAdminReservationController::class, 'rejectBooking'])  ->name('booking.reject');
    Route::post('/booking/{paymentId}/cancel',[SuperAdminReservationController::class, 'cancelBooking'])  ->name('booking.cancel');
});
Route::prefix('registration')->name('registration.')->group(function () {

    // Pages
    Route::get('/check-in',  [RegistrationController::class, 'checkIn'])->name('check-in');
    Route::get('/check-out', [RegistrationController::class, 'checkOut'])->name('check-out');
    Route::get('/all',       [RegistrationController::class, 'all'])->name('all');
    Route::get('/{id}',      [RegistrationController::class, 'show'])->name('show');

    // Actions
    Route::post('/check-in/{reservation_id}',   [RegistrationController::class, 'processCheckIn'])->name('process-check-in');
    Route::post('/check-out/{registration_id}',  [RegistrationController::class, 'processCheckOut'])->name('process-check-out');
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
  Route::prefix('breakfast')->name('breakfast.')->group(function () {
    Route::get('/menu',[BreakfastController::class, 'index'])->name('menu');
    Route::post('/menu', [BreakfastController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}',[BreakfastController::class, 'update'])->name('menu.update');
    Route::patch('/menu/{id}/toggle',[BreakfastController::class, 'toggleAvailability'])->name('menu.toggle');
    Route::delete('/menu/{id}',[BreakfastController::class, 'destroy'])->name('menu.destroy');
    Route::get('/orders', [BreakfastController::class, 'orders'])->name('orders');
    Route::patch('/orders/{id}/status', [BreakfastController::class, 'updateOrderStatus'])->name('orders.status');
  });
});

// System routes
Route::prefix('system')->group(function () {
  Route::get('/error', [MiscError::class, 'index'])->name('system.error');
  Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
