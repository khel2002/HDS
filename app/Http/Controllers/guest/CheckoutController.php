<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // CHECKOUT PAGE
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $userId       = Auth::id();
        $registration = $this->getActiveRegistration($userId);

        if (! $registration) {
            return redirect()->route('guest.dashboard')
                ->with('error', 'No active check-in found.');
        }

        $reservation = DB::table('reservations as r')
            ->join('rooms as rm',      'rm.room_id',      '=', 'r.room_id')
            ->join('room_types as rt', 'rt.room_type_id', '=', 'rm.room_type_id')
            ->where('r.reservation_id', $registration->reservation_id)
            ->select(
                'r.reservation_id',
                'r.check_in_date',
                'r.check_out_date',
                'r.no_nights',
                'r.total_amount',
                'r.balance',
                'r.adults',
                'r.children',
                'r.purpose',
                'rm.room_number',
                'rm.room_id',
                'rt.room_type_name',
                'rt.rate_per_night',
                'rt.description as room_description'
            )
            ->first();

        // Damage / missing-item charges added by staff
        $damageCharges = DB::table('checkout_damage_charges')
            ->where('registration_id', $registration->registration_id)
            ->orderByDesc('created_at')
            ->get();

        $totalDamageCharges = $damageCharges->sum('amount');

        // Current checkout request (if any)
        $checkoutRequest = DB::table('checkout_requests')
            ->where('registration_id', $registration->registration_id)
            ->orderByDesc('created_at')
            ->first();

        // Food / service charges billed to the guest
        $serviceCharges = DB::table('service_requests as sr')
            ->where('sr.registration_id', $registration->registration_id)
            ->where('sr.service_type', 'food')
            ->where('sr.request_status', 'completed')
            ->join('service_breakfast_orders as sbo', 'sbo.service_request_id', '=', 'sr.service_request_id')
            ->join('breakfast_menu as bm', 'bm.breakfast_id', '=', 'sbo.breakfast_id')
            ->select(
                'sr.service_request_id',
                'sr.requested_at',
                'bm.meal_name',
                'sbo.quantity',
                'sbo.price_at_order',
                DB::raw('sbo.quantity * sbo.price_at_order as line_total')
            )
            ->get();

        $totalServiceCharges = $serviceCharges->sum('line_total');

        // Updated balance = room balance + damage charges (service charges are already in total_amount)
        $updatedBalance = ($reservation->balance ?? 0) + $totalDamageCharges;

        return view('content.guest.checkout', compact(
            'registration',
            'reservation',
            'damageCharges',
            'totalDamageCharges',
            'checkoutRequest',
            'updatedBalance',
            'serviceCharges',
            'totalServiceCharges'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMIT CHECKOUT REQUEST  (guest → notifies super admin)
    // ─────────────────────────────────────────────────────────────
    public function requestCheckout(Request $request)
    {
        $userId       = Auth::id();
        $registration = $this->getActiveRegistration($userId);

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'No active check-in found.'], 422);
        }

        // Block if a request is already in progress
        $existing = DB::table('checkout_requests')
            ->where('registration_id', $registration->registration_id)
            ->whereIn('status', ['pending', 'inspecting'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error'   => 'A checkout request is already in progress. Please wait for staff inspection.',
            ], 422);
        }

        // Block if there are unacknowledged damage charges
        $unacknowledged = DB::table('checkout_damage_charges')
            ->where('registration_id', $registration->registration_id)
            ->where('is_acknowledged', 0)
            ->count();

        if ($unacknowledged > 0) {
            return response()->json([
                'success' => false,
                'error'   => 'Please acknowledge the outstanding damage/missing charges before requesting checkout.',
            ], 422);
        }

        DB::table('checkout_requests')->insert([
            'registration_id' => $registration->registration_id,
            'requested_by'    => $userId,
            'status'          => 'pending',
            'notes'           => $request->input('notes', null),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checkout request submitted. Staff will inspect your room shortly.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ACKNOWLEDGE DAMAGE CHARGES
    // ─────────────────────────────────────────────────────────────
    public function acknowledgeDamage(Request $request)
    {
        $userId       = Auth::id();
        $registration = $this->getActiveRegistration($userId);

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'No active check-in found.'], 422);
        }

        DB::table('checkout_damage_charges')
            ->where('registration_id', $registration->registration_id)
            ->where('is_acknowledged', 0)
            ->update([
                'is_acknowledged' => 1,
                'acknowledged_at' => now(),
            ]);

        // Recompute reservation balance to include damage charges
        $totalDamage = DB::table('checkout_damage_charges')
            ->where('registration_id', $registration->registration_id)
            ->sum('amount');

        $res = DB::table('reservations')
            ->where('reservation_id', $registration->reservation_id)
            ->select('total_amount', 'balance')
            ->first();

        // alreadyPaid = total_amount − current balance
        $alreadyPaid    = $res->total_amount - $res->balance;
        $newTotalAmount = $res->total_amount + $totalDamage;
        $newBalance     = $newTotalAmount - $alreadyPaid;

        DB::table('reservations')
            ->where('reservation_id', $registration->reservation_id)
            ->update([
                'total_amount' => $newTotalAmount,
                'balance'      => max(0, $newBalance),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Charges acknowledged. Your updated balance has been applied.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POLL CHECKOUT STATUS  (guest polling for inspection result)
    // ─────────────────────────────────────────────────────────────
    public function status()
    {
        $userId       = Auth::id();
        $registration = $this->getActiveRegistration($userId);

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'No active registration.'], 422);
        }

        $checkoutRequest = DB::table('checkout_requests')
            ->where('registration_id', $registration->registration_id)
            ->orderByDesc('created_at')
            ->first();

        $damageCharges = DB::table('checkout_damage_charges')
            ->where('registration_id', $registration->registration_id)
            ->where('is_acknowledged', 0)
            ->get();

        return response()->json([
            'success'          => true,
            'checkout_request' => $checkoutRequest,
            'damage_charges'   => $damageCharges,
            'has_new_charges'  => $damageCharges->isNotEmpty(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────
    private function getActiveRegistration(int $userId)
    {
        return DB::table('registrations as reg')
            ->join('reservations as r', 'r.reservation_id', '=', 'reg.reservation_id')
            ->where('reg.user_id', $userId)
            ->whereNotNull('reg.check_in_at')
            ->whereNull('reg.check_out_date')
            ->select(
                'reg.registration_id',
                'r.reservation_id',
                'r.check_out_date',
                'r.balance',
                'r.total_amount'
            )
            ->latest('reg.check_in_at')
            ->first();
    }
}