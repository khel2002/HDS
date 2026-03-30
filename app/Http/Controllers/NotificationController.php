<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    // ── Guest: GET /guest/notifications ─────────────────────────────────────
    public function guest()
    {
        $userId        = Auth::id();
        $notifications = [];
        $tz            = config('app.timezone', 'Asia/Manila');

        // ── Resolve active registration (checked-in) ─────────────────────────
        $registration = DB::table('registrations as reg')
            ->join('reservations as r', 'r.reservation_id', '=', 'reg.reservation_id')
            ->where('reg.user_id', $userId)
            ->whereNull('reg.check_out_date')
            ->whereNotNull('reg.check_in_at')
            ->select(
                'reg.registration_id',
                'r.check_out_date',
                'r.reservation_id',
                'r.reservation_status'
            )
            ->latest('reg.check_in_at')
            ->first();

        // ── Fallback: approved/pending reservation not yet checked in ─────────
        $pendingReservation = null;
        if (! $registration) {
            $pendingReservation = DB::table('reservations')
                ->where('user_id', $userId)
                ->whereIn('reservation_status', ['pending', 'approved'])
                ->select('reservation_id', 'reservation_status', 'check_out_date')
                ->latest('created_at')
                ->first();
        }

        // ── 1. Service request status notifications ───────────────────────────
        if ($registration) {
            $requests = DB::table('service_requests')
                ->where('registration_id', $registration->registration_id)
                ->orderByDesc('requested_at')
                ->limit(10)
                ->get();

            $cfg = [
                'food' => [
                    'pending'     => ['Your breakfast order has been received.',  'ri-restaurant-line',      'warning'],
                    'in_progress' => ['Your breakfast is being prepared!',         'ri-loader-4-line',        'info'   ],
                    'delivering'  => ['Your breakfast is on its way!',             'ri-e-bike-2-line',        'primary'],
                    'completed'   => ['Your breakfast has been delivered.',        'ri-checkbox-circle-line', 'success'],
                    'cancelled'   => ['Your breakfast order was cancelled.',       'ri-close-circle-line',    'danger' ],
                ],
                'room_service' => [
                    'pending'     => ['Your room service request was received.',  'ri-concierge-bell-line',  'warning'],
                    'in_progress' => ['Staff is attending to your request.',       'ri-loader-4-line',        'info'   ],
                    'completed'   => ['Your room service request is complete.',   'ri-checkbox-circle-line', 'success'],
                    'cancelled'   => ['Your room service request was cancelled.', 'ri-close-circle-line',    'danger' ],
                ],
            ];

            foreach ($requests as $req) {
                $entry = $cfg[$req->service_type][$req->request_status] ?? null;
                if (! $entry) continue;

                [$msg, $icon, $color] = $entry;
                $notifications[] = [
                    // id = requestId + status → changes when status changes → triggers toast
                    'id'      => 'req_' . $req->service_request_id . '_' . $req->request_status,
                    'message' => $msg,
                    'icon'    => $icon,
                    'color'   => $color,
                    'time'    => $this->humanTime($req->requested_at, $tz),
                    'type'    => 'request',
                ];
            }
        }

        // ── 2. Checkout proximity ─────────────────────────────────────────────
        $checkoutDate = $registration?->check_out_date ?? $pendingReservation?->check_out_date;

        if ($checkoutDate) {
            $daysLeft = Carbon::today($tz)->diffInDays(
                Carbon::parse($checkoutDate)->startOfDay(),
                false
            );

            if ($daysLeft === 0) {
                $notifications[] = [
                    'id'      => 'checkout_today_' . $checkoutDate,
                    'message' => 'Your check-out is today. We hope you enjoyed your stay!',
                    'icon'    => 'ri-logout-box-line',
                    'color'   => 'danger',
                    'time'    => 'Today',
                    'type'    => 'checkout',
                ];
            } elseif ($daysLeft === 1) {
                $notifications[] = [
                    'id'      => 'checkout_tomorrow_' . $checkoutDate,
                    'message' => 'Reminder: your check-out is tomorrow.',
                    'icon'    => 'ri-calendar-event-line',
                    'color'   => 'warning',
                    'time'    => 'Tomorrow',
                    'type'    => 'checkout',
                ];
            }
        }

        // ── 3. Reservation status (not yet checked in) ────────────────────────
        if (! $registration && $pendingReservation) {
            if ($pendingReservation->reservation_status === 'approved') {
                $notifications[] = [
                    'id'      => 'res_approved_' . $pendingReservation->reservation_id,
                    'message' => 'Your reservation has been approved! Ready for check-in.',
                    'icon'    => 'ri-checkbox-circle-line',
                    'color'   => 'success',
                    'time'    => 'Recently',
                    'type'    => 'reservation',
                ];
            } elseif ($pendingReservation->reservation_status === 'pending') {
                $notifications[] = [
                    'id'      => 'res_pending_' . $pendingReservation->reservation_id,
                    'message' => 'Your reservation is under review.',
                    'icon'    => 'ri-time-line',
                    'color'   => 'warning',
                    'time'    => 'Recently',
                    'type'    => 'reservation',
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'notifications' => $notifications,
            'count'         => count($notifications),
        ]);
    }

    // ── Super admin: GET /super-admin/notifications ───────────────────────────
    public function superadmin()
    {
        $notifications = [];
        $tz            = config('app.timezone', 'Asia/Manila');

        // ── Pending reservations ──────────────────────────────────────────────
        $pendingReservations = DB::table('reservations')
            ->where('reservation_status', 'pending')
            ->count();

        if ($pendingReservations > 0) {
            $notifications[] = [
                'id'      => 'pending_res_' . $pendingReservations,
                'message' => $pendingReservations . ' reservation' . ($pendingReservations > 1 ? 's' : '') . ' awaiting approval.',
                'icon'    => 'ri-calendar-check-line',
                'color'   => 'warning',
                'time'    => 'Now',
                'type'    => 'reservation',
            ];
        }

        // ── Pending service requests — split by type ──────────────────────────
        $pendingServices = DB::table('service_requests')
            ->where('request_status', 'pending')
            ->selectRaw('service_type, COUNT(*) as total')
            ->groupBy('service_type')
            ->get();

        foreach ($pendingServices as $row) {
            $isFood  = $row->service_type === 'food';
            $label   = $isFood ? 'breakfast order' : 'room service request';
            $labelPl = $isFood ? 'breakfast orders' : 'room service requests';
            $notifications[] = [
                'id'      => 'pending_' . $row->service_type . '_' . $row->total,
                'message' => $row->total . ' ' . ($row->total > 1 ? $labelPl : $label) . ' pending.',
                'icon'    => $isFood ? 'ri-restaurant-line' : 'ri-concierge-bell-line',
                'color'   => 'danger',
                'time'    => 'Now',
                'type'    => 'service',
            ];
        }
        $pendingCheckouts = DB::table('checkout_requests as cr')
        ->join('registrations as reg', 'reg.registration_id', '=', 'cr.registration_id')
        ->join('reservations as r',    'r.reservation_id',    '=', 'reg.reservation_id')
        ->leftJoin('rooms as rm',      'rm.room_id',           '=', 'r.room_id')
        ->where('cr.status', 'pending')
        ->select('cr.id', 'cr.created_at', 'rm.room_number')
        ->orderByDesc('cr.created_at')
        ->get();
        
        foreach ($pendingCheckouts as $co) {
            $room = $co->room_number ? 'Room ' . $co->room_number : 'a guest';
            $notifications[] = [
                'id'      => 'checkout_req_' . $co->id,
                'message' => 'Checkout request from ' . $room . ' — room inspection needed.',
                'icon'    => 'ri-logout-box-line',
                'color'   => 'warning',
                'time'    => $this->humanTime($co->created_at, $tz),
                'type'    => 'checkout_request',
            ];
        }
        $inspectingCount = DB::table('checkout_requests')
            ->where('status', 'inspecting')
            ->count();
        
        if ($inspectingCount > 0) {
            $notifications[] = [
                'id'      => 'inspecting_' . $inspectingCount,
                'message' => $inspectingCount . ' room' . ($inspectingCount > 1 ? 's' : '') . ' currently under inspection.',
                'icon'    => 'ri-search-eye-line',
                'color'   => 'info',
                'time'    => 'Now',
                'type'    => 'checkout_request',
            ];
        }
        // ── Guests checking out today ─────────────────────────────────────────
        $checkoutsToday = DB::table('reservations')
            ->whereDate('check_out_date', Carbon::today($tz))
            ->where('reservation_status', 'approved')
            ->count();

        if ($checkoutsToday > 0) {
            $notifications[] = [
                'id'      => 'checkouts_' . Carbon::today($tz)->toDateString(),
                'message' => $checkoutsToday . ' guest' . ($checkoutsToday > 1 ? 's' : '') . ' checking out today.',
                'icon'    => 'ri-logout-box-line',
                'color'   => 'info',
                'time'    => 'Today',
                'type'    => 'checkout',
            ];
        }

        // ── Recent service requests (last 15 min) for real-time feel ─────────
        $recentRequests = DB::table('service_requests as sr')
            ->join('registrations as reg', 'reg.registration_id', '=', 'sr.registration_id')
            ->join('reservations as r',    'r.reservation_id',    '=', 'reg.reservation_id')
            ->leftJoin('rooms as rm',      'rm.room_id',           '=', 'r.room_id')
            ->where('sr.requested_at', '>=', Carbon::now($tz)->subMinutes(15))
            ->whereIn('sr.request_status', ['pending'])
            ->select(
                'sr.service_request_id',
                'sr.service_type',
                'sr.request_status',
                'sr.requested_at',
                'rm.room_number'
            )
            ->orderByDesc('sr.requested_at')
            ->limit(5)
            ->get();

        foreach ($recentRequests as $req) {
            $isFood  = $req->service_type === 'food';
            $room    = $req->room_number ? 'Room ' . $req->room_number : 'a guest';
            $label   = $isFood ? 'breakfast order' : 'room service request';
            $existing = array_filter($notifications, fn($n) => $n['type'] === 'service');

            // Only add if not already covered by the count notification
            if (empty($existing)) {
                $notifications[] = [
                    'id'      => 'new_req_' . $req->service_request_id,
                    'message' => 'New ' . $label . ' from ' . $room . '.',
                    'icon'    => $isFood ? 'ri-restaurant-line' : 'ri-concierge-bell-line',
                    'color'   => 'danger',
                    'time'    => $this->humanTime($req->requested_at, $tz),
                    'type'    => 'service',
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'notifications' => $notifications,
            'count'         => count($notifications),
        ]);
    }

    // ── Shared: format a timestamp relative to now (timezone-aware) ──────────
    private function humanTime(?string $timestamp, string $tz): string
    {
        if (! $timestamp) return 'Just now';

        $dt  = Carbon::parse($timestamp)->setTimezone($tz);
        $now = Carbon::now($tz);

        $diffSeconds = $now->diffInSeconds($dt, false);

        // Future or within 5 seconds → "Just now"
        if ($diffSeconds >= -5) return 'Just now';

        $diffMinutes = abs((int) $now->diffInMinutes($dt, false));
        $diffHours   = abs((int) $now->diffInHours($dt, false));

        if ($diffMinutes < 1)  return 'Just now';
        if ($diffMinutes < 60) return $diffMinutes . 'm ago';
        if ($diffHours   < 24) return $diffHours . 'h ago';

        // Same calendar day
        if ($dt->isSameDay($now)) return 'Today, ' . $dt->format('g:i A');

        // Yesterday
        if ($dt->isSameDay($now->copy()->subDay())) return 'Yesterday, ' . $dt->format('g:i A');

        return $dt->format('M j, g:i A');
    }
}