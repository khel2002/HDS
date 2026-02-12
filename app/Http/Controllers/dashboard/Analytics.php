<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\GuestDetail;

class Analytics extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        
        $guestInfo = $this->getGuestInfo($user->user_id);
        $reservationStatus = $this->getReservationStatus($user->user_id);
        $recentRequests = $this->getRecentRequests($user->user_id);
        $breakfastOrders = $this->getBreakfastOrders($user->user_id);
        $hotelInfo = $this->getHotelInfo();
        
        return view('content.dashboard.guest_dashboard', compact(
            'guestInfo',
            'reservationStatus',
            'recentRequests',
            'breakfastOrders',
            'hotelInfo'
        ));
    }
    
    
    private function getGuestInfo($userId)
    {
        
        $guestDetails = GuestDetail::where('user_id', $userId)->first();
        
        
        $guestName = 'Guest'; 
        $contactNumber = null;
        
        if ($guestDetails) {
            
            $fullName = $guestDetails->full_name;
            
            
            if (!empty(trim($fullName))) {
                $guestName = trim($fullName);
            }
            
            $contactNumber = $guestDetails->contact_number;
        }
        
        
        $registration = DB::table('registrations as reg')
            ->join('reservations as res', 'reg.reservation_id', '=', 'res.reservation_id')
            ->join('rooms as r', 'res.room_id', '=', 'r.room_id')
            ->join('room_types as rt', 'r.room_type_id', '=', 'rt.room_type_id')
            ->join('guest_details as gd', 'reg.guest_details_id', '=', 'gd.guest_details_id')
            ->where('reg.user_id', $userId)
            ->whereNull('reg.check_out_date') 
            ->select(
                'reg.registration_id',
                'res.reservation_id',
                'r.room_number',
                'rt.room_type_name',
                'reg.check_in_at',
                'res.check_out_date',
                'res.adults',
                'res.children',
                'res.total_amount',
                'res.balance',
                'res.reservation_status',
                'gd.contact_number'
            )
            ->first();
        
        if (!$registration) {
            return [
                'room_number' => null,
                'room_type' => null,
                'check_in_date' => null,
                'check_out_date' => null,
                'nights_remaining' => 0,
                'nights_total' => 0,
                'nights_stayed' => 0,
                'guest_name' => $guestName,
                'adults' => 0,
                'children' => 0,
                'total_amount' => 0,
                'balance' => 0,
                'contact_number' => $contactNumber,
                'has_active_stay' => false
            ];
        }
        
        $checkInDate = Carbon::parse($registration->check_in_at);
        $checkOutDate = Carbon::parse($registration->check_out_date);
        $today = Carbon::now();
        
        
        $nightsTotal = $checkInDate->diffInDays($checkOutDate);
        $nightsStayed = $checkInDate->diffInDays($today);
        $nightsRemaining = max(0, $today->diffInDays($checkOutDate, false));
        
        return [
            'room_number' => $registration->room_number,
            'room_type' => $registration->room_type_name,
            'check_in_date' => $registration->check_in_at,
            'check_out_date' => $registration->check_out_date,
            'nights_remaining' => $nightsRemaining,
            'nights_total' => $nightsTotal,
            'nights_stayed' => min($nightsStayed, $nightsTotal),
            'guest_name' => $guestName,
            'adults' => $registration->adults,
            'children' => $registration->children,
            'total_amount' => $registration->total_amount,
            'balance' => $registration->balance,
            'contact_number' => $registration->contact_number,
            'reservation_status' => $registration->reservation_status,
            'has_active_stay' => true
        ];
    }
    
    
    private function getReservationStatus($userId)
    {
        
        $reservation = DB::table('reservations as res')
            ->join('guest_details as gd', 'res.guest_details_id', '=', 'gd.guest_details_id')
            ->leftJoin('rooms as r', 'res.room_id', '=', 'r.room_id')
            ->leftJoin('room_types as rt', 'r.room_type_id', '=', 'rt.room_type_id')
            ->leftJoin('registrations as reg', function($join) {
                $join->on('res.reservation_id', '=', 'reg.reservation_id')
                     ->whereNull('reg.check_out_date');
            })
            ->where('res.user_id', $userId)
            ->whereIn('res.reservation_status', ['pending', 'approved'])
            ->select(
                'res.reservation_id',
                'res.reservation_status',
                'res.check_in_date',
                'res.check_out_date',
                'res.booking_date',
                'res.total_amount',
                'res.balance',
                'res.reservation_fee_paid',
                'res.adults',
                'res.children',
                'res.purpose',
                'r.room_number',
                'rt.room_type_name',
                'reg.check_in_at as actual_check_in',
                DB::raw('CASE WHEN reg.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_checked_in')
            )
            ->orderBy('res.created_at', 'desc')
            ->first();
        
        if (!$reservation) {
            return [
                'has_reservation' => false,
                'status' => null,
                'room_assigned' => false
            ];
        }
        
        $checkInDate = Carbon::parse($reservation->check_in_date);
        $today = Carbon::now();
        $daysUntilCheckIn = max(0, $today->diffInDays($checkInDate, false));
        
        return [
            'has_reservation' => true,
            'status' => $reservation->reservation_status,
            'check_in_date' => $reservation->check_in_date,
            'check_out_date' => $reservation->check_out_date,
            'booking_date' => $reservation->booking_date,
            'total_amount' => $reservation->total_amount,
            'balance' => $reservation->balance,
            'reservation_fee_paid' => $reservation->reservation_fee_paid,
            'adults' => $reservation->adults,
            'children' => $reservation->children,
            'purpose' => $reservation->purpose,
            'room_number' => $reservation->room_number,
            'room_type' => $reservation->room_type_name,
            'room_assigned' => !is_null($reservation->room_number),
            'is_checked_in' => $reservation->is_checked_in,
            'days_until_checkin' => $daysUntilCheckIn
        ];
    }
    
    
    private function getRecentRequests($userId)
    {
        return DB::table('service_requests as sr')
            ->join('registrations as reg', 'sr.registration_id', '=', 'reg.registration_id')
            ->where('sr.requested_by_user_id', $userId)
            ->select(
                'sr.service_request_id',
                'sr.service_type',
                'sr.description',
                'sr.request_status',
                'sr.requested_at',
                'sr.completed_at'
            )
            ->orderBy('sr.requested_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    
    private function getBreakfastOrders($userId)
    {
        return DB::table('service_requests as sr')
            ->join('registrations as reg', 'sr.registration_id', '=', 'reg.registration_id')
            ->leftJoin('service_breakfast_orders as sbo', 'sr.service_request_id', '=', 'sbo.service_request_id')
            ->leftJoin('breakfast_menu as bm', 'sbo.breakfast_id', '=', 'bm.breakfast_id')
            ->where('sr.requested_by_user_id', $userId)
            ->where('sr.service_type', 'food')
            ->select(
                'sr.service_request_id as id',
                'sr.requested_at as order_date',
                'sr.request_status as status',
                'sr.description',
                DB::raw('GROUP_CONCAT(CONCAT(bm.meal_name, " (x", sbo.quantity, ")") SEPARATOR ", ") as items'),
                DB::raw('SUM(sbo.quantity * sbo.price_at_order) as total_price')
            )
            ->groupBy('sr.service_request_id', 'sr.requested_at', 'sr.request_status', 'sr.description')
            ->orderBy('sr.requested_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    
    private function getHotelInfo()
    {
        return [
            'wifi_password' => 'Guest2024',
            'front_desk_phone' => 'Dial 0 from room phone',
            'checkout_time' => '12:00 PM',
            'breakfast_time' => '6:00 AM - 10:00 AM',
            'emergency_number' => '911',
            'hotel_name' => 'Hotel Management System'
        ];
    }
}