<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
   public function index()
    {

        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;


        $stats = [

            'total_reservations' => DB::table('reservations')->count(),
            'pending_reservations' => DB::table('reservations')
                ->where('reservation_status', 'pending')
                ->count(),
            'approved_reservations' => DB::table('reservations')
                ->where('reservation_status', 'approved')
                ->count(),
            'today_check_ins' => DB::table('reservations')
                ->whereDate('check_in_date', $today)
                ->where('reservation_status', 'approved')
                ->count(),
            'today_check_outs' => DB::table('reservations')
                ->whereDate('check_out_date', $today)
                ->count(),


            'total_guests' => DB::table('guest_details')->count(),
            'active_guests' => DB::table('registrations')
                ->whereNull('check_out_date')
                ->count(),
            'new_guests_this_month' => DB::table('guest_details')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),


            'total_rooms' => DB::table('rooms')->count(),
            'available_rooms' => DB::table('rooms')
                ->where('status', 'available')
                ->count(),
            'occupied_rooms' => DB::table('rooms')
                ->where('status', 'occupied')
                ->count(),
            'maintenance_rooms' => DB::table('rooms')
                ->where('status', 'maintenance')
                ->count(),
            'occupancy_rate' => $this->calculateOccupancyRate(),


            'today_revenue' => DB::table('payments')
                ->whereDate('payment_date', $today)
                ->where('payment_status', 'completed')
                ->sum('amount') ?? 0,
            'monthly_revenue' => DB::table('payments')
                ->whereMonth('payment_date', $currentMonth)
                ->whereYear('payment_date', $currentYear)
                ->where('payment_status', 'completed')
                ->sum('amount') ?? 0,
            'yearly_revenue' => DB::table('payments')
                ->whereYear('payment_date', $currentYear)
                ->where('payment_status', 'completed')
                ->sum('amount') ?? 0,
            'pending_payments' => DB::table('payments')
                ->where('payment_status', 'pending')
                ->sum('amount') ?? 0,
            'total_payments_today' => DB::table('payments')
                ->whereDate('payment_date', $today)
                ->where('payment_status', 'completed')
                ->count(),


            'pending_service_requests' => DB::table('service_requests')
                ->where('request_status', 'pending')
                ->count(),
            'in_progress_service_requests' => DB::table('service_requests')
                ->where('request_status', 'in_progress')
                ->count(),
            'completed_today_requests' => DB::table('service_requests')
                ->whereDate('completed_at', $today)
                ->where('request_status', 'completed')
                ->count(),


            'breakfast_orders_today' => DB::table('service_requests')
                ->whereDate('requested_at', $today)
                ->where('service_type', 'food')
                ->count(),


            'total_users' => DB::table('users')->count(),
            'active_staff' => DB::table('users')
                ->whereIn('role_id', [1, 2, 3])
                ->where('STATUS', 'active')
                ->count(),
        ];


        $recentReservations = DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->leftJoin('rooms', 'reservations.room_id', '=', 'rooms.room_id')
            ->select(
                'reservations.*',
                'guest_details.first_name',
                'guest_details.last_name',
                'rooms.room_number'
            )
            ->orderBy('reservations.created_at', 'desc')
            ->limit(10)
            ->get();

        $recentPayments = DB::table('payments')
            ->join('reservations', 'payments.payment_id', '=', 'reservations.payment_id')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->select(
                'payments.*',
                'guest_details.first_name',
                'guest_details.last_name',
                'reservations.reservation_id'
            )
            ->whereNotNull('reservations.payment_id')
            ->orderBy('payments.payment_date', 'desc')
            ->limit(10)
            ->get();

        $activeRegistrations = DB::table('registrations')
            ->join('guest_details', 'registrations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->join('reservations', 'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->leftJoin('rooms', 'reservations.room_id', '=', 'rooms.room_id')
            ->select(
                'registrations.*',
                'guest_details.first_name',
                'guest_details.last_name',
                'rooms.room_number',
                'reservations.check_out_date'
            )
            ->whereNull('registrations.check_out_date')
            ->orderBy('registrations.check_in_at', 'desc')
            ->limit(10)
            ->get();

        $pendingServiceRequests = DB::table('service_requests')
            ->join('registrations', 'service_requests.registration_id', '=', 'registrations.registration_id')
            ->join('guest_details', 'registrations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->join('reservations', 'registrations.reservation_id', '=', 'reservations.reservation_id')
            ->leftJoin('rooms', 'reservations.room_id', '=', 'rooms.room_id')
            ->select(
                'service_requests.*',
                'guest_details.first_name',
                'guest_details.last_name',
                'rooms.room_number'
            )
            ->whereIn('service_requests.request_status', ['pending', 'in_progress'])
            ->orderBy('service_requests.requested_at', 'desc')
            ->limit(10)
            ->get();


        $revenueChartData = $this->getRevenueChartData();


        $occupancyChartData = $this->getOccupancyChartData();


        $roomStatusDistribution = DB::table('rooms')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();


        $roomTypeStats = DB::table('room_types')
            ->leftJoin('rooms', 'room_types.room_type_id', '=', 'rooms.room_type_id')
            ->select(
                'room_types.room_type_name',
                'room_types.rate_per_night',
                DB::raw('COUNT(rooms.room_id) as total_rooms'),
                DB::raw('SUM(CASE WHEN rooms.status = "available" THEN 1 ELSE 0 END) as available_rooms'),
                DB::raw('SUM(CASE WHEN rooms.status = "occupied" THEN 1 ELSE 0 END) as occupied_rooms')
            )
            ->groupBy('room_types.room_type_id', 'room_types.room_type_name', 'room_types.rate_per_night')
            ->get();


        $reservationStatusStats = DB::table('reservations')
            ->select('reservation_status', DB::raw('count(*) as count'))
            ->groupBy('reservation_status')
            ->get();


        return view('content.dashboard.admin_dashboard', compact(
            'stats',
            'recentReservations',
            'recentPayments',
            'activeRegistrations',
            'pendingServiceRequests',
            'revenueChartData',
            'occupancyChartData',
            'roomStatusDistribution',
            'roomTypeStats',
            'reservationStatusStats'
        ));
    }

    private function calculateOccupancyRate()
    {
        $totalRooms = DB::table('rooms')->count();

        if ($totalRooms == 0) {
            return 0;
        }

        $occupiedRooms = DB::table('rooms')->where('status', 'occupied')->count();

        return round(($occupiedRooms / $totalRooms) * 100, 2);
    }

    private function getRevenueChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = DB::table('payments')
                ->whereDate('payment_date', $date)
                ->where('payment_status', 'completed')
                ->sum('amount');

            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => floatval($revenue ?? 0)
            ];
        }
        return $data;
    }

    private function getOccupancyChartData()
    {
        $data = [];
        $totalRooms = DB::table('rooms')->count();

        if ($totalRooms == 0) {

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $data[] = [
                    'date' => $date->format('M d'),
                    'occupancy' => 0
                ];
            }
            return $data;
        }

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);


            $occupiedRooms = DB::table('registrations')
                ->join('reservations', 'registrations.reservation_id', '=', 'reservations.reservation_id')
                ->whereDate('reservations.check_in_date', '<=', $date)
                ->where(function($query) use ($date) {
                    $query->whereDate('reservations.check_out_date', '>=', $date)
                          ->orWhereNull('registrations.check_out_date');
                })
                ->distinct('registrations.registration_id')
                ->count();

            $occupancyRate = round(($occupiedRooms / $totalRooms) * 100, 2);

            $data[] = [
                'date' => $date->format('M d'),
                'occupancy' => $occupancyRate
            ];
        }
        return $data;
    }
}
