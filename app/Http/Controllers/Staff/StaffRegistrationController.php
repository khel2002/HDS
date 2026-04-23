<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StaffRegistrationController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // CHECK-IN PAGE
    // ─────────────────────────────────────────────────────────────
    public function checkIn()
    {
        $reservations = DB::table('reservations')
            ->join('users',       'reservations.user_id',   '=', 'users.user_id')
            ->join('rooms',       'reservations.room_id',   '=', 'rooms.room_id')
            ->join('room_types',  'rooms.room_type_id',     '=', 'room_types.room_type_id')
            ->join('payments',    'reservations.payment_id','=', 'payments.payment_id')
            ->leftJoin('guest_details', function ($join) {
                $join->on('guest_details.reservation_id', '=', 'reservations.reservation_id')
                     ->where('guest_details.is_primary', '=', 1);
            })
            ->leftJoin('registrations', 'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->where('reservations.reservation_status', 'approved')
            ->whereNull('registrations.registration_id')
            ->select(
                'reservations.reservation_id',
                'reservations.check_in_date',
                'reservations.check_out_date',
                'reservations.no_nights',
                'reservations.adults',
                'reservations.total_amount',
                'reservations.balance',
                'reservations.reservation_fee',
                'reservations.reservation_fee_paid',
                'reservations.payment_id',
                'reservations.purpose',
                'rooms.room_number',
                'rooms.room_id',
                'room_types.room_type_name',
                'payments.payment_method',
                'payments.payment_status',
                'users.email',
                'users.user_id',
                DB::raw("COALESCE(guest_details.first_name, '') as guest_first_name"),
                DB::raw("COALESCE(guest_details.last_name, '')  as guest_last_name"),
                DB::raw("COALESCE(guest_details.contact_number, '') as guest_contact"),
            )
            ->orderBy('reservations.check_in_date', 'asc')
            ->get();

        return view('content.staff.registration.check-in', compact('reservations'));
    }

    // ─────────────────────────────────────────────────────────────
    // PROCESS CHECK-IN (AJAX)
    // ─────────────────────────────────────────────────────────────
    public function processCheckIn(Request $request, $reservation_id)
    {
        DB::beginTransaction();
        try {
            $res = DB::table('reservations')
                ->where('reservation_id', $reservation_id)
                ->where('reservation_status', 'approved')
                ->first();

            if (!$res) {
                return response()->json(['success' => false, 'message' => 'Reservation not found or not approved.'], 404);
            }

            $alreadyCheckedIn = DB::table('registrations')
                ->where('reservation_id', $reservation_id)
                ->exists();

            if ($alreadyCheckedIn) {
                return response()->json(['success' => false, 'message' => 'This reservation has already been checked in.'], 422);
            }

            $leadGuest = DB::table('guest_details')
                ->where('reservation_id', $reservation_id)
                ->where('is_primary', 1)
                ->first();

            if (!$leadGuest) {
                return response()->json(['success' => false, 'message' => 'Guest details not found for this reservation.'], 422);
            }

            DB::table('registrations')->insert([
                'reservation_id'   => $res->reservation_id,
                'guest_details_id' => $leadGuest->guest_details_id,
                'user_id'          => $res->user_id,
                'payment_id'       => $res->payment_id,
                'check_in_at'      => now(),
                'check_out_date'   => null,
                'created_at'       => now(),
            ]);

            if ($res->reservation_fee_paid == 0) {
                DB::table('reservations')
                    ->where('reservation_id', $reservation_id)
                    ->update(['reservation_fee_paid' => 1]);

                DB::table('payments')
                    ->where('payment_id', $res->payment_id)
                    ->where('payment_method', 'cash')
                    ->update([
                        'payment_status' => 'completed',
                        'paid_at'        => now(),
                    ]);
            }

            DB::commit();

            $stats = $this->buildCheckInStats();

            return response()->json([
                'success'        => true,
                'message'        => 'Guest checked in successfully.',
                'reservation_id' => (int) $reservation_id,
                'stats'          => $stats,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('processCheckIn error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Check-in failed: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // CHECK-OUT PAGE
    // ─────────────────────────────────────────────────────────────
    public function checkOut()
    {
        $registrations = DB::table('registrations')
            ->join('reservations',  'registrations.reservation_id',   '=', 'reservations.reservation_id')
            ->join('users',         'registrations.user_id',          '=', 'users.user_id')
            ->join('rooms',         'reservations.room_id',           '=', 'rooms.room_id')
            ->join('room_types',    'rooms.room_type_id',             '=', 'room_types.room_type_id')
            ->join('payments',      'registrations.payment_id',       '=', 'payments.payment_id')
            ->leftJoin('guest_details as gd', function ($join) {
                $join->on('gd.reservation_id', '=', 'reservations.reservation_id')
                     ->where('gd.is_primary', '=', 1);
            })
            ->leftJoinSub(
                DB::table('checkout_requests')
                    ->select('registration_id', 'status')
                    ->whereIn('id', function ($sub) {
                        $sub->selectRaw('MAX(id)')
                            ->from('checkout_requests')
                            ->groupBy('registration_id');
                    }),
                'cr',
                'cr.registration_id',
                '=',
                'registrations.registration_id'
            )
            ->whereNull('registrations.check_out_date')
            ->select(
                'registrations.registration_id',
                'registrations.check_in_at',
                'registrations.check_out_date',
                'reservations.reservation_id',
                'reservations.check_in_date',
                'reservations.check_out_date as expected_checkout',
                'reservations.no_nights',
                'reservations.total_amount',
                'reservations.balance',
                'reservations.adults',
                'rooms.room_number',
                'rooms.room_id',
                'room_types.room_type_name',
                'payments.payment_method',
                'payments.payment_status',
                'users.email',
                'users.user_id',
                DB::raw("COALESCE(cr.status, 'none') as inspection_status"),
                DB::raw("COALESCE(gd.first_name, '') as guest_first_name"),
                DB::raw("COALESCE(gd.last_name, '')  as guest_last_name"),
                DB::raw("COALESCE(gd.contact_number, '') as guest_contact"),
            )
            ->orderBy('reservations.check_out_date', 'asc')
            ->get();

        return view('content.staff.registration.check-out', compact('registrations'));
    }

    // ─────────────────────────────────────────────────────────────
    // PROCESS CHECK-OUT (AJAX)
    // Guards:
    //   1. Checkout request must exist and be 'cleared'
    //   2. All damage charges must be acknowledged
    //   3. Reservation balance must be 0
    // ─────────────────────────────────────────────────────────────
    public function processCheckOut(Request $request, $registration_id)
    {
        DB::beginTransaction();
        try {
            $reg = DB::table('registrations')
                ->where('registration_id', $registration_id)
                ->whereNull('check_out_date')
                ->first();

            if (!$reg) {
                return response()->json(['success' => false, 'message' => 'Registration not found or already checked out.'], 404);
            }

            $res = DB::table('reservations')
                ->where('reservation_id', $reg->reservation_id)
                ->first();

            if (!$res) {
                return response()->json(['success' => false, 'message' => 'Reservation not found.'], 404);
            }

            // [GATE 1] Room inspection must be cleared
            $checkoutRequest = DB::table('checkout_requests')
                ->where('registration_id', $registration_id)
                ->orderByDesc('created_at')
                ->first();

            if (! $checkoutRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout request found. The guest must submit a checkout request first.',
                ], 422);
            }

            if ($checkoutRequest->status !== 'cleared') {
                $statusLabels = [
                    'pending'    => 'pending inspection',
                    'inspecting' => 'currently being inspected',
                    'has_issues' => 'has unresolved issues',
                ];
                $label = $statusLabels[$checkoutRequest->status] ?? $checkoutRequest->status;
                return response()->json([
                    'success' => false,
                    'message' => "Cannot check out: room inspection is {$label}. Complete the inspection first.",
                ], 422);
            }

            // [GATE 2] All damage charges must be acknowledged by the guest
            $unacknowledged = DB::table('checkout_damage_charges')
                ->where('registration_id', $registration_id)
                ->where('is_acknowledged', 0)
                ->count();

            if ($unacknowledged > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Guest has {$unacknowledged} unacknowledged damage charge(s). Wait for the guest to acknowledge before checking out.",
                ], 422);
            }

            // [GATE 3] Balance must be fully paid
            if ($res->balance > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest still has an outstanding balance of ₱' . number_format($res->balance, 2) . '. Collect full payment before checking out.',
                ], 422);
            }

            // All gates passed — process checkout
            DB::table('registrations')
                ->where('registration_id', $registration_id)
                ->update(['check_out_date' => now()->toDateString()]);

            DB::table('reservations')
                ->where('reservation_id', $reg->reservation_id)
                ->update(['balance' => 0]);

            // Free the room
            if ($res->room_id) {
                DB::table('rooms')
                    ->where('room_id', $res->room_id)
                    ->update(['status' => 'available']);
            }

            DB::commit();

            $stats = $this->buildCheckOutStats();

            return response()->json([
                'success'         => true,
                'message'         => 'Guest checked out successfully. Room is now available.',
                'registration_id' => (int) $registration_id,
                'stats'           => $stats,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('processCheckOut error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Check-out failed: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ALL REGISTRATIONS
    // ─────────────────────────────────────────────────────────────
    public function all(Request $request)
    {
        $query = DB::table('registrations')
            ->join('reservations',  'registrations.reservation_id',   '=', 'reservations.reservation_id')
            ->join('users',         'registrations.user_id',          '=', 'users.user_id')
            ->join('rooms',         'reservations.room_id',           '=', 'rooms.room_id')
            ->join('room_types',    'rooms.room_type_id',             '=', 'room_types.room_type_id')
            ->join('payments',      'registrations.payment_id',       '=', 'payments.payment_id')
            ->leftJoin('guest_details', function ($join) {
                $join->on('guest_details.reservation_id', '=', 'reservations.reservation_id')
                     ->where('guest_details.is_primary', '=', 1);
            })
            ->select(
                'registrations.registration_id',
                'registrations.check_in_at',
                'registrations.check_out_date',
                'registrations.created_at',
                'reservations.reservation_id',
                'reservations.check_in_date',
                'reservations.check_out_date as expected_checkout',
                'reservations.no_nights',
                'reservations.total_amount',
                'reservations.balance',
                'reservations.adults',
                'rooms.room_number',
                'room_types.room_type_name',
                'payments.payment_method',
                'payments.payment_status',
                'users.email',
                DB::raw("COALESCE(guest_details.first_name, '') as guest_first_name"),
                DB::raw("COALESCE(guest_details.last_name, '')  as guest_last_name"),
                DB::raw("COALESCE(guest_details.contact_number, '') as guest_contact"),
                DB::raw("IF(registrations.check_out_date IS NULL, 'active', 'completed') as reg_status"),
            );

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('registrations.check_out_date');
            } elseif ($request->status === 'completed') {
                $query->whereNotNull('registrations.check_out_date');
            }
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('guest_details.first_name', 'like', $s)
                  ->orWhere('guest_details.last_name', 'like', $s)
                  ->orWhere('users.email',             'like', $s)
                  ->orWhere('rooms.room_number',       'like', $s);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('registrations.check_in_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('registrations.check_in_at', '<=', $request->date_to);
        }

        $registrations = $query->orderBy('registrations.created_at', 'desc')->paginate(15);

        return view('content.staff.registration.all', compact('registrations'));
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW — single registration detail
    // ─────────────────────────────────────────────────────────────
    public function show($registration_id)
    {
        $registration = DB::table('registrations')
            ->join('reservations',  'registrations.reservation_id',   '=', 'reservations.reservation_id')
            ->join('users',         'registrations.user_id',          '=', 'users.user_id')
            ->join('rooms',         'reservations.room_id',           '=', 'rooms.room_id')
            ->join('room_types',    'rooms.room_type_id',             '=', 'room_types.room_type_id')
            ->join('payments',      'registrations.payment_id',       '=', 'payments.payment_id')
            ->where('registrations.registration_id', $registration_id)
            ->select(
                'registrations.*',
                'reservations.reservation_id',
                'reservations.check_in_date',
                'reservations.check_out_date as expected_checkout',
                'reservations.no_nights',
                'reservations.total_amount',
                'reservations.balance',
                'reservations.adults',
                'reservations.reservation_fee',
                'reservations.purpose',
                'rooms.room_number',
                'rooms.room_id',
                'room_types.room_type_name',
                'room_types.rate_per_night',
                'payments.payment_method',
                'payments.payment_status',
                'payments.amount as payment_amount',
                'payments.paid_at',
                'users.email',
            )
            ->first();

        if (!$registration) {
            return redirect()->route('staff.registration.all')
                ->with('error', 'Registration not found.');
        }

        $guests = DB::table('guest_details')
            ->where('reservation_id', $registration->reservation_id)
            ->orderBy('is_primary', 'desc')
            ->get();

        return view('content.staff.registration.show', compact('registration', 'guests'));
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────
    private function buildCheckInStats(): array
    {
        $today   = now()->toDateString();
        $pending = DB::table('reservations as r')
            ->leftJoin('registrations as reg', 'reg.reservation_id', '=', 'r.reservation_id')
            ->where('r.reservation_status', 'approved')
            ->whereNull('reg.registration_id')
            ->select('r.check_in_date')
            ->get();

        return [
            'total'    => $pending->count(),
            'overdue'  => $pending->filter(fn($r) => $r->check_in_date < $today)->count(),
            'today'    => $pending->filter(fn($r) => $r->check_in_date === $today)->count(),
            'upcoming' => $pending->filter(fn($r) => $r->check_in_date > $today)->count(),
        ];
    }

    private function buildCheckOutStats(): array
    {
        $today  = now()->toDateString();
        $active = DB::table('registrations as reg')
            ->join('reservations as r', 'reg.reservation_id', '=', 'r.reservation_id')
            ->whereNull('reg.check_out_date')
            ->select('r.check_out_date as expected_checkout')
            ->get();

        return [
            'total'   => $active->count(),
            'overdue' => $active->filter(fn($r) => $r->expected_checkout < $today)->count(),
            'today'   => $active->filter(fn($r) => $r->expected_checkout === $today)->count(),
            'staying' => $active->filter(fn($r) => $r->expected_checkout > $today)->count(),
        ];
    }
}