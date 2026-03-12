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
use Stripe\Checkout\Session as StripeSession;
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
        \Log::info('=== createCheckoutSession HIT ===');

        $validated = $request->validate([
            'reservation_data'                                => 'required|array',
            'reservation_data.rooms'                          => 'required|array|min:1',
            'reservation_data.rooms.*.room_id'                => 'required|integer',
            'reservation_data.rooms.*.arrival_date'           => 'required|date',
            'reservation_data.rooms.*.departure_date'         => 'required|date',
            'reservation_data.rooms.*.number_of_guests'       => 'required|integer|min:1',
            'reservation_data.rooms.*.special_requests'       => 'nullable|string',
            'reservation_data.rooms.*.guests'                 => 'nullable|array',
            'reservation_data.rooms.*.guests.*.first_name'    => 'nullable|string|max:45',
            'reservation_data.rooms.*.guests.*.last_name'     => 'nullable|string|max:45',
            'reservation_data.rooms.*.guests.*.email'         => 'nullable|email|max:100',
            'reservation_data.rooms.*.guests.*.phone'         => 'nullable|string|max:45',
            'reservation_data.rooms.*.guests.*.address'       => 'nullable|string|max:255',
            'reservation_data.rooms.*.guests.*.arrival_time'  => 'nullable|string',
            'reservation_data.first_name'                     => 'required|string|max:45',
            'reservation_data.middle_name'                    => 'nullable|string|max:45',
            'reservation_data.last_name'                      => 'required|string|max:45',
            'reservation_data.email'                          => 'required|email|max:100',
            'reservation_data.contact_number'                 => 'required|string|max:45',
            'reservation_data.dob'                            => 'required|date|before:today',
            'amount'                                          => 'required|numeric|min:500',
        ]);

        \Log::info('createCheckoutSession - validation passed', [
            'email'      => $validated['reservation_data']['email'],
            'room_count' => count($validated['reservation_data']['rooms']),
        ]);

        try {
            $data             = $validated['reservation_data'];
            $roomCount        = count($data['rooms']);
            $primaryArrival   = $data['rooms'][0]['arrival_date'];
            $primaryDeparture = $data['rooms'][0]['departure_date'];

            $cacheKey = 'res_' . Str::uuid()->toString();
            Cache::store('file')->put($cacheKey, $data, now()->addHours(2));
            \Log::info('createCheckoutSession - cached', ['key' => $cacheKey]);

            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'client_reference_id'  => $cacheKey,
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => 'php',
                        'product_data' => [
                            'name'        => 'Hotel Reservation Fee',
                            'description' => "$roomCount room(s) · $primaryArrival → $primaryDeparture",
                        ],
                        'unit_amount'  => (int) round($validated['amount'] * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'           => 'payment',
                'success_url'    => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('payment.cancel'),
                'customer_email' => $data['email'],
                'metadata'       => [
                    'cache_key' => $cacheKey,
                    'email'     => $data['email'],
                ],
            ]);

            \Log::info('createCheckoutSession - Stripe session created', ['id' => $checkoutSession->id]);

            return response()->json(['id' => $checkoutSession->id, 'url' => $checkoutSession->url]);

        } catch (Exception $e) {
            \Log::error('createCheckoutSession error: ' . $e->getMessage());
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
        $stripeSessionId = $request->query('session_id');
        \Log::info('=== paymentSuccess HIT ===', ['stripe_session_id' => $stripeSessionId]);

        if (!$stripeSessionId) {
            return redirect()->route('frontpage.index')->with('error', 'Invalid payment session.');
        }

        try {
            // ── 1. Verify with Stripe ─────────────────────────────
            $stripeSession = StripeSession::retrieve($stripeSessionId);
            \Log::info('paymentSuccess - Stripe status', [
                'payment_status'      => $stripeSession->payment_status,
                'client_reference_id' => $stripeSession->client_reference_id,
            ]);

            if ($stripeSession->payment_status !== 'paid') {
                return redirect()->route('frontpage.index')->with('error', 'Payment not completed.');
            }

            // ── 2. Idempotency: check if already processed ────────
            $alreadyProcessed = DB::table('payments')
                ->where('stripe_session_id', $stripeSessionId)
                ->exists();

            if ($alreadyProcessed) {
                \Log::info('paymentSuccess - already processed', ['stripe_session_id' => $stripeSessionId]);
                return redirect()->route('frontpage.index')
                    ->with('success', 'Your reservation is already confirmed. Check your email.');
            }

            // ── 3. Retrieve cached reservation data ───────────────
            $cacheKey = $stripeSession->client_reference_id
                     ?? ($stripeSession->metadata['cache_key'] ?? null);

            $reservationData = $cacheKey ? Cache::store('file')->get($cacheKey) : null;
            \Log::info('paymentSuccess - cache', [
                'key'   => $cacheKey,
                'found' => $reservationData ? 'YES' : 'NO',
            ]);

            if (!$reservationData) {
                \Log::error('paymentSuccess - data not found', ['stripe_id' => $stripeSessionId]);
                return redirect()->route('frontpage.index')
                    ->with('error', "Payment received but reservation data lost. Contact us with Stripe session: $stripeSessionId");
            }

            // ── 4. Build reservations ─────────────────────────────
            DB::beginTransaction();
            $result = $this->buildReservations($reservationData, 'online', $stripeSessionId);
            DB::commit();

            \Log::info('paymentSuccess - committed', ['reservation_ids' => $result['reservation_ids']]);

            // ── 5. Confirmation email ─────────────────────────────
            $this->sendEmail($result);

            // ── 6. Clear cache ────────────────────────────────────
            if ($cacheKey) Cache::store('file')->forget($cacheKey);

            // ── 7. Redirect with signed URL ───────────────────────
            $idsStr = implode(',', $result['reservation_ids']);
            $token  = hash_hmac('sha256', $result['user_id'] . '|' . $idsStr, config('app.key'));

            return redirect()->route('reservation.confirmation', [
                'uid'   => $result['user_id'],
                'rids'  => $idsStr,
                'token' => $token,
                'pm'    => 'online',
                'pw'    => $result['password'],
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('paymentSuccess exception: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('frontpage.index')
                ->with('error', 'Reservation processing failed: ' . $e->getMessage());
        }
    }

    public function paymentCancel()
    {
        return redirect()->route('frontpage.index')->with('error', 'Payment was cancelled.');
    }

    // ─────────────────────────────────────────────────────────────
    // BUILD RESERVATIONS (shared: cash & online)
    //
    // GUEST DETAILS LOGIC:
    //   Section 2 (Account): account holder → users table ONLY.
    //   Section 3 (Details): ALL guests per room → guest_details.
    //     - guests[0] per room: is_primary = 1 (lead guest)
    //     - guests[1..n] per room: is_primary = 0 (additional)
    //   reservations.guest_details_id → guests[0] of that room.
    // ─────────────────────────────────────────────────────────────
    private function buildReservations(array $data, string $paymentMethod, ?string $stripeSessionId = null): array
    {
        // Re-index rooms to ensure 0-based sequential keys
        $roomsPayload = array_values($data['rooms']);
        $roomIds      = array_column($roomsPayload, 'room_id');

        // ── Availability check ────────────────────────────────────
        $availableIds = DB::table('rooms')
            ->whereIn('room_id', $roomIds)
            ->where('status', 'available')
            ->pluck('room_id')
            ->toArray();

        foreach ($roomIds as $roomId) {
            if (!in_array($roomId, $availableIds)) {
                throw new Exception("Room $roomId is no longer available.");
            }
        }

        // ── Date overlap check ────────────────────────────────────
        foreach ($roomsPayload as $i => $rd) {
            if ($this->checkDateOverlap($rd['room_id'], $rd['arrival_date'], $rd['departure_date'])) {
                throw new Exception('Room ' . ($i + 1) . ' is already booked for those dates.');
            }
        }

        // ── Pricing ───────────────────────────────────────────────
        $roomTypes = DB::table('room_types')
            ->join('rooms', 'room_types.room_type_id', '=', 'rooms.room_type_id')
            ->whereIn('rooms.room_id', $roomIds)
            ->select('rooms.room_id', 'rooms.room_number', 'room_types.room_type_name', 'room_types.rate_per_night')
            ->get()
            ->keyBy('room_id');

        $feePerRoom       = 500;
        $totalFee         = $feePerRoom * count($roomIds);
        $primaryArrival   = $roomsPayload[0]['arrival_date'];
        $primaryDeparture = $roomsPayload[0]['departure_date'];
        $primaryNights    = (int)(new \DateTime($primaryArrival))->diff(new \DateTime($primaryDeparture))->days;

        // ── Duplicate email guard ─────────────────────────────────
        if (DB::table('users')->where('email', $data['email'])->exists()) {
            throw new Exception('An account with this email already exists.');
        }

        // ── Create user (account holder only) ─────────────────────
        $tempPassword = Str::random(12);
        $guestRoleId  = DB::table('roles')->where('role_name', 'guest')->value('role_id');
        if (!$guestRoleId) throw new Exception('Guest role not found.');

        $userId = DB::table('users')->insertGetId([
            'role_id'       => $guestRoleId,
            'email'         => $data['email'],
            'password'      => Hash::make($tempPassword),
            'first_name'    => $data['first_name'],
            'middle_name'   => $data['middle_name'] ?? null,
            'last_name'     => $data['last_name'],
            'temporary_act' => 1,
            'STATUS'        => 'active',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // ── Payment record ────────────────────────────────────────
        $paymentId = DB::table('payments')->insertGetId([
            'payment_method'        => $paymentMethod,
            'amount'                => $totalFee,
            'payment_date'          => now(),
            'payment_status'        => $paymentMethod === 'online' ? 'completed' : 'pending',
            'stripe_session_id'     => $stripeSessionId,
            'stripe_payment_intent' => null,
            'paid_at'               => $paymentMethod === 'online' ? now() : null,
        ]);

        // ── Reservations + guests per room ────────────────────────
        $reservationIds = [];
        $totalSubtotal  = 0;

        foreach ($roomsPayload as $rd) {
            $roomId   = $rd['room_id'];
            $nights   = (int)(new \DateTime($rd['arrival_date']))->diff(new \DateTime($rd['departure_date']))->days;
            $rate     = $roomTypes[$roomId]->rate_per_night;
            $subtotal = $rate * $nights;
            $balance  = $subtotal - $feePerRoom;
            $totalSubtotal += $subtotal;

            // a) Insert reservation — guest_details_id is null until we create guests
            $resId = DB::table('reservations')->insertGetId([
                'user_id'              => $userId,
                'guest_details_id'     => null,  // updated below
                'payment_id'           => $paymentId,
                'room_id'              => $roomId,
                'reservation_fee'      => $feePerRoom,
                'purpose'              => $rd['special_requests'] ?? null,
                'total_amount'         => $subtotal,
                'balance'              => $balance,
                'reservation_status'   => 'pending',
                'booking_date'         => now()->toDateString(),
                'check_in_date'        => $rd['arrival_date'],
                'check_out_date'       => $rd['departure_date'],
                'adults'               => (int)($rd['number_of_guests'] ?? 1),
                'children'             => 0,
                'no_nights'            => $nights,
                'reservation_fee_paid' => $paymentMethod === 'online' ? 1 : 0,
                'created_at'           => now(),
            ]);

            $reservationIds[] = $resId;

            // b) Insert all guests for this room
            $leadGuestId = null;
            foreach (($rd['guests'] ?? []) as $gIdx => $guest) {
                if (empty($guest['first_name']) || empty($guest['last_name'])) continue;

                $newGuestId = DB::table('guest_details')->insertGetId([
                    'user_id'        => $userId,
                    'reservation_id' => $resId,
                    'first_name'     => $guest['first_name'],
                    'middle_name'    => $guest['middle_name'] ?? null,
                    'last_name'      => $guest['last_name'],
                    'contact_number' => $guest['phone'] ?? null,
                    'dob'            => null,
                    'arrival_date'   => $rd['arrival_date'],
                    'departure_date' => $rd['departure_date'],
                    'is_primary'     => $gIdx === 0 ? 1 : 0,
                    'created_at'     => now(),
                ]);

                if ($gIdx === 0) {
                    $leadGuestId = $newGuestId;
                }
            }

            // c) Point reservation at the lead guest of this room
            if ($leadGuestId) {
                DB::table('reservations')
                    ->where('reservation_id', $resId)
                    ->update(['guest_details_id' => $leadGuestId]);
            }
        }

        $roomDetails = array_map(fn($id) => [
            'room_number'    => $roomTypes[$id]->room_number,
            'room_type_name' => $roomTypes[$id]->room_type_name,
        ], $roomIds);

        return [
            'user_id'         => $userId,
            'reservation_ids' => $reservationIds,
            'email'           => $data['email'],
            'password'        => $tempPassword,
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'rooms'           => $roomDetails,
            'room_count'      => count($roomIds),
            'arrival_date'    => $primaryArrival,
            'departure_date'  => $primaryDeparture,
            'no_nights'       => $primaryNights,
            'total_amount'    => $totalSubtotal,
            'reservation_fee' => $totalFee,
            'balance'         => $totalSubtotal - $totalFee,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────
    private function checkDateOverlap($roomId, $arrivalDate, $departureDate): bool
    {
        return DB::table('reservations')
            ->where('room_id', $roomId)
            ->whereNotIn('reservation_status', ['cancelled', 'rejected'])
            ->where('check_in_date', '<', $departureDate)
            ->where('check_out_date', '>', $arrivalDate)
            ->exists();
    }

    private function sendEmail(array $data): void
    {
        try {
            Mail::send('emails.reservation-confirmation', $data, function ($m) use ($data) {
                $m->to($data['email'], ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))
                ->subject('Reservation Confirmation - Hotel De SLSU');
            });
            \Log::info('Email sent successfully to: ' . $data['email']);
        } catch (Exception $e) {
            \Log::error('Email failed: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}