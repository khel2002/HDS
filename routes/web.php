<?php
<<<<<<< Updated upstream

use App\Http\Controllers\admin\accounts\AdminAccountManagementController;
use App\Http\Controllers\admin\accounts\GuestAccountManagementController;
use App\Http\Controllers\admin\AdminDashboardController;
=======
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\{
  AdminAccountManagementController,
  AdminDashboardController
};

use App\Http\Controllers\dashboard\Analytics;

use App\Http\Controllers\SuperAdmin\{
  SuperAdminDashboardController,
  ReservationController as SuperAdminReservationController,
  RoomController,
  RoomTypeController,
  AmenitiesController
};


use App\Http\Controllers\{
  FrontpageController,
  PaymentController,
  GoogleAuthController,
  ReservationController

};

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


// Landing pages
Route::get('/landing', [FrontpageController::class, 'index'])->name('frontpage.index');
Route::get('/room/{room_id}', [FrontpageController::class, 'roomDetails'])->name('frontpage.room-details');
Route::get('/sample-landing', [FrontpageController::class, 'sampleLanding'])->name('frontpage.sample');

// Reservation routes
Route::prefix('reservation')->name('reservation.')->group(function () {
  Route::get('/create/{room_id}', [ReservationController::class, 'showReservationForm'])->name('create');
  Route::post('/store', [ReservationController::class, 'store'])->name('store');
  Route::post('/get-available-rooms', [ReservationController::class, 'getAvailableRooms'])
    ->name('get-available-rooms');

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
<<<<<<< Updated upstream

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

=======
>>>>>>> Stashed changes

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

  Route::prefix('reservations')->name('reservations.')->group(function () {
    Route::get('/all', [SuperAdminReservationController::class, 'index'])->name('index');
    Route::post('/all', [SuperAdminReservationController::class, 'store'])->name('store');
    Route::get('/{id}', [SuperAdminReservationController::class, 'show'])->name('show');
    Route::put('/{id}', [SuperAdminReservationController::class, 'update'])->name('update');
    Route::delete('/{id}', [SuperAdminReservationController::class, 'destroy'])->name('destroy');
    
    // Status management
    Route::post('/{id}/approve', [SuperAdminReservationController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [SuperAdminReservationController::class, 'reject'])->name('reject');
    Route::post('/{id}/cancel', [SuperAdminReservationController::class, 'cancel'])->name('cancel');
  });
});

// System routes
Route::prefix('system')->group(function () {
  Route::get('/error', [MiscError::class, 'index'])->name('system.error');
  Route::get('/maintenance', [MiscUnderMaintenance::class, 'index'])->name('system.maintenance');
});
// Route::get('/test-guest-data', function() {
//     $userId = 20; // Replace with auth()->id() or your actual user ID
    
//     echo "<h1>Guest Name Debugging</h1>";
//     echo "<hr>";
    
//     // Test 1: Check User Model
//     echo "<h2>1. User Table Data</h2>";
//     $user = \App\Models\User::find($userId);
//     if ($user) {
//         echo "✓ User found<br>";
//         echo "- email: " . $user->email . "<br>";
//         echo "- first_name: " . ($user->first_name ?? '<span style="color:red">NULL</span>') . "<br>";
//         echo "- last_name: " . ($user->last_name ?? '<span style="color:red">NULL</span>') . "<br>";
//     } else {
//         echo "✗ User not found<br>";
//     }
    
//     echo "<hr>";
    
//     // Test 2: Check GuestDetail Model
//     echo "<h2>2. Guest Details Table Data</h2>";
//     $guestDetail = \App\Models\GuestDetail::where('user_id', $userId)->first();
//     if ($guestDetail) {
//         echo "✓ Guest details record found<br>";
//         echo "- guest_details_id: " . $guestDetail->guest_details_id . "<br>";
//         echo "- first_name: " . ($guestDetail->first_name ?? '<span style="color:red">NULL</span>') . "<br>";
//         echo "- middle_name: " . ($guestDetail->middle_name ?? '<span style="color:red">NULL</span>') . "<br>";
//         echo "- last_name: " . ($guestDetail->last_name ?? '<span style="color:red">NULL</span>') . "<br>";
//         echo "- <strong>full_name accessor: " . $guestDetail->full_name . "</strong><br>";
//     } else {
//         echo "✗ Guest details record NOT found<br>";
//     }
    
//     echo "<hr>";
    
//     // Test 3: Check Raw Database Query
//     echo "<h2>3. Raw Database Query</h2>";
//     $rawData = DB::table('guest_details')->where('user_id', $userId)->first();
//     if ($rawData) {
//         echo "✓ Raw query found data<br>";
//         echo "<pre>";
//         print_r($rawData);
//         echo "</pre>";
//     } else {
//         echo "✗ Raw query found nothing<br>";
//     }
    
//     echo "<hr>";
    
//     // Test 4: Simulate Controller Logic
//     echo "<h2>4. Simulating Controller getGuestInfo()</h2>";
    
//     $guestDetails = \App\Models\GuestDetail::where('user_id', $userId)->first();
    
//     $guestName = 'Guest'; // Default fallback
//     if ($guestDetails) {
//         echo "✓ Guest details exists<br>";
        
//         // Check if the accessor method exists
//         if (method_exists($guestDetails, 'getFullNameAttribute')) {
//             echo "✓ getFullNameAttribute method exists<br>";
//             $guestName = $guestDetails->full_name;
//             echo "✓ Using accessor: <strong style='color:green'>" . $guestName . "</strong><br>";
//         } else {
//             echo "✗ getFullNameAttribute method NOT found<br>";
//             // Manual construction
//             $parts = array_filter([
//                 $guestDetails->first_name,
//                 $guestDetails->middle_name,
//                 $guestDetails->last_name,
//             ]);
//             $guestName = implode(' ', $parts);
//             echo "✓ Manual construction: <strong style='color:blue'>" . $guestName . "</strong><br>";
//         }
        
//         // Check if empty
//         if (empty(trim($guestName))) {
//             echo "✗ Guest name is EMPTY after construction<br>";
//             $guestName = 'Guest';
//         } else {
//             echo "✓ Guest name has content: '" . $guestName . "'<br>";
//         }
//     } else {
//         echo "✗ No guest details found<br>";
//     }
    
//     echo "<br><h3>Final Result: <span style='color:green; font-size:24px'>" . $guestName . "</span></h3>";
    
//     echo "<hr>";
//     echo "<h2>5. What the Blade Template Sees</h2>";
//     echo "<p>In your blade template, the variable would be:</p>";
//     echo "<code>\$guestInfo['guest_name'] = '" . $guestName . "'</code><br>";
//     echo "<p>And it would display as: <strong>" . $guestName . "</strong></p>";
    
//     return '';
// });