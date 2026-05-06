<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $rows = DB::table('reservations as r')
            ->join('guest_details as g',   'r.guest_details_id', '=', 'g.guest_details_id')
            ->join('users as u',           'r.user_id',           '=', 'u.user_id')
            ->leftJoin('rooms as rm',      'r.room_id',           '=', 'rm.room_id')
            ->leftJoin('room_types as rt', 'rm.room_type_id',     '=', 'rt.room_type_id')
            ->leftJoin('payments as p',    'r.payment_id',        '=', 'p.payment_id')
            ->select([
                'r.reservation_id',
                'r.user_id',
                'r.guest_details_id',
                'r.payment_id',
                'r.reservation_status',
                'r.booking_date',
                'r.check_in_date',
                'r.check_out_date',
                'r.no_nights',
                'r.adults',
                'r.children',
                'r.no_of_pax',
                'r.total_amount',
                'r.balance',
                'r.reservation_fee',
                'r.reservation_fee_paid',
                'r.purpose',
                'g.first_name',
                'g.last_name',
                'g.middle_name',
                'g.contact_number',
                'g.dob',
                'u.email',
                'rm.room_id',
                'rm.room_number',
                'rm.image_path as room_image',
                'rt.room_type_name',
                'rt.rate_per_night',
                'rt.max_pax',
                'p.payment_status',
                'p.payment_method',
                'p.amount as paid_amount',
            ])
            ->orderByDesc('r.created_at')
            ->get();

        $grouped = $rows->groupBy(function ($row) {
            return $row->payment_id
                ? 'p_' . $row->payment_id
                : 'u_' . $row->user_id . '_' . $row->booking_date;
        });

        $bookings = $grouped->map(function ($groupRows) {
            $first = $groupRows->first();

            $totalAmount  = $groupRows->sum('total_amount');
            $totalBalance = $groupRows->sum('balance');
            $totalResFee  = $groupRows->sum('reservation_fee');
            $allFeePaid   = $groupRows->every(fn($r) => $r->reservation_fee_paid);

            $rooms = $groupRows->map(fn($r) => (object)[
                'reservation_id'       => $r->reservation_id,
                'reservation_status'   => $r->reservation_status,
                'room_id'              => $r->room_id,
                'room_number'          => $r->room_number,
                'room_image'           => $r->room_image,
                'room_type_name'       => $r->room_type_name,
                'rate_per_night'       => $r->rate_per_night,
                'max_pax'              => $r->max_pax,
                'check_in_date'        => $r->check_in_date,
                'check_out_date'       => $r->check_out_date,
                'no_nights'            => $r->no_nights,
                'adults'               => $r->adults,
                'children'             => $r->children,
                'no_of_pax'            => $r->no_of_pax,
                'total_amount'         => $r->total_amount,
                'balance'              => $r->balance,
                'reservation_fee'      => $r->reservation_fee,
                'reservation_fee_paid' => $r->reservation_fee_paid,
                'purpose'              => $r->purpose,
            ])->values();

            $statuses = $rooms->pluck('reservation_status')->unique()->values();
            $overallStatus = $statuses->count() === 1
                ? $statuses->first()
                : ($statuses->contains('approved') ? 'approved' : $statuses->first());

            return (object)[
                'booking_key'            => $first->payment_id ? 'p_' . $first->payment_id : 'u_' . $first->user_id . '_' . $first->booking_date,
                'payment_id'             => $first->payment_id,
                'user_id'                => $first->user_id,
                'guest_details_id'       => $first->guest_details_id,
                'first_name'             => $first->first_name,
                'middle_name'            => $first->middle_name,
                'last_name'              => $first->last_name,
                'contact_number'         => $first->contact_number,
                'dob'                    => $first->dob,
                'email'                  => $first->email,
                'booking_date'           => $first->booking_date,
                'check_in_date'          => $first->check_in_date,
                'check_out_date'         => $first->check_out_date,
                'payment_status'         => $first->payment_status,
                'payment_method'         => $first->payment_method,
                'paid_amount'            => $first->paid_amount,
                'overall_status'         => $overallStatus,
                'room_count'             => $rooms->count(),
                'total_amount'           => $totalAmount,
                'total_balance'          => $totalBalance,
                'total_reservation_fee'  => $totalResFee,
                'all_fee_paid'           => $allFeePaid,
                'rooms'                  => $rooms,
                'reservation_ids'        => $rooms->pluck('reservation_id')->toArray(),
                'primary_reservation_id' => $first->reservation_id,
            ];
        })->values();

        $stats = $this->buildStats();

        return view('content.admin.reservation.index', compact('bookings', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW  (JSON — used by the modal)
    // ─────────────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $row = DB::table('reservations')->where('reservation_id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        $siblings = $row->payment_id
            ? DB::table('reservations')->where('payment_id', $row->payment_id)->pluck('reservation_id')
            : collect([$id]);

        $rows = DB::table('reservations as r')
            ->join('guest_details as g',   'r.guest_details_id', '=', 'g.guest_details_id')
            ->join('users as u',           'r.user_id',           '=', 'u.user_id')
            ->leftJoin('rooms as rm',      'r.room_id',           '=', 'rm.room_id')
            ->leftJoin('room_types as rt', 'rm.room_type_id',     '=', 'rt.room_type_id')
            ->leftJoin('payments as p',    'r.payment_id',        '=', 'p.payment_id')
            ->whereIn('r.reservation_id', $siblings)
            ->select([
                'r.reservation_id', 'r.user_id', 'r.payment_id', 'r.guest_details_id',
                'r.reservation_status', 'r.booking_date',
                'r.check_in_date', 'r.check_out_date', 'r.no_nights',
                'r.adults', 'r.children', 'r.no_of_pax',
                'r.total_amount', 'r.balance', 'r.reservation_fee',
                'r.reservation_fee_paid', 'r.purpose',
                'g.first_name', 'g.last_name', 'g.middle_name',
                'g.contact_number', 'g.dob',
                'u.email',
                'rm.room_id', 'rm.room_number', 'rm.image_path as room_image',
                'rt.room_type_name', 'rt.rate_per_night', 'rt.max_pax',
                'rt.description as room_description',
                'p.payment_status', 'p.payment_method', 'p.amount as paid_amount',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }

        $first   = $rows->first();
        $booking = [
            'primary_reservation_id' => $first->reservation_id,
            'payment_id'             => $first->payment_id,
            'user_id'                => $first->user_id,
            'guest'                  => [
                'first_name'     => $first->first_name,
                'middle_name'    => $first->middle_name,
                'last_name'      => $first->last_name,
                'email'          => $first->email,
                'contact_number' => $first->contact_number,
                'dob'            => $first->dob,
            ],
            'booking_date'   => $first->booking_date,
            'payment_method' => $first->payment_method,
            'payment_status' => $first->payment_status,
            'paid_amount'    => $first->paid_amount,
            'total_amount'   => $rows->sum('total_amount'),
            'total_balance'  => $rows->sum('balance'),
            'total_res_fee'  => $rows->sum('reservation_fee'),
            'all_fee_paid'   => $rows->every(fn($r) => $r->reservation_fee_paid),
            'room_count'     => $rows->count(),
            'rooms'          => $rows->map(fn($r) => [
                'reservation_id'       => $r->reservation_id,
                'reservation_status'   => $r->reservation_status,
                'room_id'              => $r->room_id,
                'room_number'          => $r->room_number,
                'room_type_name'       => $r->room_type_name,
                'room_description'     => $r->room_description,
                'room_image'           => $r->room_image ? asset('storage/' . $r->room_image) : null,
                'rate_per_night'       => $r->rate_per_night,
                'max_pax'              => $r->max_pax,
                'check_in_date'        => $r->check_in_date,
                'check_out_date'       => $r->check_out_date,
                'no_nights'            => $r->no_nights,
                'adults'               => $r->adults,
                'children'             => $r->children,
                'no_of_pax'            => $r->no_of_pax,
                'total_amount'         => $r->total_amount,
                'balance'              => $r->balance,
                'reservation_fee'      => $r->reservation_fee,
                'reservation_fee_paid' => (bool) $r->reservation_fee_paid,
                'purpose'              => $r->purpose,
            ])->values()->toArray(),
        ];

        return response()->json(['booking' => $booking]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SINGLE ROOM STATUS CHANGES
    // ─────────────────────────────────────────────────────────────────────────
    public function approve($id) { return $this->changeStatus($id, 'approved',  'Reservation approved.'); }
    public function reject($id)  { return $this->changeStatus($id, 'rejected',  'Reservation rejected.'); }
    public function cancel($id)  { return $this->changeStatus($id, 'cancelled', 'Reservation cancelled.'); }

    // ─────────────────────────────────────────────────────────────────────────
    // BULK (BOOKING-LEVEL) STATUS CHANGES
    // ─────────────────────────────────────────────────────────────────────────
    public function approveBooking($paymentId) { return $this->changeBookingStatus($paymentId, 'approved',  'All rooms approved.'); }
    public function rejectBooking($paymentId)  { return $this->changeBookingStatus($paymentId, 'rejected',  'All rooms rejected.'); }
    public function cancelBooking($paymentId)  { return $this->changeBookingStatus($paymentId, 'cancelled', 'All rooms cancelled.'); }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        $r = DB::table('reservations')->where('reservation_id', $id)->first();
        if (!$r) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }
        if ($r->reservation_status === 'approved') {
            return response()->json(['message' => 'Cannot delete an approved reservation. Cancel it first.'], 422);
        }

        DB::transaction(function () use ($id) {
            $registrationIds = DB::table('registrations')
                ->where('reservation_id', $id)
                ->pluck('registration_id');

            if ($registrationIds->isNotEmpty()) {
                DB::table('service_breakfast_orders')
                    ->whereIn('service_request_id', function ($q) use ($registrationIds) {
                        $q->select('service_request_id')
                          ->from('service_requests')
                          ->whereIn('registration_id', $registrationIds);
                    })->delete();

                DB::table('service_requests')
                    ->whereIn('registration_id', $registrationIds)
                    ->delete();

                DB::table('registrations')->where('reservation_id', $id)->delete();
            }

            DB::table('reservations')
                ->where('reservation_id', $id)
                ->update(['guest_details_id' => null, 'payment_id' => null]);

            DB::table('reservations')->where('reservation_id', $id)->delete();
        });

        return response()->json(['stats' => $this->buildStats(), 'message' => 'Deleted successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function changeStatus($id, string $status, string $message)
    {
        if (!DB::table('reservations')->where('reservation_id', $id)->exists()) {
            return response()->json(['message' => 'Reservation not found.'], 404);
        }
        DB::table('reservations')->where('reservation_id', $id)->update(['reservation_status' => $status]);

        return response()->json([
            'reservation_id' => (int) $id,
            'new_status'     => $status,
            'stats'          => $this->buildStats(),
            'message'        => $message,
        ]);
    }

    private function changeBookingStatus($paymentId, string $status, string $message)
    {
        $ids = DB::table('reservations')->where('payment_id', $paymentId)->pluck('reservation_id');
        if ($ids->isEmpty()) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }
        DB::table('reservations')->where('payment_id', $paymentId)->update(['reservation_status' => $status]);

        return response()->json([
            'payment_id' => $paymentId,
            'new_status' => $status,
            'stats'      => $this->buildStats(),
            'message'    => $message,
        ]);
    }

    private function buildStats(): array
    {
        $all = DB::table('reservations')->select('reservation_status')->get();
        return [
            'total'     => $all->count(),
            'pending'   => $all->where('reservation_status', 'pending')->count(),
            'approved'  => $all->where('reservation_status', 'approved')->count(),
            'rejected'  => $all->where('reservation_status', 'rejected')->count(),
            'cancelled' => $all->where('reservation_status', 'cancelled')->count(),
        ];
    }
}