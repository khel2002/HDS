<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckoutRequestController extends Controller
{

    public function index()
    {
        $checkoutRequests = DB::table('checkout_requests as cr')
            ->join('registrations as reg',  'reg.registration_id', '=', 'cr.registration_id')
            ->join('reservations as r',     'r.reservation_id',    '=', 'reg.reservation_id')
            ->join('rooms as rm',           'rm.room_id',          '=', 'r.room_id')
            ->join('room_types as rt',      'rt.room_type_id',     '=', 'rm.room_type_id')
            ->join('users as u',            'u.user_id',           '=', 'cr.requested_by')
            ->leftJoin('guest_details as gd', function ($join) {
                $join->on('gd.reservation_id', '=', 'r.reservation_id')
                     ->where('gd.is_primary', '=', 1);
            })
            ->select(
                'cr.id as checkout_request_id',
                'cr.registration_id',
                'cr.status',
                'cr.notes',
                'cr.staff_notes',
                'cr.created_at as requested_at',
                'cr.reviewed_at',
                'reg.check_in_at',
                'r.reservation_id',
                'r.check_out_date as expected_checkout',
                'r.total_amount',
                'r.balance',
                'r.no_nights',
                'rm.room_number',
                'rm.room_id',
                'rt.room_type_name',
                'u.email',
                DB::raw("COALESCE(gd.first_name, '') as guest_first_name"),
                DB::raw("COALESCE(gd.last_name,  '') as guest_last_name"),
                DB::raw("COALESCE(gd.contact_number, '') as guest_contact"),
            )
            ->orderByRaw("FIELD(cr.status, 'inspecting', 'pending', 'has_issues', 'cleared')")
            ->orderByDesc('cr.created_at')
            ->get();

        // Attach damage charges to each request
        $checkoutRequests = $checkoutRequests->map(function ($req) {
            $req->damage_charges = DB::table('checkout_damage_charges')
                ->where('registration_id', $req->registration_id)
                ->orderByDesc('created_at')
                ->get();

            $req->total_damage = $req->damage_charges->sum('amount');
            return $req;
        });

        $stats = $this->buildStats();

        return view('content.super-admin.registration.checkout-requests', compact('checkoutRequests', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────
    // START INSPECTION
    // POST /super-admin/checkout-requests/{id}/start-inspection
    // pending → inspecting
    // ─────────────────────────────────────────────────────────────
    public function startInspection(Request $request, $id)
    {
        $staffId = Auth::id();

        $cr = DB::table('checkout_requests')->where('id', $id)->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        if ($cr->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error'   => 'This request is not in a pending state.',
            ], 422);
        }

        DB::table('checkout_requests')->where('id', $id)->update([
            'status'      => 'inspecting',
            'reviewed_by' => $staffId,
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room inspection started. Guest has been notified.',
            'stats'   => $this->buildStats(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ADD DAMAGE / MISSING CHARGE
    // POST /super-admin/checkout-requests/{id}/add-charge
    // Automatically adds to reservation balance
    // ─────────────────────────────────────────────────────────────
    public function addCharge(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:1',
        ]);

        $staffId = Auth::id();

        $cr = DB::table('checkout_requests')->where('id', $id)->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        if (! in_array($cr->status, ['inspecting', 'has_issues'])) {
            return response()->json([
                'success' => false,
                'error'   => 'You can only add charges during an active inspection.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Insert the charge record
            $chargeId = DB::table('checkout_damage_charges')->insertGetId([
                'registration_id'     => $cr->registration_id,
                'checkout_request_id' => $id,
                'added_by'            => $staffId,
                'description'         => $request->description,
                'amount'              => $request->amount,
                'is_acknowledged'     => 0,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // 2. Add to reservation balance
            $reg = DB::table('registrations')
                ->where('registration_id', $cr->registration_id)
                ->first();

            DB::table('reservations')
                ->where('reservation_id', $reg->reservation_id)
                ->increment('balance', $request->amount);

            // 3. Mark checkout request as has_issues
            DB::table('checkout_requests')->where('id', $id)->update([
                'status'     => 'has_issues',
                'updated_at' => now(),
            ]);

            DB::commit();

            // Return the new charge for UI rendering
            $charge = DB::table('checkout_damage_charges')->where('id', $chargeId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Charge added and balance updated.',
                'charge'  => $charge,
                'stats'   => $this->buildStats(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('addCharge error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // REMOVE CHARGE
    // DELETE /super-admin/checkout-requests/{id}/charges/{chargeId}
    // ─────────────────────────────────────────────────────────────
    public function removeCharge(Request $request, $id, $chargeId)
    {
        $cr = DB::table('checkout_requests')->where('id', $id)->first();
        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        $charge = DB::table('checkout_damage_charges')
            ->where('id', $chargeId)
            ->where('checkout_request_id', $id)
            ->first();

        if (! $charge) {
            return response()->json(['success' => false, 'error' => 'Charge not found.'], 404);
        }

        if ($charge->is_acknowledged) {
            return response()->json([
                'success' => false,
                'error'   => 'Cannot remove a charge that has already been acknowledged by the guest.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Deduct from reservation balance
            $reg = DB::table('registrations')
                ->where('registration_id', $cr->registration_id)
                ->first();

            DB::table('reservations')
                ->where('reservation_id', $reg->reservation_id)
                ->decrement('balance', $charge->amount);

            DB::table('checkout_damage_charges')->where('id', $chargeId)->delete();

            // If no more charges remain, revert status back to inspecting
            $remaining = DB::table('checkout_damage_charges')
                ->where('checkout_request_id', $id)
                ->count();

            if ($remaining === 0 && $cr->status === 'has_issues') {
                DB::table('checkout_requests')->where('id', $id)->update([
                    'status'     => 'inspecting',
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Charge removed and balance updated.',
                'stats'   => $this->buildStats(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('removeCharge error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // MARK CLEARED (no issues found)
    // POST /super-admin/checkout-requests/{id}/mark-cleared
    // inspecting → cleared
    // ─────────────────────────────────────────────────────────────
    public function markCleared(Request $request, $id)
    {
        $staffId = Auth::id();

        $cr = DB::table('checkout_requests')->where('id', $id)->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        if (! in_array($cr->status, ['inspecting', 'has_issues'])) {
            return response()->json([
                'success' => false,
                'error'   => 'Room must be in inspection state to mark as cleared.',
            ], 422);
        }

        DB::table('checkout_requests')->where('id', $id)->update([
            'status'      => 'cleared',
            'staff_notes' => $request->input('staff_notes', null),
            'reviewed_by' => $staffId,
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room marked as cleared. Guest can now complete checkout.',
            'stats'   => $this->buildStats(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // MARK HAS ISSUES (without adding a charge yet)
    // POST /super-admin/checkout-requests/{id}/mark-has-issues
    // inspecting → has_issues
    // ─────────────────────────────────────────────────────────────
    public function markHasIssues(Request $request, $id)
    {
        $staffId = Auth::id();

        $cr = DB::table('checkout_requests')->where('id', $id)->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        if ($cr->status !== 'inspecting') {
            return response()->json([
                'success' => false,
                'error'   => 'Room must be in inspecting state.',
            ], 422);
        }

        DB::table('checkout_requests')->where('id', $id)->update([
            'status'      => 'has_issues',
            'staff_notes' => $request->input('staff_notes', null),
            'reviewed_by' => $staffId,
            'reviewed_at' => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to has issues. Add charges below.',
            'stats'   => $this->buildStats(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // FINALIZE CHECKOUT
    // POST /super-admin/checkout-requests/{id}/finalize
    // Requirements: status = cleared AND balance = 0 AND all charges acknowledged
    // ─────────────────────────────────────────────────────────────
    public function finalizeCheckout(Request $request, $id)
    {
        $cr = DB::table('checkout_requests')->where('id', $id)->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Checkout request not found.'], 404);
        }

        // [GATE 1] Room must be cleared before finalizing
        if ($cr->status !== 'cleared') {
            return response()->json([
                'success' => false,
                'error'   => 'Room inspection must be cleared before finalizing checkout.',
            ], 422);
        }

        // [GATE 2] All damage charges must be acknowledged by the guest
        $unacknowledged = DB::table('checkout_damage_charges')
            ->where('registration_id', $cr->registration_id)
            ->where('is_acknowledged', 0)
            ->count();

        if ($unacknowledged > 0) {
            return response()->json([
                'success' => false,
                'error'   => 'Guest has not yet acknowledged all damage charges.',
            ], 422);
        }

        // [GATE 3] Reservation balance must be fully settled
        $reg = DB::table('registrations')
            ->where('registration_id', $cr->registration_id)
            ->whereNull('check_out_date')
            ->first();

        if (! $reg) {
            return response()->json([
                'success' => false,
                'error'   => 'Registration not found or already checked out.',
            ], 404);
        }

        $reservation = DB::table('reservations')
            ->where('reservation_id', $reg->reservation_id)
            ->first();

        if (! $reservation) {
            return response()->json(['success' => false, 'error' => 'Reservation not found.'], 404);
        }

        if ($reservation->balance > 0) {
            return response()->json([
                'success' => false,
                'error'   => 'Guest still has an outstanding balance of ₱' . number_format($reservation->balance, 2) . '. Collect payment before finalizing.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Stamp check-out date on registration
            DB::table('registrations')
                ->where('registration_id', $cr->registration_id)
                ->update(['check_out_date' => now()->toDateString()]);

            // 2. Ensure balance is zeroed out
            DB::table('reservations')
                ->where('reservation_id', $reg->reservation_id)
                ->update(['balance' => 0]);

            // 3. Free the room
            if ($reservation->room_id) {
                DB::table('rooms')
                    ->where('room_id', $reservation->room_id)
                    ->update(['status' => 'available']);
            }

            DB::commit();

            $stats = $this->buildCheckOutStats();

            return response()->json([
                'success'         => true,
                'message'         => 'Guest checked out successfully. Room is now available.',
                'registration_id' => (int) $cr->registration_id,
                'stats'           => $stats,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('finalizeCheckout error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET DETAILS (AJAX) — for modal refresh
    // GET /super-admin/checkout-requests/{id}/details
    // ─────────────────────────────────────────────────────────────
    public function details($id)
    {
        $cr = DB::table('checkout_requests as cr')
            ->join('registrations as reg',  'reg.registration_id', '=', 'cr.registration_id')
            ->join('reservations as r',     'r.reservation_id',    '=', 'reg.reservation_id')
            ->join('rooms as rm',           'rm.room_id',          '=', 'r.room_id')
            ->join('room_types as rt',      'rt.room_type_id',     '=', 'rm.room_type_id')
            ->join('users as u',            'u.user_id',           '=', 'cr.requested_by')
            ->leftJoin('guest_details as gd', function ($join) {
                $join->on('gd.reservation_id', '=', 'r.reservation_id')
                     ->where('gd.is_primary', '=', 1);
            })
            ->where('cr.id', $id)
            ->select(
                'cr.id as checkout_request_id',
                'cr.registration_id',
                'cr.status',
                'cr.notes',
                'cr.staff_notes',
                'cr.created_at as requested_at',
                'reg.check_in_at',
                'r.reservation_id',
                'r.check_out_date as expected_checkout',
                'r.total_amount',
                'r.balance',
                'r.no_nights',
                'rm.room_number',
                'rt.room_type_name',
                'u.email',
                DB::raw("COALESCE(gd.first_name, '') as guest_first_name"),
                DB::raw("COALESCE(gd.last_name,  '') as guest_last_name"),
            )
            ->first();

        if (! $cr) {
            return response()->json(['success' => false, 'error' => 'Not found.'], 404);
        }

        $cr->damage_charges = DB::table('checkout_damage_charges')
            ->where('registration_id', $cr->registration_id)
            ->orderByDesc('created_at')
            ->get();

        $cr->total_damage = $cr->damage_charges->sum('amount');

        return response()->json(['success' => true, 'data' => $cr]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────
    private function buildStats(): array
    {
        $all = DB::table('checkout_requests')->select('status')->get();

        return [
            'pending'    => $all->where('status', 'pending')->count(),
            'inspecting' => $all->where('status', 'inspecting')->count(),
            'has_issues' => $all->where('status', 'has_issues')->count(),
            'cleared'    => $all->where('status', 'cleared')->count(),
            'total'      => $all->count(),
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
            'total'    => $active->count(),
            'overdue'  => $active->filter(fn($r) => $r->expected_checkout < $today)->count(),
            'today'    => $active->filter(fn($r) => $r->expected_checkout === $today)->count(),
            'staying'  => $active->filter(fn($r) => $r->expected_checkout > $today)->count(),
        ];
    }
}