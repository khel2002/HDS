<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuestPortalController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // ── Active reservation (approved or checked-in) ──────────────────────
        // We look for the most recent non-cancelled/rejected reservation
        $reservation = DB::table('reservations as r')
            ->join('rooms as rm', 'rm.room_id', '=', 'r.room_id')
            ->join('room_types as rt', 'rt.room_type_id', '=', 'rm.room_type_id')
            ->leftJoin('payments as p', 'p.payment_id', '=', 'r.payment_id')
            ->leftJoin('registrations as reg', 'reg.reservation_id', '=', 'r.reservation_id')
            ->where('r.user_id', $userId)
            ->whereIn('r.reservation_status', ['pending', 'approved'])
            ->select(
                'r.reservation_id',
                'r.reservation_status',
                'r.check_in_date',
                'r.check_out_date',
                'r.total_amount',
                'r.balance',
                'r.adults',
                'r.children',
                'r.no_nights',
                'r.purpose',
                'rm.room_number',
                'rm.image_path',
                'rt.room_type_name',
                'rt.rate_per_night',
                'rt.max_pax',
                'rt.description as room_description',
                'p.amount as paid_amount',
                'p.payment_status',
                'reg.check_in_at',
                'reg.registration_id'
            )
            ->orderByDesc('r.created_at')
            ->first();

        // ── Determine portal status ──────────────────────────────────────────
        // 'checked-in'  = registration record exists with check_in_at set
        // 'approved'    = reservation approved but not yet checked in
        // 'pending'     = reservation pending approval
        $portalStatus = 'pending';
        if ($reservation) {
            if ($reservation->check_in_at) {
                $portalStatus = 'checked-in';
            } elseif ($reservation->reservation_status === 'approved') {
                $portalStatus = 'approved';
            }
        }

        // ── Guest (primary guest details) ────────────────────────────────────
        $guestDetails = null;
        if ($reservation) {
            $guestDetails = DB::table('guest_details')
                ->where('reservation_id', $reservation->reservation_id)
                ->where('is_primary', 1)
                ->first();
        }

        // Fall back to users table if no guest_details row yet
        $authUser = Auth::user();

        $guest = [
            'status'    => $portalStatus,
            'name'      => $guestDetails
                            ? trim($guestDetails->first_name . ' ' . $guestDetails->last_name)
                            : trim($authUser->first_name . ' ' . $authUser->last_name),
            'email'     => $authUser->email,
            'checkIn'   => $reservation ? \Carbon\Carbon::parse($reservation->check_in_date)->format('F j, Y') : '—',
            'checkOut'  => $reservation ? \Carbon\Carbon::parse($reservation->check_out_date)->format('F j, Y') : '—',
            'guestId'   => $reservation ? 'RES-' . str_pad($reservation->reservation_id, 5, '0', STR_PAD_LEFT) : '—',
            'pax'       => $reservation ? ($reservation->adults + $reservation->children) : 0,
            'nights'    => $reservation ? $reservation->no_nights : 0,
            'purpose'   => $reservation ? $reservation->purpose : null,
        ];

        // ── Room ─────────────────────────────────────────────────────────────
        $room = null;
        if ($reservation) {
            // Room amenities
            $amenities = DB::table('room_amenities as ra')
                ->join('amenities as a', 'a.amenity_id', '=', 'ra.amenity_id')
                ->join('rooms as rm', 'rm.room_id', '=', 'ra.room_id')
                ->where('rm.room_number', $reservation->room_number)
                ->pluck('a.amenity_name')
                ->toArray();

            $room = [
                'number'      => $reservation->room_number,
                'type'        => $reservation->room_type_name,
                'description' => $reservation->room_description,
                'rate'        => number_format($reservation->rate_per_night, 2),
                'max_pax'     => $reservation->max_pax,
                'image'       => $reservation->image_path
                                    ? asset('storage/' . $reservation->image_path)
                                    : 'https://images.unsplash.com/photo-1731336478850-6bce7235e320?fit=max&w=900',
                'amenities'   => $amenities,
            ];
        }

        // ── Balance ──────────────────────────────────────────────────────────
        $balance = [
            'remaining' => $reservation ? '₱' . number_format($reservation->balance, 2) : '₱0.00',
            'charges'   => $reservation ? '₱' . number_format($reservation->total_amount, 2) : '₱0.00',
            'payments'  => $reservation ? '₱' . number_format(($reservation->total_amount - $reservation->balance), 2) : '₱0.00',
        ];

        // ── Breakfast menu (available items only) ────────────────────────────
        $menuItems = DB::table('breakfast_menu')
            ->where('is_available', 1)
            ->orderBy('meal_name')
            ->get()
            ->map(fn($m) => [
                'name'     => $m->meal_name,
                'category' => 'Breakfast',
                'price'    => '₱' . number_format($m->price, 2),
                'price_raw'=> $m->price,
                'desc'     => $m->description ?? '',
                'id'       => $m->breakfast_id,
                'img'      => $m->image_path ? asset('storage/' . $m->image_path) : null,
            ])
            ->toArray();

        // ── Service requests for current registration ────────────────────────
        $serviceRequests = [];
        if ($reservation && $reservation->registration_id) {
            $serviceRequests = DB::table('service_requests')
                ->where('registration_id', $reservation->registration_id)
                ->orderByDesc('requested_at')
                ->get()
                ->toArray();
        }

        return view('content.dashboard.guest-portal', compact(
            'guest',
            'room',
            'balance',
            'menuItems',
            'serviceRequests',
            'reservation'
        ));
    }

    // ── Submit a service / food request ─────────────────────────────────────
    public function requestService(Request $request)
    {
        $request->validate([
            'service_type' => 'required|in:food,room_service',
            'description'  => 'required|string|max:255',
        ]);

        $userId = Auth::id();

        // Get active registration
        $registration = DB::table('registrations')
            ->join('reservations', 'reservations.reservation_id', '=', 'registrations.reservation_id')
            ->where('reservations.user_id', $userId)
            ->whereNotNull('registrations.check_in_at')
            ->whereNull('registrations.check_out_date')
            ->select('registrations.registration_id')
            ->first();

        if (!$registration) {
            return response()->json(['error' => 'No active check-in found.'], 422);
        }

        $serviceRequestId = DB::table('service_requests')->insertGetId([
            'registration_id'     => $registration->registration_id,
            'requested_by_user_id'=> $userId,
            'service_type'        => $request->service_type,
            'description'         => $request->description,
            'request_status'      => 'pending',
            'requested_at'        => now(),
        ]);

        // If food order, attach breakfast items
        if ($request->service_type === 'food' && $request->has('items')) {
            $breakfastIds = collect($request->items)->pluck('breakfast_id')->toArray();
            $prices = DB::table('breakfast_menu')
                ->whereIn('breakfast_id', $breakfastIds)
                ->pluck('price', 'breakfast_id');

            $rows = [];
            foreach ($request->items as $item) {
                $rows[] = [
                    'service_request_id' => $serviceRequestId,
                    'breakfast_id'       => $item['breakfast_id'],
                    'quantity'           => $item['quantity'] ?? 1,
                    'price_at_order'     => $prices[$item['breakfast_id']] ?? 0,
                ];
            }
            DB::table('service_breakfast_orders')->insert($rows);
        }

        return response()->json(['success' => true, 'service_request_id' => $serviceRequestId]);
    }
}