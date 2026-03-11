<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;

class ReservationController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // SHOW FORM
    // ─────────────────────────────────────────────────────────────
    public function showReservationForm($room_id)
    {
        try {
            $roomData = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.room_id', $room_id)
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id', 'rooms.room_type_id', 'rooms.room_number',
                    'rooms.image_path', 'rooms.status',
                    'room_types.room_type_name', 'room_types.description',
                    'room_types.rate_per_night', 'room_types.max_pax'
                )
                ->first();

            if (!$roomData) {
                return redirect()->route('frontpage.index')
                    ->with('error', 'Room not found or not available');
            }

            $room = (object) [
                'room_id'      => $roomData->room_id,
                'room_type_id' => $roomData->room_type_id,
                'room_number'  => $roomData->room_number,
                'image_path'   => $roomData->image_path,
                'status'       => $roomData->status,
                'roomType'     => (object) [
                    'room_type_name' => $roomData->room_type_name,
                    'description'    => $roomData->description,
                    'rate_per_night' => $roomData->rate_per_night,
                    'max_pax'        => $roomData->max_pax,
                ],
            ];

            return view('content.reservation.reservation-form', compact('room'));

        } catch (Exception $e) {
            \Log::error('showReservationForm: ' . $e->getMessage());
            return redirect()->route('frontpage.index')
                ->with('error', 'Unable to load reservation form');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET AVAILABLE ROOMS (AJAX)
    // ─────────────────────────────────────────────────────────────
    public function getAvailableRooms(Request $request)
    {
        try {
            $validated = $request->validate([
                'arrival_date'     => 'required|date',
                'departure_date'   => 'required|date|after:arrival_date',
                'exclude_room_ids' => 'array',
            ]);

            $query = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id', 'rooms.room_number', 'rooms.image_path',
                    'room_types.room_type_name', 'room_types.description',
                    'room_types.rate_per_night', 'room_types.max_pax'
                );

            if (!empty($validated['exclude_room_ids'])) {
                $query->whereNotIn('rooms.room_id', $validated['exclude_room_ids']);
            }

            $rooms = $query->get()
                ->filter(fn($room) => !$this->checkDateOverlap(
                    $room->room_id,
                    $validated['arrival_date'],
                    $validated['departure_date']
                ))
                ->values()
                ->map(function ($room) {
                    $room->image_path = asset('storage/' . $room->image_path);
                    return $room;
                });

            return response()->json(['success' => true, 'rooms' => $rooms]);

        } catch (Exception $e) {
            \Log::error('getAvailableRooms: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to fetch available rooms'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // STORE — cash reservations
    //
    // GUEST DETAILS LOGIC:
    //   Section 2 (Account): first_name/last_name/email/etc → users table ONLY.
    //   Section 3 (Details): guests[0..n] per room → ALL go into guest_details.
    //     - guests[0] is the lead guest for that room (is_primary = 1).
    //     - guests[1..n] are additional guests (is_primary = 0).
    //   reservations.guest_details_id → points to guests[0] of that room.
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        \Log::info('=== STORE HIT ===', [
            'rooms_raw'       => $request->input('rooms'),
            'email'           => $request->input('email'),
            'payment_method'  => $request->input('payment_method'),
            'rooms_data_keys' => array_keys($request->input('rooms_data', [])),
        ]);

        // ── 1. Top-level validation ───────────────────────────────
        $validated = $request->validate([
            'rooms'          => 'required|json',
            'rooms_data'     => 'required|array|min:1',
            'first_name'     => 'required|string|max:45',
            'middle_name'    => 'nullable|string|max:45',
            'last_name'      => 'required|string|max:45',
            'email'          => 'required|email|max:100',
            'contact_number' => 'required|string|max:45',
            'dob'            => 'required|date|before:today',
            'payment_method' => 'required|in:cash,online',
        ]);

        // ── 2. Per-room validation ────────────────────────────────
        $roomsData = $request->input('rooms_data', []);
        foreach ($roomsData as $idx => $rd) {
            $n = $idx + 1;
            if (empty($rd['arrival_date']))
                return back()->withInput()->with('error', "Room $n: check-in date is required.");
            if (empty($rd['departure_date']))
                return back()->withInput()->with('error', "Room $n: check-out date is required.");
            if (strtotime($rd['departure_date']) <= strtotime($rd['arrival_date']))
                return back()->withInput()->with('error', "Room $n: check-out must be after check-in.");
            if (empty($rd['number_of_guests']) || (int)$rd['number_of_guests'] < 1)
                return back()->withInput()->with('error', "Room $n: number of guests is required.");
            // guests[0] is the lead guest for this room — name required
            if (empty($rd['guests'][0]['first_name']) || empty($rd['guests'][0]['last_name']))
                return back()->withInput()->with('error', "Room $n: lead guest name is required.");
        }

        // ── 3. Decode rooms JSON ──────────────────────────────────
        $roomsList = json_decode($validated['rooms'], true);
        if (empty($roomsList)) {
            return back()->withInput()->with('error', 'No rooms selected. Please go back and select a room.');
        }

        $roomIds = array_column($roomsList, 'room_id');

        if (count($roomIds) !== count($roomsData)) {
            return back()->withInput()->with('error', 'Room data mismatch. Please refresh and try again.');
        }

        DB::beginTransaction();

        try {
            // ── 4. Rooms available ────────────────────────────────
            $availableRoomIds = DB::table('rooms')
                ->whereIn('room_id', $roomIds)
                ->where('status', 'available')
                ->pluck('room_id')
                ->toArray();

            foreach ($roomIds as $roomId) {
                if (!in_array($roomId, $availableRoomIds)) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Room $roomId is no longer available.");
                }
            }

            // ── 5. Date overlap per room ──────────────────────────
            foreach ($roomIds as $i => $roomId) {
                if ($this->checkDateOverlap($roomId, $roomsData[$i]['arrival_date'], $roomsData[$i]['departure_date'])) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Room ' . ($i + 1) . ' is already booked for those dates.');
                }
            }

            // ── 6. Load pricing ───────────────────────────────────
            $roomTypes = DB::table('room_types')
                ->join('rooms', 'room_types.room_type_id', '=', 'rooms.room_type_id')
                ->whereIn('rooms.room_id', $roomIds)
                ->select('rooms.room_id', 'rooms.room_number', 'room_types.room_type_name', 'room_types.rate_per_night')
                ->get()
                ->keyBy('room_id');

            // ── 7. Duplicate email guard ──────────────────────────
            if (DB::table('users')->where('email', $validated['email'])->exists()) {
                DB::rollBack();
                return back()->withInput()->with('error', 'An account with this email already exists. Please use a different email.');
            }

            // ── 8. Fees & dates ───────────────────────────────────
            $feePerRoom       = 500;
            $totalFee         = $feePerRoom * count($roomIds);
            $primaryArrival   = $roomsData[0]['arrival_date'];
            $primaryDeparture = $roomsData[0]['departure_date'];
            $primaryNights    = (int)(new \DateTime($primaryArrival))->diff(new \DateTime($primaryDeparture))->days;

            // ── 9. Create user (account holder only — NO guest_details row) ──
            $tempPassword = Str::random(12);
            $guestRoleId  = DB::table('roles')->where('role_name', 'guest')->value('role_id');
            if (!$guestRoleId) throw new Exception('Guest role not found in the roles table.');

            $userId = DB::table('users')->insertGetId([
                'role_id'       => $guestRoleId,
                'email'         => $validated['email'],
                'password'      => Hash::make($tempPassword),
                'temporary_act' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            \Log::info('STORE - user created', ['user_id' => $userId]);

            // ── 10. Payment record ────────────────────────────────
            $paymentId = DB::table('payments')->insertGetId([
                'payment_method' => 'cash',
                'amount'         => $totalFee,
                'payment_date'   => now(),
                'payment_status' => 'pending',
            ]);

            // ── 11. Reservations + guests per room ────────────────
            // For each room:
            //   a) Insert reservation first (guest_details_id = null temporarily)
            //   b) Insert all guests for this room, linking reservation_id
            //   c) Update reservation.guest_details_id to guests[0] (lead guest)
            $reservationIds = [];
            $totalSubtotal  = 0;

            foreach ($roomIds as $i => $roomId) {
                $rd      = $roomsData[$i];
                $nights  = (int)(new \DateTime($rd['arrival_date']))->diff(new \DateTime($rd['departure_date']))->days;
                $rate    = $roomTypes[$roomId]->rate_per_night;
                $subtotal = $rate * $nights;
                $balance  = $subtotal - $feePerRoom;
                $totalSubtotal += $subtotal;

                // a) Insert reservation with guest_details_id = null for now
                $resId = DB::table('reservations')->insertGetId([
                    'user_id'              => $userId,
                    'guest_details_id'     => null,
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
                    'adults'               => (int)$rd['number_of_guests'],
                    'children'             => 0,
                    'no_nights'            => $nights,
                    'reservation_fee_paid' => 0,
                    'created_at'           => now(),
                ]);

                $reservationIds[] = $resId;
                \Log::info("STORE - reservation $resId created for room $roomId");

                // b) Insert ALL guests for this room (guests[0] = lead, is_primary = 1)
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

                    // Capture lead guest ID from first guest in list
                    if ($gIdx === 0) {
                        $leadGuestId = $newGuestId;
                    }
                }

                // c) Update reservation to point to the lead guest
                if ($leadGuestId) {
                    DB::table('reservations')
                        ->where('reservation_id', $resId)
                        ->update(['guest_details_id' => $leadGuestId]);
                }
            }

            DB::commit();
            \Log::info('STORE - committed', ['reservation_ids' => $reservationIds, 'user_id' => $userId]);

            // ── 12. Email ─────────────────────────────────────────
            $roomDetails = collect($roomIds)->map(fn($id) => [
                'room_number'    => $roomTypes[$id]->room_number,
                'room_type_name' => $roomTypes[$id]->room_type_name,
            ])->toArray();

            $this->sendEmail([
                'first_name'      => $validated['first_name'],
                'last_name'       => $validated['last_name'],
                'email'           => $validated['email'],
                'password'        => $tempPassword,
                'rooms'           => $roomDetails,
                'room_count'      => count($roomIds),
                'arrival_date'    => $primaryArrival,
                'departure_date'  => $primaryDeparture,
                'no_nights'       => $primaryNights,
                'total_amount'    => $totalSubtotal,
                'reservation_fee' => $totalFee,
                'balance'         => $totalSubtotal - $totalFee,
            ]);

            // ── 13. Redirect ──────────────────────────────────────
            $idsStr = implode(',', $reservationIds);
            $token  = hash_hmac('sha256', $userId . '|' . $idsStr, config('app.key'));

            return redirect()->route('reservation.confirmation', [
                'uid'   => $userId,
                'rids'  => $idsStr,
                'token' => $token,
                'pm'    => 'cash',
                'pw'    => $tempPassword,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('STORE - exception: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()
                ->with('error', 'Failed to create reservation: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // CONFIRMATION
    // ─────────────────────────────────────────────────────────────
    public function confirmation(Request $request)
    {
        $uid   = $request->query('uid');
        $rids  = $request->query('rids');
        $token = $request->query('token');
        $pm    = $request->query('pm', 'cash');
        $pw    = $request->query('pw');

        if (empty($uid) || empty($rids) || empty($token)) {
            return redirect()->route('frontpage.index')
                ->with('error', 'Invalid confirmation link. Please check your email.');
        }

        $expected = hash_hmac('sha256', $uid . '|' . $rids, config('app.key'));
        if (!hash_equals($expected, $token)) {
            return redirect()->route('frontpage.index')
                ->with('error', 'Invalid confirmation link.');
        }

        $reservationIds = array_map('intval', explode(',', $rids));

        $reservations = DB::table('reservations')
            ->join('rooms',      'reservations.room_id',    '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id',      '=', 'room_types.room_type_id')
            ->join('payments',   'reservations.payment_id', '=', 'payments.payment_id')
            ->whereIn('reservations.reservation_id', $reservationIds)
            ->where('reservations.user_id', $uid)
            ->select(
                'reservations.reservation_id',
                'reservations.check_in_date',
                'reservations.check_out_date',
                'reservations.no_nights',
                'reservations.total_amount',
                'reservations.balance',
                'reservations.reservation_fee',
                'rooms.room_number',
                'room_types.room_type_name',
                'payments.payment_method',
                'payments.payment_status'
            )
            ->get();

        if ($reservations->isEmpty()) {
            return redirect()->route('frontpage.index')
                ->with('error', 'Reservation not found. Please check your email.');
        }

        $user  = DB::table('users')->where('user_id', $uid)->first();
        $first = $reservations->first();

        // Lead guest of the first reservation (for display name)
        $leadGuest = DB::table('guest_details')
            ->where('reservation_id', $first->reservation_id)
            ->where('is_primary', 1)
            ->first();

        $reservationData = [
            'reservation_ids' => $reservationIds,
            'reservation_id'  => $first->reservation_id,
            'rooms'           => $reservations->map(fn($r) => [
                'room_number'    => $r->room_number,
                'room_type_name' => $r->room_type_name,
            ])->toArray(),
            'room_count'      => $reservations->count(),
            'room_number'     => $first->room_number,
            'room_type_name'  => $first->room_type_name,
            'arrival_date'    => $first->check_in_date,
            'departure_date'  => $first->check_out_date,
            'no_nights'       => $first->no_nights,
            'total_amount'    => $reservations->sum('total_amount'),
            'reservation_fee' => $reservations->sum('reservation_fee'),
            'balance'         => $reservations->sum('balance'),
            'first_name'      => $leadGuest->first_name ?? '',
            'last_name'       => $leadGuest->last_name  ?? '',
        ];

        $credentials = [
            'email'    => $user->email ?? '',
            'password' => $pw ?? '(check your email)',
        ];

        $paymentMethod  = $pm;
        $paymentSuccess = true;

        return view('content.reservation.confirmation', compact(
            'credentials', 'reservationData', 'paymentSuccess', 'paymentMethod'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // CHECK AVAILABILITY (AJAX)
    // ─────────────────────────────────────────────────────────────
    public function checkAvailability(Request $request)
    {
        $v = $request->validate([
            'room_id'        => 'required|exists:rooms,room_id',
            'arrival_date'   => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date',
        ]);

        $overlap = $this->checkDateOverlap($v['room_id'], $v['arrival_date'], $v['departure_date']);

        return response()->json([
            'available' => !$overlap,
            'message'   => $overlap ? 'Room is already booked for selected dates' : 'Room is available',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET BOOKED DATES (calendar)
    // ─────────────────────────────────────────────────────────────
    public function getBookedDates($room_id)
    {
        $rows = DB::table('reservations')
            ->where('room_id', $room_id)
            ->whereNotIn('reservation_status', ['cancelled', 'rejected'])
            ->select('check_in_date as start', 'check_out_date as end', 'reservation_status as status')
            ->get();

        return response()->json($rows->map(fn($r) => [
            'start'           => $r->start,
            'end'             => $r->end,
            'title'           => ucfirst($r->status),
            'backgroundColor' => $this->statusColor($r->status),
            'borderColor'     => $this->statusColor($r->status),
            'display'         => 'background',
        ]));
    }

    // ─────────────────────────────────────────────────────────────
    // CANCEL
    // ─────────────────────────────────────────────────────────────
    public function cancel(Request $request, $reservation_id)
    {
        DB::beginTransaction();
        try {
            $res = DB::table('reservations')->where('reservation_id', $reservation_id)->first();
            if (!$res) return back()->with('error', 'Reservation not found');

            DB::table('reservations')
                ->where('reservation_id', $reservation_id)
                ->update(['reservation_status' => 'cancelled']);

            $otherActive = DB::table('reservations')
                ->where('user_id', $res->user_id)
                ->where('reservation_id', '!=', $reservation_id)
                ->whereNotIn('reservation_status', ['cancelled', 'rejected'])
                ->exists();

            if (!$otherActive) {
                DB::table('users')
                    ->where('user_id', $res->user_id)
                    ->where('temporary_act', true)
                    ->delete();
            }

            DB::commit();
            return redirect()->route('frontpage.index')
                ->with('success', 'Reservation cancelled successfully');
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('cancel: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel reservation');
        }
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
        } catch (Exception $e) {
            \Log::error('Email failed: ' . $e->getMessage());
        }
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'pending'   => '#fbbf24',
            'approved'  => '#10b981',
            'rejected'  => '#ef4444',
            'cancelled' => '#6b7280',
            default     => '#3b82f6',
        };
    }
}