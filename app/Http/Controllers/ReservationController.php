<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class ReservationController extends Controller
{

    public function showReservationForm($room_id)
    {
        try {

            $roomData = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.room_id', $room_id)
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.room_number',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->first();

            if (!$roomData) {
                return redirect()->route('frontpage.index')
                    ->with('error', 'Room not found or not available');
            }


            $room = (object) [
                'room_id' => $roomData->room_id,
                'room_type_id' => $roomData->room_type_id,
                'room_number' => $roomData->room_number,
                'image_path' => $roomData->image_path,
                'status' => $roomData->status,
                'roomType' => (object) [
                    'room_type_name' => $roomData->room_type_name,
                    'description' => $roomData->description,
                    'rate_per_night' => $roomData->rate_per_night,
                    'max_pax' => $roomData->max_pax
                ]
            ];

            return view('content.reservation.reservation-form', compact('room'));

        } catch (Exception $e) {
            \Log::error('Error loading reservation form: ' . $e->getMessage());
            return redirect()->route('frontpage.index')
                ->with('error', 'Unable to load reservation form');
        }
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'first_name' => 'required|string|max:45',
            'middle_name' => 'nullable|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'required|email|max:100',
            'contact_number' => 'required|string|max:45',
            'dob' => 'required|date|before:today',
            'arrival_date' => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'purpose' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,online'
        ]);

        DB::beginTransaction();

        try {

            $room = DB::table('rooms')
                ->where('room_id', $validated['room_id'])
                ->where('status', 'available')
                ->first();

            if (!$room) {
                return back()->with('error', 'Room is no longer available')->withInput();
            }


            $hasOverlap = DB::table('reservations')
                ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
                ->where('reservations.room_id', $validated['room_id'])
                ->where('reservations.reservation_status', '!=', 'cancelled')
                ->where('reservations.reservation_status', '!=', 'rejected')
                ->where(function($query) use ($validated) {
                    $query->whereBetween('guest_details.arrival_date', [$validated['arrival_date'], $validated['departure_date']])
                          ->orWhereBetween('guest_details.departure_date', [$validated['arrival_date'], $validated['departure_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('guest_details.arrival_date', '<=', $validated['arrival_date'])
                                ->where('guest_details.departure_date', '>=', $validated['departure_date']);
                          });
                })
                ->exists();

            if ($hasOverlap) {
                return back()->with('error', 'Room is already booked for the selected dates')->withInput();
            }


            $roomType = DB::table('room_types')
                ->where('room_type_id', $room->room_type_id)
                ->first();


            $arrival = new \DateTime($validated['arrival_date']);
            $departure = new \DateTime($validated['departure_date']);
            $nights = $arrival->diff($departure)->days;


            $subtotal = $roomType->rate_per_night * $nights;
            $reservationFee = 500;
            $totalAmount = $subtotal + $reservationFee;
            $balance = $totalAmount - $reservationFee;


            $temporaryPassword = Str::random(12);


            $guestRoleId = DB::table('roles')
                ->where('role_name', 'guest')
                ->value('role_id');


            $userId = DB::table('users')->insertGetId([
                'role_id' => $guestRoleId,
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'temporary_act' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);


            $guestDetailsId = DB::table('guest_details')->insertGetId([
                'user_id' => $userId,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'contact_number' => $validated['contact_number'],
                'dob' => $validated['dob'],
                'arrival_date' => $validated['arrival_date'],
                'departure_date' => $validated['departure_date'],
                'created_at' => now()
            ]);


            $paymentId = DB::table('payments')->insertGetId([
                'payment_method' => $validated['payment_method']
            ]);


            $reservationId = DB::table('reservations')->insertGetId([
                'user_id' => $userId,
                'guest_details_id' => $guestDetailsId,
                'payment_id' => $paymentId,
                'room_id' => $validated['room_id'],
                'reservation_fee' => $reservationFee,
                'purpose' => $validated['purpose'],
                'total_amount' => $totalAmount,
                'balance' => $balance,
                'reservation_status' => 'pending',
                'booking_date' => now()->toDateString(),
                'adults' => $validated['adults'],
                'children' => $validated['children'],
                'no_nights' => $nights,
                'created_at' => now()
            ]);

            DB::commit();





            session([
                'temp_credentials' => [
                    'email' => $validated['email'],
                    'password' => $temporaryPassword,
                    'reservation_id' => $reservationId
                ]
            ]);

            return redirect()->route('reservation.success')
                ->with('success', 'Reservation created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error creating reservation: ' . $e->getMessage());
            return back()->with('error', 'Failed to create reservation. Please try again.')
                ->withInput();
        }
    }


    public function success()
    {
        $credentials = session('temp_credentials');

        if (!$credentials) {
            return redirect()->route('frontpage.index');
        }


        $reservation = DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->join('rooms', 'reservations.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->join('payments', 'reservations.payment_id', '=', 'payments.payment_id')
            ->where('reservations.reservation_id', $credentials['reservation_id'])
            ->select(
                'reservations.*',
                'guest_details.first_name',
                'guest_details.last_name',
                'guest_details.arrival_date',
                'guest_details.departure_date',
                'rooms.room_number',
                'room_types.room_type_name',
                'room_types.rate_per_night',
                'payments.payment_method'
            )
            ->first();


        session()->forget('temp_credentials');

        return view('content.reservation.reservation-success', compact('credentials', 'reservation'));
    }


    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'arrival_date' => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date'
        ]);

        $hasOverlap = DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $validated['room_id'])
            ->where('reservations.reservation_status', '!=', 'cancelled')
            ->where('reservations.reservation_status', '!=', 'rejected')
            ->where(function($query) use ($validated) {
                $query->whereBetween('guest_details.arrival_date', [$validated['arrival_date'], $validated['departure_date']])
                      ->orWhereBetween('guest_details.departure_date', [$validated['arrival_date'], $validated['departure_date']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('guest_details.arrival_date', '<=', $validated['arrival_date'])
                            ->where('guest_details.departure_date', '>=', $validated['departure_date']);
                      });
            })
            ->exists();

        return response()->json([
            'available' => !$hasOverlap,
            'message' => $hasOverlap ? 'Room is already booked for selected dates' : 'Room is available'
        ]);
    }


    public function getBookedDates($room_id)
    {
        $bookedDates = DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $room_id)
            ->where('reservation_status', '!=', 'cancelled')
            ->where('reservation_status', '!=', 'rejected')
            ->select(
                'guest_details.arrival_date as start',
                'guest_details.departure_date as end',
                'reservations.reservation_status as status'
            )
            ->get();


        $events = $bookedDates->map(function($booking) {
            return [
                'start' => $booking->start,
                'end' => $booking->end,
                'title' => ucfirst($booking->status),
                'backgroundColor' => $this->getStatusColor($booking->status),
                'borderColor' => $this->getStatusColor($booking->status),
                'display' => 'background'
            ];
        });

        return response()->json($events);
    }


    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => '#fbbf24',
            'approved' => '#10b981',
            'rejected' => '#ef4444',
            'cancelled' => '#6b7280',
            default => '#3b82f6'
        };
    }


    public function cancel(Request $request, $reservation_id)
    {
        DB::beginTransaction();

        try {
            $reservation = DB::table('reservations')
                ->where('reservation_id', $reservation_id)
                ->first();

            if (!$reservation) {
                return back()->with('error', 'Reservation not found');
            }


            DB::table('reservations')
                ->where('reservation_id', $reservation_id)
                ->update([
                    'reservation_status' => 'cancelled'
                ]);


            DB::table('users')
                ->where('user_id', $reservation->user_id)
                ->where('temporary_act', true)
                ->delete();

            DB::commit();

            return redirect()->route('frontpage.index')
                ->with('success', 'Reservation cancelled successfully');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error cancelling reservation: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel reservation');
        }
    }
}
