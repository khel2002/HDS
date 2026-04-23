<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $pendingServiceRequests    = ServiceRequest::where('service_type', 'room_service')
                                        ->where('request_status', 'pending')
                                        ->count();

        $inProgressServiceRequests = ServiceRequest::where('service_type', 'room_service')
                                        ->where('request_status', 'in_progress')
                                        ->count();

        $pendingBreakfastOrders    = ServiceRequest::where('service_type', 'food')
                                        ->where('request_status', 'pending')
                                        ->count();

        $preparingBreakfastOrders  = ServiceRequest::where('service_type', 'food')
                                        ->where('request_status', 'in_progress')
                                        ->count();

        $activeGuests = DB::table('registrations')
                            ->whereNotNull('check_in_at')
                            ->whereNull('check_out_date')
                            ->count();

        $occupiedRooms  = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();

        $stats = [
            'pending_service_requests'     => $pendingServiceRequests,
            'in_progress_service_requests' => $inProgressServiceRequests,
            'pending_breakfast_orders'     => $pendingBreakfastOrders,
            'preparing_breakfast_orders'   => $preparingBreakfastOrders,
            'active_guests'                => $activeGuests,
            'occupied_rooms'               => $occupiedRooms,
            'available_rooms'              => $availableRooms,
        ];

        // ── Pending Room Service Requests ──────────────────────────────────────

        $pendingServiceRequestsList = DB::table('service_requests')
            ->select(
                'service_requests.service_request_id',
                'service_requests.service_type',
                'service_requests.description',
                'service_requests.request_status',
                'service_requests.requested_at',
                'users.first_name',
                'users.last_name',
                'rooms.room_number'
            )
            ->join('registrations',
                'service_requests.registration_id', '=', 'registrations.registration_id')
            ->join('reservations',
                'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->join('rooms',
                'reservations.room_id', '=', 'rooms.room_id')
            ->join('users',
                'service_requests.requested_by_user_id', '=', 'users.user_id')
            ->where('service_requests.service_type', 'room_service')
            ->whereIn('service_requests.request_status', ['pending', 'in_progress'])
            ->orderByRaw("FIELD(service_requests.request_status, 'pending', 'in_progress')")
            ->orderBy('service_requests.requested_at', 'asc')
            ->limit(10)
            ->get();

        // ── Pending Breakfast Orders ───────────────────────────────────────────
        // Aggregate item count from service_breakfast_orders

        $pendingBreakfastOrdersList = DB::table('service_requests')
            ->select(
                'service_requests.service_request_id',
                'service_requests.description',
                'service_requests.request_status',
                'service_requests.requested_at',
                'users.first_name',
                'users.last_name',
                'rooms.room_number',
                DB::raw('COALESCE(SUM(service_breakfast_orders.quantity), 0) as item_count')
            )
            ->join('registrations',
                'service_requests.registration_id', '=', 'registrations.registration_id')
            ->join('reservations',
                'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->join('rooms',
                'reservations.room_id', '=', 'rooms.room_id')
            ->join('users',
                'service_requests.requested_by_user_id', '=', 'users.user_id')
            ->leftJoin('service_breakfast_orders',
                'service_breakfast_orders.service_request_id', '=', 'service_requests.service_request_id')
            ->where('service_requests.service_type', 'food')
            ->whereIn('service_requests.request_status', ['pending', 'in_progress'])
            ->groupBy(
                'service_requests.service_request_id',
                'service_requests.description',
                'service_requests.request_status',
                'service_requests.requested_at',
                'users.first_name',
                'users.last_name',
                'rooms.room_number'
            )
            ->orderByRaw("FIELD(service_requests.request_status, 'pending', 'in_progress')")
            ->orderBy('service_requests.requested_at', 'asc')
            ->limit(10)
            ->get();

        // ── Active Registrations ───────────────────────────────────────────────

        $activeRegistrations = DB::table('registrations')
            ->select(
                'registrations.registration_id',
                'registrations.check_in_at',
                'registrations.check_out_date',
                'users.first_name',
                'users.last_name',
                'rooms.room_number'
            )
            ->join('users',
                'registrations.user_id', '=', 'users.user_id')
            ->join('reservations',
                'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->join('rooms',
                'reservations.room_id', '=', 'rooms.room_id')
            ->whereNotNull('registrations.check_in_at')
            ->whereNull('registrations.check_out_date')
            ->orderBy('registrations.check_out_date', 'asc')
            ->limit(10)
            ->get();

        return view('content.dashboard.staff_dashboard', compact(
            'stats',
            'pendingServiceRequestsList',
            'pendingBreakfastOrdersList',
            'activeRegistrations'
        ));
    }
}