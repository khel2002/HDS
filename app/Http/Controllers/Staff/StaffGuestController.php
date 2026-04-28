<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffGuestController extends Controller
{
    // ─────────────────────────────────────────────
    //  ALL GUESTS
    // ─────────────────────────────────────────────
    public function all(Request $request)
    {
        $search  = $request->get('search');
        $status  = $request->get('status');
        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowed = ['created_at', 'last_name', 'email', 'last_login_at'];
        if (!in_array($sortBy, $allowed)) {
            $sortBy = 'created_at';
        }

        $guests = DB::table('users as u')
            ->join('roles as r', 'u.role_id', '=', 'r.role_id')
            ->leftJoin('reservations as res', 'u.user_id', '=', 'res.user_id')
            ->leftJoin('registrations as reg', 'u.user_id', '=', 'reg.user_id')
            ->where('r.role_name', 'guest')
            ->when($search, fn($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('u.first_name', 'like', "%{$search}%")
                       ->orWhere('u.last_name',  'like', "%{$search}%")
                       ->orWhere('u.email',       'like', "%{$search}%");
                })
            )
            ->when($status, fn($q) => $q->where('u.STATUS', $status))
            ->select(
                'u.user_id',
                'u.first_name',
                'u.middle_name',
                'u.last_name',
                'u.email',
                'u.STATUS',
                'u.created_at',
                'u.last_login_at',
                DB::raw('COUNT(DISTINCT res.reservation_id) as total_reservations'),
                DB::raw('COUNT(DISTINCT reg.registration_id) as total_stays')
            )
            ->groupBy(
                'u.user_id', 'u.first_name', 'u.middle_name', 'u.last_name',
                'u.email', 'u.STATUS', 'u.created_at', 'u.last_login_at'
            )
            ->orderBy("u.{$sortBy}", $sortDir)
            ->get();

        $stats = [
            'total_guests'    => $guests->count(),
            'active_guests'   => $guests->filter(fn($g) => $g->STATUS === 'active')->count(),
            'inactive_guests' => $guests->filter(fn($g) => $g->STATUS === 'inactive')->count(),
            'new_this_month'  => DB::table('users as u')
                ->join('roles as r', 'u.role_id', '=', 'r.role_id')
                ->where('r.role_name', 'guest')
                ->whereMonth('u.created_at', now()->month)
                ->whereYear('u.created_at', now()->year)
                ->count(),
        ];

        return view('content.staff.guests.all', compact('guests', 'stats', 'search', 'status'));
    }

    // ─────────────────────────────────────────────
    //  CURRENT GUESTS  (checked-in, not checked-out)
    // ─────────────────────────────────────────────
    public function current(Request $request)
    {
        $search   = $request->get('search');
        $roomType = $request->get('room_type');

        $currentGuests = DB::table('registrations as reg')
            ->join('users as u',          'reg.user_id',        '=', 'u.user_id')
            ->join('reservations as res',  'reg.reservation_id', '=', 'res.reservation_id')
            ->join('rooms as ro',          'res.room_id',        '=', 'ro.room_id')
            ->join('room_types as rt',     'ro.room_type_id',    '=', 'rt.room_type_id')
            ->join('payments as p',        'reg.payment_id',     '=', 'p.payment_id')
            ->leftJoin('guest_details as gd',
                fn($j) => $j->on('gd.reservation_id', '=', 'res.reservation_id')
                             ->where('gd.is_primary', 1)
            )
            ->whereNotNull('reg.check_in_at')
            ->whereNull('reg.check_out_date')
            ->when($search, fn($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('u.first_name',  'like', "%{$search}%")
                       ->orWhere('u.last_name',  'like', "%{$search}%")
                       ->orWhere('u.email',       'like', "%{$search}%")
                       ->orWhere('ro.room_number','like', "%{$search}%");
                })
            )
            ->when($roomType, fn($q) => $q->where('ro.room_type_id', $roomType))
            ->select(
                'reg.registration_id',
                'reg.check_in_at',
                'res.reservation_id',
                'res.check_out_date',
                'res.no_nights',
                'res.adults',
                'res.children',
                'res.total_amount',
                'res.balance',
                'u.user_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'ro.room_id',
                'ro.room_number',
                'rt.room_type_name',
                'rt.rate_per_night',
                'p.payment_status',
                'p.payment_method',
                DB::raw("COALESCE(gd.contact_number, '') as contact_number")
            )
            ->orderBy('reg.check_in_at', 'desc')
            ->get();

        $roomTypes = DB::table('room_types')->orderBy('room_type_name')->get();

        $stats = [
            'total_current'           => $currentGuests->count(),
            'total_rooms_occupied'    => $currentGuests->unique('room_number')->count(),
            'expected_checkout_today' => $currentGuests
                ->filter(fn($g) => $g->check_out_date === now()->toDateString())
                ->count(),
            'outstanding_balance'     => $currentGuests->sum('balance'),
        ];

        return view('content.staff.guests.current', compact(
            'currentGuests', 'stats', 'roomTypes', 'search', 'roomType'
        ));
    }

    // ─────────────────────────────────────────────
    //  GUEST HISTORY  (completed check-outs)
    // ─────────────────────────────────────────────
    public function history(Request $request)
    {
        $search   = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $roomType = $request->get('room_type');

        $history = DB::table('registrations as reg')
            ->join('users as u',          'reg.user_id',        '=', 'u.user_id')
            ->join('reservations as res',  'reg.reservation_id', '=', 'res.reservation_id')
            ->join('rooms as ro',          'res.room_id',        '=', 'ro.room_id')
            ->join('room_types as rt',     'ro.room_type_id',    '=', 'rt.room_type_id')
            ->join('payments as p',        'reg.payment_id',     '=', 'p.payment_id')
            ->whereNotNull('reg.check_out_date')
            ->when($search, fn($q) =>
                $q->where(function ($q2) use ($search) {
                    $q2->where('u.first_name',  'like', "%{$search}%")
                       ->orWhere('u.last_name',  'like', "%{$search}%")
                       ->orWhere('u.email',       'like', "%{$search}%")
                       ->orWhere('ro.room_number','like', "%{$search}%");
                })
            )
            ->when($dateFrom, fn($q) => $q->whereDate('reg.check_out_date', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('reg.check_out_date', '<=', $dateTo))
            ->when($roomType, fn($q) => $q->where('ro.room_type_id', $roomType))
            ->select(
                'reg.registration_id',
                'reg.check_in_at',
                'reg.check_out_date',
                'reg.created_at',
                'res.reservation_id',
                'res.no_nights',
                'res.adults',
                'res.children',
                'res.total_amount',
                'res.balance',
                'res.purpose',
                'u.user_id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'ro.room_number',
                'rt.room_type_name',
                'rt.rate_per_night',
                'p.payment_status',
                'p.payment_method',
                'p.amount as amount_paid'
            )
            ->orderBy('reg.check_out_date', 'desc')
            ->get();

        $roomTypes = DB::table('room_types')->orderBy('room_type_name')->get();

        $stats = [
            'total_stays'   => $history->count(),
            'total_revenue' => $history->where('payment_status', 'completed')->sum('amount_paid'),
            'avg_nights'    => round($history->avg('no_nights'), 1),
            'unique_guests' => $history->unique('user_id')->count(),
        ];

        return view('content.staff.guests.history', compact(
            'history', 'stats', 'roomTypes', 'search', 'dateFrom', 'dateTo', 'roomType'
        ));
    }

    // ─────────────────────────────────────────────
    //  SHOW  (single guest profile + all stays)
    // ─────────────────────────────────────────────
    public function show($id)
    {
        $guest = DB::table('users as u')
            ->join('roles as r', 'u.role_id', '=', 'r.role_id')
            ->where('u.user_id', $id)
            ->where('r.role_name', 'guest')
            ->select('u.*', 'r.role_name')
            ->first();

        abort_if(!$guest, 404);

        $stays = DB::table('registrations as reg')
            ->join('reservations as res', 'reg.reservation_id', '=', 'res.reservation_id')
            ->join('rooms as ro',         'res.room_id',        '=', 'ro.room_id')
            ->join('room_types as rt',    'ro.room_type_id',    '=', 'rt.room_type_id')
            ->join('payments as p',       'reg.payment_id',     '=', 'p.payment_id')
            ->where('reg.user_id', $id)
            ->select(
                'reg.*',
                'res.check_in_date', 'res.check_out_date as expected_checkout',
                'res.no_nights', 'res.total_amount', 'res.balance', 'res.purpose',
                'ro.room_number', 'rt.room_type_name', 'rt.rate_per_night',
                'p.payment_status', 'p.payment_method', 'p.amount as amount_paid'
            )
            ->orderBy('reg.check_in_at', 'desc')
            ->get();

        $guestDetails = DB::table('guest_details')
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'guest'        => $guest,
            'stays'        => $stays,
            'guestDetails' => $guestDetails,
        ]);
    }

    // ─────────────────────────────────────────────
    //  UPDATE STATUS  (active / inactive)
    // ─────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $guest = DB::table('users')->where('user_id', $id)->first();
        abort_if(!$guest, 404);

        DB::table('users')
            ->where('user_id', $id)
            ->update(['STATUS' => $request->status, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Guest status updated to {$request->status}.",
        ]);
    }
}