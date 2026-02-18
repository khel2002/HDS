<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
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


    // ─────────────────────────────────────────────────────────────
    // CREATE STRIPE CHECKOUT SESSION
    // ─────────────────────────────────────────────────────────────
    public function createCheckoutSession(Request $request)
    {
        \Log::info('Stripe checkout payload:', $request->all());

        $validated = $request->validate([
            'reservation_data'                          => 'required|array',
            'reservation_data.rooms'                    => 'required|array|min:1',
            'reservation_data.rooms.*.room_id'          => 'required|integer',
            'reservation_data.rooms.*.arrival_date'     => 'required|date',
            'reservation_data.rooms.*.departure_date'   => 'required|date|after:reservation_data.rooms.*.arrival_date',
            'reservation_data.rooms.*.number_of_guests' => 'required|integer|min:1',
            'reservation_data.rooms.*.special_requests' => 'nullable|string',
            'reservation_data.first_name'               => 'required|string|max:45',
            'reservation_data.middle_name'              => 'nullable|string|max:45',
            'reservation_data.last_name'                => 'required|string|max:45',
            'reservation_data.email'                    => 'required|email|max:100',
            'reservation_data.contact_number'           => 'required|string|max:45',
            'reservation_data.dob'                      => 'required|date|before:today',
            'amount'                                    => 'required|numeric|min:500',
        ]);

        try {
            $reservationData = $validated['reservation_data'];
            $roomCount       = count($reservationData['rooms']);

            $primaryArrival   = $reservationData['rooms'][0]['arrival_date'];
            $primaryDeparture = $reservationData['rooms'][0]['departure_date'];

            // Store in both PHP session and file cache
            session(['pending_reservation' => $reservationData]);
            session()->save();

            $cacheKey = 'pending_reservation_' . Str::uuid();
            Cache::store('file')->put($cacheKey, $reservationData, now()->addHours(2));

            $successUrl = route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}';

            $checkoutSession = Session::create([
                'payment_method_types'  => ['card'],
                'client_reference_id'   => $cacheKey,
                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'php',
                        'product_data' => [
                            'name'        => 'Hotel Reservation Fee',
                            'description' => sprintf(
                                'Reservation fee for %d room(s) - %s to %s',
                                $roomCount,
                                $primaryArrival,
                                $primaryDeparture
                            ),
                        ],
                        'unit_amount' => (int) ($validated['amount'] * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'           => 'payment',
                'success_url'    => $successUrl,
                'cancel_url'     => route('payment.cancel'),
                'customer_email' => $reservationData['email'],
                'metadata'       => [
                    'customer_name'  => $reservationData['first_name'] . ' ' . $reservationData['last_name'],
                    'email'          => $reservationData['email'],
                    'room_count'     => (string) $roomCount,
                    'arrival_date'   => $primaryArrival,
                    'departure_date' => $primaryDeparture,
                    'cache_key'      => $cacheKey,
                ],
            ]);

            return response()->json([
                'id'  => $checkoutSession->id,
                'url' => $checkoutSession->url,
            ]);

        } catch (Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage());
            return response()->json([
                'error'   => 'Failed to create payment session',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // ─────────────────────────────────────────────────────────────
    // PAYMENT SUCCESS CALLBACK
    // ─────────────────────────────────────────────────────────────
    public function paymentSuccess(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            if (!$sessionId) {
                \Log::error('paymentSuccess: no session_id in request');
                return redirect()->route('frontpage.index')->with('error', 'Invalid payment session');
            }

            // Verify with Stripe
            $stripeSession = Session::retrieve($sessionId);

            if ($stripeSession->payment_status !== 'paid') {
                \Log::error('paymentSuccess: payment not completed', ['status' => $stripeSession->payment_status]);
                return redirect()->route('frontpage.index')->with('error', 'Payment not completed');
            }

            // Guard against duplicate processing
            $existingPayment = DB::table('payments')
                ->where('stripe_session_id', $sessionId)
                ->first();

            if ($existingPayment) {
                \Log::info('paymentSuccess: duplicate callback ignored', ['session_id' => $sessionId]);
                // Still show confirmation if session data exists
                if (session('payment_success')) {
                    return redirect()->route('reservation.confirmation');
                }
                return redirect()->route('frontpage.index')
                    ->with('error', 'This payment has already been processed. Check your email for confirmation details.');
            }

            // Retrieve reservation data — try cache first, then PHP session
            $cacheKey = $stripeSession->client_reference_id
                        ?? ($stripeSession->metadata['cache_key'] ?? null);

            $reservationData = null;
            if ($cacheKey) {
                $reservationData = Cache::store('file')->get($cacheKey);
                \Log::info('paymentSuccess: cache lookup', [
                    'cache_key' => $cacheKey,
                    'found'     => $reservationData ? 'yes' : 'no',
                ]);
            }

            if (!$reservationData) {
                $reservationData = session('pending_reservation');
                \Log::info('paymentSuccess: fell back to PHP session', [
                    'found' => $reservationData ? 'yes' : 'no',
                ]);
            }

            if (!$reservationData) {
                \Log::error('paymentSuccess: reservation data not found', [
                    'session_id' => $sessionId,
                    'cache_key'  => $cacheKey ?? 'none',
                ]);
                return redirect()->route('frontpage.index')
                    ->with('error', 'Reservation data not found. Your payment was received — please contact us with Stripe session ID: ' . $sessionId);
            }

            DB::beginTransaction();

            $result = $this->createMultiRoomReservation(
                $reservationData,
                'online',
                $sessionId,
                $stripeSession->payment_intent
            );

            DB::commit();

            // Send confirmation email (non-blocking — failure doesn't roll back)
            $this->sendReservationEmail($result);

            // Clear cache + session pending data
            if ($cacheKey) {
                Cache::store('file')->forget($cacheKey);
            }
            session()->forget('pending_reservation');

            // Store confirmation data in session for the confirmation view
            session([
                'temp_credentials' => [
                    'email'           => $result['email'],
                    'password'        => $result['password'],
                    'reservation_ids' => $result['reservation_ids'],
                ],
                'reservation_data' => $result,
                'payment_success'  => true,
                'payment_method'   => 'online',
            ]);
            session()->save();

            return redirect()->route('reservation.confirmation');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Payment success handler error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('frontpage.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function paymentCancel()
    {
        session()->forget('pending_reservation');
        return redirect()->route('frontpage.index')->with('error', 'Payment was cancelled');
    }


    // ─────────────────────────────────────────────────────────────
    // INTERNAL — create reservations for all rooms
    // ─────────────────────────────────────────────────────────────
    private function createMultiRoomReservation(
        array $data,
        string $paymentMethod,
        ?string $stripeSessionId = null,
        ?string $stripePaymentIntent = null
    ): array {
        $roomsPayload = $data['rooms'];
        $roomIds      = array_column($roomsPayload, 'room_id');

        // Verify rooms are still available
        $rooms = DB::table('rooms')
            ->whereIn('room_id', $roomIds)
            ->where('status', 'available')
            ->get();

        if ($rooms->count() !== count($roomIds)) {
            throw new Exception('One or more rooms are no longer available');
        }

        // Check date overlaps per room
        foreach ($roomsPayload as $index => $rd) {
            if ($this->checkDateOverlap($rd['room_id'], $rd['arrival_date'], $rd['departure_date'])) {
                throw new Exception('Room ' . ($index + 1) . ' is already booked for the selected dates');
            }
        }

        // Get room/type info keyed by room_id
        $roomTypes = DB::table('room_types')
            ->join('rooms', 'room_types.room_type_id', '=', 'rooms.room_type_id')
            ->whereIn('rooms.room_id', $roomIds)
            ->select('rooms.room_id', 'rooms.room_number', 'room_types.room_type_name', 'room_types.rate_per_night')
            ->get()
            ->keyBy('room_id');

        // Pricing uses room-0 dates as the "primary" stay
        $primaryArrival   = $roomsPayload[0]['arrival_date'];
        $primaryDeparture = $roomsPayload[0]['departure_date'];
        $primaryNights    = (new \DateTime($primaryArrival))->diff(new \DateTime($primaryDeparture))->days;

        $reservationFeePerRoom = 500;
        $totalReservationFee   = $reservationFeePerRoom * count($roomIds);
        $totalRoomCost         = $roomTypes->sum('rate_per_night');
        $subtotal              = $totalRoomCost * $primaryNights;
        $totalAmount           = $subtotal;
        $balance               = $totalAmount - $totalReservationFee;

        // Create guest user account
        $temporaryPassword = Str::random(12);
        $guestRoleId = DB::table('roles')->where('role_name', 'guest')->value('role_id');
        if (!$guestRoleId) {
            throw new Exception('Guest role not found in database');
        }

        $userId = DB::table('users')->insertGetId([
            'role_id'       => $guestRoleId,
            'email'         => $data['email'],
            'password'      => Hash::make($temporaryPassword),
            'temporary_act' => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Create guest details record
        $guestDetailsId = DB::table('guest_details')->insertGetId([
            'user_id'        => $userId,
            'first_name'     => $data['first_name'],
            'middle_name'    => $data['middle_name'] ?? null,
            'last_name'      => $data['last_name'],
            'contact_number' => $data['contact_number'],
            'dob'            => $data['dob'],
            'arrival_date'   => $primaryArrival,
            'departure_date' => $primaryDeparture,
            'created_at'     => now(),
        ]);

        // Create payment record
        $paymentId = DB::table('payments')->insertGetId([
            'payment_method'        => $paymentMethod,
            'stripe_session_id'     => $stripeSessionId,
            'stripe_payment_intent' => $stripePaymentIntent,
            'paid_at'               => $paymentMethod === 'online' ? now() : null,
            'amount'                => $totalReservationFee,
            'payment_date'          => now(),
            'payment_status'        => $paymentMethod === 'online' ? 'completed' : 'pending',
        ]);

        // Create one reservation row per room
        $reservationIds = [];
        foreach ($roomsPayload as $rd) {
            $roomId        = $rd['room_id'];
            $roomArrival   = $rd['arrival_date'];
            $roomDeparture = $rd['departure_date'];
            $roomNights    = (new \DateTime($roomArrival))->diff(new \DateTime($roomDeparture))->days;
            $roomRate      = $roomTypes[$roomId]->rate_per_night;
            $roomSubtotal  = $roomRate * $roomNights;
            $roomBalance   = $roomSubtotal - $reservationFeePerRoom;
            $guestCount    = (int) ($rd['number_of_guests'] ?? 1);

            $reservationId = DB::table('reservations')->insertGetId([
                'user_id'              => $userId,
                'guest_details_id'     => $guestDetailsId,
                'payment_id'           => $paymentId,
                'room_id'              => $roomId,
                'reservation_fee'      => $reservationFeePerRoom,
                'purpose'              => $rd['special_requests'] ?? null,
                'total_amount'         => $roomSubtotal,
                'balance'              => $roomBalance,
                'reservation_status'   => 'pending',
                'booking_date'         => now()->toDateString(),
                'check_in_date'        => $roomArrival,
                'check_out_date'       => $roomDeparture,
                'adults'               => $guestCount,
                'children'             => 0,
                'no_nights'            => $roomNights,
                'reservation_fee_paid' => $paymentMethod === 'online' ? 1 : 0,
                'created_at'           => now(),
            ]);

            $reservationIds[] = $reservationId;
        }

        // Build room details array for email / session
        $roomDetails = [];
        foreach ($roomsPayload as $rd) {
            $roomDetails[] = [
                'room_number'    => $roomTypes[$rd['room_id']]->room_number,
                'room_type_name' => $roomTypes[$rd['room_id']]->room_type_name,
            ];
        }

        return [
            'reservation_ids'  => $reservationIds,
            'reservation_id'   => $reservationIds[0],
            'email'            => $data['email'],
            'password'         => $temporaryPassword,
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'rooms'            => $roomDetails,
            'room_count'       => count($roomIds),
            'room_number'      => $roomTypes[$roomIds[0]]->room_number,
            'room_type_name'   => $roomTypes[$roomIds[0]]->room_type_name,
            'arrival_date'     => $primaryArrival,
            'departure_date'   => $primaryDeparture,
            'total_amount'     => $totalAmount,
            'balance'          => $balance,
            'no_nights'        => $primaryNights,
            'reservation_fee'  => $totalReservationFee,
        ];
    }


    private function checkDateOverlap($roomId, $arrivalDate, $departureDate): bool
    {
        return DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $roomId)
            ->whereNotIn('reservations.reservation_status', ['cancelled', 'rejected'])
            ->where(function ($query) use ($arrivalDate, $departureDate) {
                $query->whereBetween('guest_details.arrival_date', [$arrivalDate, $departureDate])
                      ->orWhereBetween('guest_details.departure_date', [$arrivalDate, $departureDate])
                      ->orWhere(function ($q) use ($arrivalDate, $departureDate) {
                          $q->where('guest_details.arrival_date', '<=', $arrivalDate)
                            ->where('guest_details.departure_date', '>=', $departureDate);
                      });
            })
            ->exists();
    }


    private function sendReservationEmail(array $data): void
    {
        try {
            \Log::info('Sending reservation email to: ' . $data['email']);

            Mail::send('emails.reservation-confirmation', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['first_name'] . ' ' . $data['last_name'])
                        ->subject('Reservation Confirmation - Hotel De SLSU');
            });

            \Log::info('Reservation email sent successfully to: ' . $data['email']);

        } catch (Exception $e) {
            // Email failure must NOT roll back the reservation — just log it
            \Log::error('Failed to send reservation email: ' . $e->getMessage(), [
                'to'    => $data['email'],
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}