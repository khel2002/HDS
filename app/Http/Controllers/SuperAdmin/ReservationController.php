<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\GuestDetail;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all reservations with related data
        $reservations = DB::table('reservations as r')
            ->join('guest_details as gd', 'r.guest_details_id', '=', 'gd.guest_details_id')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->leftJoin('rooms as rm', 'r.room_id', '=', 'rm.room_id')
            ->leftJoin('room_types as rt', 'rm.room_type_id', '=', 'rt.room_type_id')
            ->select(
                'r.*',
                'gd.first_name as guest_first_name',
                'gd.middle_name as guest_middle_name',
                'gd.last_name as guest_last_name',
                'gd.contact_number as guest_contact',
                'gd.dob as guest_dob',
                'u.email as guest_email',
                'rm.room_number',
                'rt.room_type_name',
                'rt.room_type_id'
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('reservation_status', 'pending')->count(),
            'approved_reservations' => Reservation::where('reservation_status', 'approved')->count(),
            'total_revenue' => Reservation::whereIn('reservation_status', ['approved'])->sum('total_amount')
        ];

        // Get available rooms
        $rooms = DB::table('rooms as r')
            ->join('room_types as rt', 'r.room_type_id', '=', 'rt.room_type_id')
            ->where('r.status', 'available')
            ->select(
                'r.room_id',
                'r.room_number',
                'rt.room_type_name',
                'rt.rate_per_night'
            )
            ->get();

        // Get room types for filter
        $roomTypes = RoomType::all();

        return view('content.super-admin.reservation.index', compact(
            'reservations',
            'stats',
            'rooms',
            'roomTypes'
        ));
    }

    /**
     * Store a newly created reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:45',
            'middle_name' => 'nullable|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'required|email|max:100',
            'contact_number' => 'required|string|max:45',
            'dob' => 'nullable|date',
            'room_id' => 'required|exists:rooms,room_id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'purpose' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        
        try {
            // Check if user exists with this email
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                // Create new guest user
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make(uniqid()), // Random password
                    'role_id' => 4, // Guest role
                    'temporary_act' => 1,
                    'STATUS' => 'active'
                ]);
            }

            // Create guest details
            $guestDetails = GuestDetail::create([
                'user_id' => $user->user_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'contact_number' => $request->contact_number,
                'dob' => $request->dob,
                'arrival_date' => $request->check_in_date,
                'departure_date' => $request->check_out_date
            ]);

            // Calculate nights and total amount
            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);
            $nights = $checkIn->diffInDays($checkOut);

            $room = Room::with('roomType')->find($request->room_id);
            $totalAmount = $nights * $room->roomType->rate_per_night;
            $reservationFee = 500; // Fixed reservation fee
            $balance = $totalAmount - $reservationFee;

            // Create reservation
            $reservation = Reservation::create([
                'user_id' => $user->user_id,
                'guest_details_id' => $guestDetails->guest_details_id,
                'room_id' => $request->room_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'no_nights' => $nights,
                'purpose' => $request->purpose,
                'total_amount' => $totalAmount,
                'balance' => $balance,
                'reservation_fee' => $reservationFee,
                'reservation_status' => 'approved', // Auto-approve for admin-created
                'booking_date' => now(),
                'reservation_fee_paid' => 0
            ]);

            DB::commit();

            return redirect()->route('super_admin.reservations.index')
                ->with('success', 'Reservation created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create reservation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified reservation
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $reservation = DB::table('reservations as r')
            ->join('guest_details as gd', 'r.guest_details_id', '=', 'gd.guest_details_id')
            ->join('users as u', 'r.user_id', '=', 'u.user_id')
            ->leftJoin('rooms as rm', 'r.room_id', '=', 'rm.room_id')
            ->leftJoin('room_types as rt', 'rm.room_type_id', '=', 'rt.room_type_id')
            ->where('r.reservation_id', $id)
            ->select(
                'r.*',
                'gd.first_name as guest_first_name',
                'gd.middle_name as guest_middle_name',
                'gd.last_name as guest_last_name',
                'gd.contact_number as guest_contact',
                'gd.dob as guest_dob',
                'u.email as guest_email',
                'rm.room_number',
                'rt.room_type_name'
            )
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }

    /**
     * Update the specified reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:45',
            'middle_name' => 'nullable|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'nullable|email|max:100',
            'contact_number' => 'nullable|string|max:45',
            'room_id' => 'nullable|exists:rooms,room_id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'reservation_status' => 'required|in:pending,approved,rejected,cancelled',
            'purpose' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        
        try {
            $reservation = Reservation::findOrFail($id);

            // Update guest details
            $guestDetails = GuestDetail::find($reservation->guest_details_id);
            $guestDetails->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'contact_number' => $request->contact_number,
                'arrival_date' => $request->check_in_date,
                'departure_date' => $request->check_out_date
            ]);

            // Update user email if provided
            if ($request->email) {
                $user = User::find($reservation->user_id);
                $user->update(['email' => $request->email]);
            }

            // Recalculate if dates changed
            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);
            $nights = $checkIn->diffInDays($checkOut);

            $updateData = [
                'room_id' => $request->room_id,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'no_nights' => $nights,
                'purpose' => $request->purpose,
                'reservation_status' => $request->reservation_status
            ];

            // Recalculate total if room changed
            if ($request->room_id != $reservation->room_id && $request->room_id) {
                $room = Room::with('roomType')->find($request->room_id);
                $totalAmount = $nights * $room->roomType->rate_per_night;
                $updateData['total_amount'] = $totalAmount;
                $updateData['balance'] = $totalAmount - $reservation->reservation_fee;
            }

            $reservation->update($updateData);

            DB::commit();

            return redirect()->route('super_admin.reservations.index')
                ->with('success', 'Reservation updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update reservation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Approve a reservation
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            
            if ($reservation->reservation_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending reservations can be approved'
                ], 400);
            }

            $reservation->update([
                'reservation_status' => 'approved'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservation approved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            
            if ($reservation->reservation_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending reservations can be rejected'
                ], 400);
            }

            $reservation->update([
                'reservation_status' => 'rejected'
            ]);

            // TODO: Send notification email to guest with reason

            return response()->json([
                'success' => true,
                'message' => 'Reservation rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            
            if ($reservation->reservation_status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation is already cancelled'
                ], 400);
            }

            $reservation->update([
                'reservation_status' => 'cancelled'
            ]);

            // TODO: Process refund if applicable
            // TODO: Send cancellation email to guest

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified reservation
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            
            // Only allow deletion of cancelled or rejected reservations
            if (!in_array($reservation->reservation_status, ['cancelled', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only cancelled or rejected reservations can be deleted'
                ], 400);
            }

            $reservation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reservation: ' . $e->getMessage()
            ], 500);
        }
    }
}