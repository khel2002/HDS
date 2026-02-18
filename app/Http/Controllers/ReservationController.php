<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
                'room_id'      => $roomData->room_id,
                'room_type_id' => $roomData->room_type_id,
                'room_number'  => $roomData->room_number,
                'image_path'   => $roomData->image_path,
                'status'       => $roomData->status,
                'roomType'     => (object) [
                    'room_type_name' => $roomData->room_type_name,
                    'description'    => $roomData->description,
                    'rate_per_night' => $roomData->rate_per_night,
                    'max_pax'        => $roomData->max_pax,
                ],
            ];

            return view('content.reservation.reservation-form', compact('room'));

        } catch (Exception $e) {
            \Log::error('Error loading reservation form: ' . $e->getMessage());
            return redirect()->route('frontpage.index')->with('error', 'Unable to load reservation form');
        }
    }


    // ─────────────────────────────────────────────────────────────
    // GET AVAILABLE ROOMS
    // ─────────────────────────────────────────────────────────────
    public function getAvailableRooms(Request $request)
    {
        try {
            $validated = $request->validate([
                'arrival_date'     => 'required|date',
                'departure_date'   => 'required|date|after:arrival_date',
                'exclude_room_ids' => 'array',
            ]);

            $availableRooms = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id',
                    'rooms.room_number',
                    'rooms.image_path',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->get();

            if (!empty($validated['exclude_room_ids'])) {
                $availableRooms = $availableRooms->whereNotIn('room_id', $validated['exclude_room_ids']);
            }

            $today    = now()->toDateString();
            $tomorrow = now()->addDay()->toDateString();
            $datesAreReal = !(
                $validated['arrival_date']   === $today &&
                $validated['departure_date'] === $tomorrow
            );

            if ($datesAreReal) {
                $filteredRooms = $availableRooms->filter(function ($room) use ($validated) {
                    return !$this->checkDateOverlap(
                        $room->room_id,
                        $validated['arrival_date'],
                        $validated['departure_date']
                    );
                })->values();
            } else {
                $filteredRooms = $availableRooms->values();
            }

            $filteredRooms = $filteredRooms->map(function ($room) {
                $room->image_path = asset('storage/' . $room->image_path);
                return $room;
            });

            return response()->json(['success' => true, 'rooms' => $filteredRooms]);

        } catch (Exception $e) {
            \Log::error('Error fetching available rooms: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to fetch available rooms'], 500);
        }
    }


    // ─────────────────────────────────────────────────────────────
    // STORE  (cash reservations)
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rooms'                              => 'required|json',
            'rooms_data'                         => 'required|array|min:1',
            'rooms_data.*.arrival_date'          => 'required|date|after_or_equal:today',
            'rooms_data.*.departure_date'        => 'required|date|after:rooms_data.*.arrival_date',
            'rooms_data.*.number_of_guests'      => 'required|integer|min:1',
            'rooms_data.*.special_requests'      => 'nullable|string|max:500',
            'rooms_data.*.guests'                => 'required|array|min:1',
            'rooms_data.*.guests.*.first_name'   => 'required|string|max:45',
            'rooms_data.*.guests.*.last_name'    => 'required|string|max:45',
            'rooms_data.*.guests.*.email'        => 'nullable|email|max:100',
            'rooms_data.*.guests.*.phone'        => 'nullable|string|max:45',
            'rooms_data.*.guests.*.address'      => 'nullable|string|max:255',
            'rooms_data.*.guests.*.arrival_time' => 'nullable',
            'first_name'                         => 'required|string|max:45',
            'middle_name'                        => 'nullable|string|max:45',
            'last_name'                          => 'required|string|max:45',
            'email'                              => 'required|email|max:100',
            'contact_number'                     => 'required|string|max:45',
            'dob'                                => 'required|date|before:today',
            'payment_method'                     => 'required|in:cash,online',
        ]);

        DB::beginTransaction();

        try {
            $roomsList = json_decode($validated['rooms'], true);
            if (empty($roomsList)) {
                return back()->with('error', 'No rooms selected')->withInput();
            }

            $roomIds   = array_column($roomsList, 'room_id');
            $roomsData = $validated['rooms_data'];

            // Verify all rooms are still available
            $rooms = DB::table('rooms')
                ->whereIn('room_id', $roomIds)
                ->where('status', 'available')
                ->get();

            if ($rooms->count() !== count($roomIds)) {
                return back()->with('error', 'One or more rooms are no longer available')->withInput();
            }

            // Check date overlaps per room
            foreach ($roomIds as $index => $roomId) {
                $arrivalDate   = $roomsData[$index]['arrival_date'];
                $departureDate = $roomsData[$index]['departure_date'];

                if ($this->checkDateOverlap($roomId, $arrivalDate, $departureDate)) {
                    return back()->with('error', 'Room ' . ($index + 1) . ' is already booked for the selected dates')->withInput();
                }
            }

            // Get room pricing
            $roomTypes = DB::table('room_types')
                ->join('rooms', 'room_types.room_type_id', '=', 'rooms.room_type_id')
                ->whereIn('rooms.room_id', $roomIds)
                ->select('rooms.room_id', 'rooms.room_number', 'room_types.room_type_name', 'room_types.rate_per_night')
                ->get()
                ->keyBy('room_id');

            $primaryArrival   = $roomsData[0]['arrival_date'];
            $primaryDeparture = $roomsData[0]['departure_date'];
            $nights = (new \DateTime($primaryArrival))->diff(new \DateTime($primaryDeparture))->days;

            $reservationFeePerRoom = 500;
            $totalReservationFee   = $reservationFeePerRoom * count($roomIds);
            $totalRoomCost         = $roomTypes->sum('rate_per_night');
            $subtotal              = $totalRoomCost * $nights;
            $totalAmount           = $subtotal;
            $balance               = $totalAmount - $totalReservationFee;

            // Create user account
            $temporaryPassword = Str::random(12);
            $guestRoleId = DB::table('roles')->where('role_name', 'guest')->value('role_id');

            $userId = DB::table('users')->insertGetId([
                'role_id'       => $guestRoleId,
                'email'         => $validated['email'],
                'password'      => Hash::make($temporaryPassword),
                'temporary_act' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Create guest details
            $guestDetailsId = DB::table('guest_details')->insertGetId([
                'user_id'        => $userId,
                'first_name'     => $validated['first_name'],
                'middle_name'    => $validated['middle_name'] ?? null,
                'last_name'      => $validated['last_name'],
                'contact_number' => $validated['contact_number'],
                'dob'            => $validated['dob'],
                'arrival_date'   => $primaryArrival,
                'departure_date' => $primaryDeparture,
                'created_at'     => now(),
            ]);

            // Create payment record
            $paymentId = DB::table('payments')->insertGetId([
                'payment_method' => $validated['payment_method'],
                'amount'         => $totalReservationFee,
                'payment_date'   => now(),
                'payment_status' => 'pending',
            ]);

            // Create one reservation per room
            $reservationIds = [];
            foreach ($roomIds as $index => $roomId) {
                $rd            = $roomsData[$index];
                $roomArrival   = $rd['arrival_date'];
                $roomDeparture = $rd['departure_date'];
                $roomNights    = (new \DateTime($roomArrival))->diff(new \DateTime($roomDeparture))->days;
                $roomRate      = $roomTypes[$roomId]->rate_per_night;
                $roomSubtotal  = $roomRate * $roomNights;
                $roomBalance   = $roomSubtotal - $reservationFeePerRoom;
                $guestCount    = (int) $rd['number_of_guests'];

                $reservationId = DB::table('reservations')->insertGetId([
                    'user_id'              => $userId,
                    'guest_details_id'     => $guestDetailsId,
                    'payment_id'           => $paymentId,
                    'room_id'              => $roomId,
                    'reservation_fee'      => $reservationFeePerRoom,
                    'purpose'              => $rd['special_requests'] ?? null,
                    'total_amount'         => $roomSubtotal,
                    'balance'              => $roomBalance,
                    'reservation_status'   => 'pending',
                    'booking_date'         => now()->toDateString(),
                    'check_in_date'        => $roomArrival,
                    'check_out_date'       => $roomDeparture,
                    'adults'               => $guestCount,
                    'children'             => 0,
                    'no_nights'            => $roomNights,
                    'reservation_fee_paid' => 0,
                    'created_at'           => now(),
                ]);

                $reservationIds[] = $reservationId;
            }

            DB::commit();

            // Build room details for session / email
            $roomDetails = [];
            foreach ($roomIds as $roomId) {
                $roomDetails[] = [
                    'room_number'    => $roomTypes[$roomId]->room_number,
                    'room_type_name' => $roomTypes[$roomId]->room_type_name,
                ];
            }

            $emailData = [
                'reservation_ids'  => $reservationIds,
                'reservation_id'   => $reservationIds[0],
                'email'            => $validated['email'],
                'password'         => $temporaryPassword,
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'rooms'            => $roomDetails,
                'room_count'       => count($roomIds),
                'room_number'      => $roomTypes[$roomIds[0]]->room_number,
                'room_type_name'   => $roomTypes[$roomIds[0]]->room_type_name,
                'arrival_date'     => $primaryArrival,
                'departure_date'   => $primaryDeparture,
                'total_amount'     => $totalAmount,
                'balance'          => $balance,
                'no_nights'        => $nights,
                'reservation_fee'  => $totalReservationFee,
            ];

            // Send confirmation email (non-blocking)
            $this->sendReservationEmail($emailData);

            session([
                'temp_credentials' => [
                    'email'           => $validated['email'],
                    'password'        => $temporaryPassword,
                    'reservation_ids' => $reservationIds,
                ],
                'reservation_data' => $emailData,
                'payment_success'  => true,
                'payment_method'   => 'cash',
            ]);
            session()->save();

            return redirect()->route('reservation.confirmation')
                ->with('success', 'Reservation created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error creating reservation: ' . $e->getMessage());
            return back()->with('error', 'Failed to create reservation. Please try again.')->withInput();
        }
    }


    // ─────────────────────────────────────────────────────────────
    // CONFIRMATION PAGE
    // ─────────────────────────────────────────────────────────────
    public function confirmation()
    {
        $credentials     = session('temp_credentials');
        $reservationData = session('reservation_data');
        $paymentSuccess  = session('payment_success');
        $paymentMethod   = session('payment_method', 'cash');

        // If no session data at all, redirect home
        if (!$credentials || !$reservationData || !$paymentSuccess) {
            \Log::warning('reservation.confirmation: missing session data', [
                'has_credentials'     => (bool) $credentials,
                'has_reservation_data'=> (bool) $reservationData,
                'has_payment_success' => (bool) $paymentSuccess,
            ]);
            return redirect()->route('frontpage.index')
                ->with('error', 'Your session has expired. Please check your email for confirmation details.');
        }

        // Do NOT forget session here — the blade needs it to render.
        // Clear it only after the page is fully loaded (or on next meaningful action).

        return view('content.reservation.confirmation', compact(
            'credentials', 'reservationData', 'paymentSuccess', 'paymentMethod'
        ));
    }


    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────
    private function checkDateOverlap($roomId, $arrivalDate, $departureDate): bool
    {
        return DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $roomId)
            ->whereNotIn('reservations.reservation_status', ['cancelled', 'rejected'])
            ->where(function ($query) use ($arrivalDate, $departureDate) {
                $query->whereBetween('guest_details.arrival_date', [$arrivalDate, $departureDate])
                      ->orWhereBetween('guest_details.departure_date', [$arrivalDate, $departureDate])
                      ->orWhere(function ($q) use ($arrivalDate, $departureDate) {
                          $q->where('guest_details.arrival_date', '<=', $arrivalDate)
                            ->where('guest_details.departure_date', '>=', $departureDate);
                      });
            })
            ->exists();
    }


    private function sendReservationEmail(array $data): void
    {
        try {
            \Log::info('Sending reservation email to: ' . $data['email']);

            Mail::send('emails.reservation-confirmation', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['first_name'] . ' ' . $data['last_name'])
                        ->subject('Reservation Confirmation - Hotel De SLSU');
            });

            \Log::info('Reservation email sent successfully to: ' . $data['email']);

        } catch (Exception $e) {
            // Email failure must NOT roll back the reservation
            \Log::error('Failed to send reservation email: ' . $e->getMessage(), [
                'to'    => $data['email'],
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id'        => 'required|exists:rooms,room_id',
            'arrival_date'   => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date',
        ]);

        $hasOverlap = $this->checkDateOverlap(
            $validated['room_id'],
            $validated['arrival_date'],
            $validated['departure_date']
        );

        return response()->json([
            'available' => !$hasOverlap,
            'message'   => $hasOverlap
                ? 'Room is already booked for selected dates'
                : 'Room is available',
        ]);
    }


    public function getBookedDates($room_id)
    {
        $bookedDates = DB::table('reservations')
            ->join('guest_details', 'reservations.guest_details_id', '=', 'guest_details.guest_details_id')
            ->where('reservations.room_id', $room_id)
            ->whereNotIn('reservation_status', ['cancelled', 'rejected'])
            ->select(
                'guest_details.arrival_date as start',
                'guest_details.departure_date as end',
                'reservations.reservation_status as status'
            )
            ->get();

        $events = $bookedDates->map(function ($booking) {
            return [
                'start'           => $booking->start,
                'end'             => $booking->end,
                'title'           => ucfirst($booking->status),
                'backgroundColor' => $this->getStatusColor($booking->status),
                'borderColor'     => $this->getStatusColor($booking->status),
                'display'         => 'background',
            ];
        });

        return response()->json($events);
    }


    private function getStatusColor($status): string
    {
        return match ($status) {
            'pending'   => '#fbbf24',
            'approved'  => '#10b981',
            'rejected'  => '#ef4444',
            'cancelled' => '#6b7280',
            default     => '#3b82f6',
        };
    }


    public function cancel(Request $request, $reservation_id)
    {
        DB::beginTransaction();

        try {
            $reservation = DB::table('reservations')->where('reservation_id', $reservation_id)->first();

            if (!$reservation) {
                return back()->with('error', 'Reservation not found');
            }

            DB::table('reservations')
                ->where('reservation_id', $reservation_id)
                ->update(['reservation_status' => 'cancelled']);

            DB::table('users')
                ->where('user_id', $reservation->user_id)
                ->where('temporary_act', true)
                ->delete();

            DB::commit();

            return redirect()->route('frontpage.index')->with('success', 'Reservation cancelled successfully');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error cancelling reservation: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel reservation');
        }
    }
}