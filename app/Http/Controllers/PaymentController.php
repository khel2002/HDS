<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Exception;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }


    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'reservation_data' => 'required|array',
            'amount' => 'required|numeric|min:500'
        ]);

        try {
            // Store reservation data in session
            session(['pending_reservation' => $validated['reservation_data']]);

            // Create Stripe checkout session
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'php',
                        'product_data' => [
                            'name' => 'Hotel Reservation Fee',
                            'description' => 'Reservation fee for ' . $validated['reservation_data']['room_type_name'],
                        ],
                        'unit_amount' => $validated['amount'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'room_id' => $validated['reservation_data']['room_id'],
                    'email' => $validated['reservation_data']['email']
                ]
            ]);

            return response()->json([
                'id' => $checkoutSession->id,
                'url' => $checkoutSession->url
            ]);

        } catch (Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create payment session'
            ], 500);
        }
    }


    public function paymentSuccess(Request $request)
    {
        \Log::info('Payment success handler started', ['session_id' => $request->get('session_id')]);

        try {
            $sessionId = $request->get('session_id');

            if (!$sessionId) {
                \Log::error('No session ID provided in payment success callback');
                return redirect()->route('frontpage.index')
                    ->with('error', 'Invalid payment session');
            }

            // Verify payment with Stripe
            \Log::info('Retrieving Stripe session', ['session_id' => $sessionId]);
            $session = Session::retrieve($sessionId);
            \Log::info('Stripe session retrieved', [
                'payment_status' => $session->payment_status,
                'payment_intent' => $session->payment_intent
            ]);

            if ($session->payment_status !== 'paid') {
                \Log::error('Payment not completed', ['status' => $session->payment_status]);
                return redirect()->route('frontpage.index')
                    ->with('error', 'Payment not completed');
            }

            // Get reservation data from session
            $reservationData = session('pending_reservation');
            \Log::info('Retrieved reservation data from session', [
                'has_data' => !is_null($reservationData),
                'data_keys' => $reservationData ? array_keys($reservationData) : []
            ]);

            if (!$reservationData) {
                \Log::error('Reservation data not found in session');
                return redirect()->route('frontpage.index')
                    ->with('error', 'Reservation data not found. Please try booking again.');
            }

            DB::beginTransaction();

            try {
                \Log::info('Creating reservation with data', ['email' => $reservationData['email']]);

                // Create reservation with online payment
                $result = $this->createReservation($reservationData, 'online', $sessionId, $session->payment_intent);

                \Log::info('Reservation created successfully', [
                    'reservation_id' => $result['reservation_id'],
                    'email' => $result['email']
                ]);

                // Send confirmation email
                $this->sendReservationEmail($result);

                DB::commit();
                \Log::info('Transaction committed successfully');

                // Clear pending reservation from session
                session()->forget('pending_reservation');

                // Store credentials and reservation data for confirmation page
                session([
                    'temp_credentials' => [
                        'email' => $result['email'],
                        'password' => $result['password'],
                        'reservation_id' => $result['reservation_id']
                    ],
                    'reservation_data' => $result,
                    'payment_success' => true,
                    'payment_method' => 'online'
                ]);

                \Log::info('Session data stored for confirmation', [
                    'has_credentials' => session()->has('temp_credentials'),
                    'has_reservation_data' => session()->has('reservation_data')
                ]);

                // Build the confirmation URL with original query parameters
                $confirmationUrl = route('reservation.create', ['room_id' => $reservationData['room_id']])
                    . '?arrival_date=' . $reservationData['arrival_date']
                    . '&departure_date=' . $reservationData['departure_date']
                    . '&adults=' . $reservationData['adults']
                    . '&children=' . $reservationData['children']
                    . '&payment_success=1';

                \Log::info('Redirecting to confirmation page', ['url' => $confirmationUrl]);

                // Redirect to confirmation page with step 4 active
                return redirect($confirmationUrl)
                    ->with('success', 'Payment successful! Check your email for login credentials.');

            } catch (Exception $e) {
                DB::rollBack();
                \Log::error('Reservation creation error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('frontpage.index')
                    ->with('error', 'Failed to create reservation after payment: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            \Log::error('Payment success handler error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('frontpage.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }


    public function paymentCancel()
    {
        session()->forget('pending_reservation');

        return redirect()->route('frontpage.index')
            ->with('error', 'Payment was cancelled');
    }


    private function createReservation($data, $paymentMethod, $stripeSessionId = null, $stripePaymentIntent = null)
    {
        \Log::info('createReservation method started', [
            'room_id' => $data['room_id'],
            'email' => $data['email'],
            'payment_method' => $paymentMethod
        ]);

        // Verify room availability
        $room = DB::table('rooms')
            ->where('room_id', $data['room_id'])
            ->where('status', 'available')
            ->first();

        if (!$room) {
            \Log::error('Room not available', ['room_id' => $data['room_id']]);
            throw new Exception('Room is no longer available');
        }
        \Log::info('Room verified as available', ['room_number' => $room->room_number]);

        // Check for date overlaps
        $hasOverlap = $this->checkDateOverlap($data['room_id'], $data['arrival_date'], $data['departure_date']);

        if ($hasOverlap) {
            \Log::error('Date overlap detected', [
                'room_id' => $data['room_id'],
                'arrival' => $data['arrival_date'],
                'departure' => $data['departure_date']
            ]);
            throw new Exception('Room is already booked for the selected dates');
        }
        \Log::info('No date overlap found');

        // Generate temporary password
        $temporaryPassword = Str::random(12);

        // Get guest role ID
        $guestRoleId = DB::table('roles')
            ->where('role_name', 'guest')
            ->value('role_id');

        if (!$guestRoleId) {
            \Log::error('Guest role not found in database');
            throw new Exception('Guest role not found in database');
        }
        \Log::info('Guest role found', ['role_id' => $guestRoleId]);

        // Create user account
        try {
            $userId = DB::table('users')->insertGetId([
                'role_id' => $guestRoleId,
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'temporary_act' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            \Log::info('User created', ['user_id' => $userId]);
        } catch (Exception $e) {
            \Log::error('Failed to create user', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create user account: ' . $e->getMessage());
        }

        // Create guest details
        try {
            $guestDetailsId = DB::table('guest_details')->insertGetId([
                'user_id' => $userId,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'contact_number' => $data['contact_number'],
                'dob' => $data['dob'],
                'arrival_date' => $data['arrival_date'],
                'departure_date' => $data['departure_date'],
                'created_at' => now()
            ]);
            \Log::info('Guest details created', ['guest_details_id' => $guestDetailsId]);
        } catch (Exception $e) {
            \Log::error('Failed to create guest details', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create guest details: ' . $e->getMessage());
        }

        // Create payment record with all required fields
        try {
            $paymentId = DB::table('payments')->insertGetId([
                'payment_method' => $paymentMethod,
                'stripe_session_id' => $stripeSessionId,
                'stripe_payment_intent' => $stripePaymentIntent,
                'paid_at' => $paymentMethod === 'online' ? now() : null,
                'amount' => 500.00, // Reservation fee amount
                'payment_date' => now(),
                'payment_status' => $paymentMethod === 'online' ? 'completed' : 'pending'
            ]);
            \Log::info('Payment record created', [
                'payment_id' => $paymentId,
                'method' => $paymentMethod,
                'stripe_session' => $stripeSessionId
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to create payment record', ['error' => $e->getMessage()]);
            throw new Exception('Failed to create payment record: ' . $e->getMessage());
        }

        // Calculate pricing
        $roomType = DB::table('room_types')
            ->where('room_type_id', $room->room_type_id)
            ->first();

        if (!$roomType) {
            \Log::error('Room type not found', ['room_type_id' => $room->room_type_id]);
            throw new Exception('Room type not found');
        }

        $arrival = new \DateTime($data['arrival_date']);
        $departure = new \DateTime($data['departure_date']);
        $nights = $arrival->diff($departure)->days;

        $subtotal = $roomType->rate_per_night * $nights;
        $reservationFee = 500;
        $totalAmount = $subtotal + $reservationFee;
        $balance = $totalAmount - $reservationFee;

        \Log::info('Pricing calculated', [
            'nights' => $nights,
            'rate_per_night' => $roomType->rate_per_night,
            'subtotal' => $subtotal,
            'total' => $totalAmount,
            'balance' => $balance
        ]);

        // Create reservation with all required fields
        try {
            $reservationId = DB::table('reservations')->insertGetId([
                'user_id' => $userId,
                'guest_details_id' => $guestDetailsId,
                'payment_id' => $paymentId,
                'room_id' => $data['room_id'],
                'reservation_fee' => $reservationFee,
                'purpose' => $data['purpose'] ?? null,
                'total_amount' => $totalAmount,
                'balance' => $balance,
                'reservation_status' => 'pending',
                'booking_date' => now()->toDateString(),
                'check_in_date' => $data['arrival_date'],
                'check_out_date' => $data['departure_date'],
                'adults' => $data['adults'],
                'children' => $data['children'],
                'no_nights' => $nights,
                'reservation_fee_paid' => $paymentMethod === 'online' ? 1 : 0,
                'created_at' => now()
            ]);
            \Log::info('Reservation created successfully', ['reservation_id' => $reservationId]);
        } catch (Exception $e) {
            \Log::error('Failed to create reservation', [
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode()
            ]);
            throw new Exception('Failed to create reservation: ' . $e->getMessage());
        }

        return [
            'reservation_id' => $reservationId,
            'email' => $data['email'],
            'password' => $temporaryPassword,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'room_number' => $room->room_number,
            'room_type_name' => $roomType->room_type_name,
            'arrival_date' => $data['arrival_date'],
            'departure_date' => $data['departure_date'],
            'total_amount' => $totalAmount,
            'balance' => $balance,
            'no_nights' => $nights
        ];
    }


    private function checkDateOverlap($roomId, $arrivalDate, $departureDate)
    {
        return DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $roomId)
            ->where('reservations.reservation_status', '!=', 'cancelled')
            ->where('reservations.reservation_status', '!=', 'rejected')
            ->where(function($query) use ($arrivalDate, $departureDate) {
                $query->whereBetween('guest_details.arrival_date', [$arrivalDate, $departureDate])
                      ->orWhereBetween('guest_details.departure_date', [$arrivalDate, $departureDate])
                      ->orWhere(function($q) use ($arrivalDate, $departureDate) {
                          $q->where('guest_details.arrival_date', '<=', $arrivalDate)
                            ->where('guest_details.departure_date', '>=', $departureDate);
                      });
            })
            ->exists();
    }


    private function sendReservationEmail($data)
    {
        try {
            Mail::send('emails.reservation-confirmation', $data, function($message) use ($data) {
                $message->to($data['email'], $data['first_name'] . ' ' . $data['last_name'])
                        ->subject('Reservation Confirmation - Hotel De SLSU');
            });

            \Log::info('Reservation confirmation email sent to: ' . $data['email']);
        } catch (Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
            // Don't throw - email failure shouldn't break the reservation
        }
    }

}
