<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BreakfastMenu;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffBreakfastController extends Controller
{
    /**
     * Display the breakfast menu (staff view).
     * Staff can view items and toggle availability — no add / edit / delete.
     */
    public function menu()
    {
        $breakfastItems = BreakfastMenu::orderBy('meal_name')->get();
        $stats          = $this->buildMenuStats($breakfastItems);

        return view('content.staff.breakfast.menu', compact('breakfastItems', 'stats'));
    }

    /**
     * Toggle availability of a breakfast menu item (AJAX PATCH).
     */
    public function toggleAvailability($id)
    {
        $item = BreakfastMenu::findOrFail($id);
        $item->update(['is_available' => ! $item->is_available]);

        return response()->json([
            'item'  => [
                'breakfast_id' => $item->breakfast_id,
                'is_available' => (bool) $item->is_available,
            ],
            'stats' => $this->buildMenuStats(BreakfastMenu::all()),
        ]);
    }

    /**
     * Display all breakfast orders (staff view).
     * Staff can see orders and update statuses only — no menu management.
     */
    public function index()
    {
        $orders = ServiceRequest::with([
                'breakfastOrders.breakfastMenu',
                'registration.guestDetails',
                'registration.reservation.room.roomType',
            ])
            ->where('service_type', 'food')
            ->orderByRaw("FIELD(request_status, 'pending', 'in_progress', 'delivering', 'completed', 'cancelled')")
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        $stats = $this->buildOrderStats();

        return view('content.staff.breakfast.orders', compact('orders', 'stats'));
    }

    /**
     * Display only pending breakfast orders.
     */
    public function pending()
    {
        $orders = ServiceRequest::with([
                'breakfastOrders.breakfastMenu',
                'registration.guestDetails',
                'registration.reservation.room.roomType',
            ])
            ->where('service_type', 'food')
            ->where('request_status', 'pending')
            ->orderBy('requested_at', 'asc')
            ->paginate(15);

        $stats = $this->buildOrderStats();

        return view('content.staff.breakfast.orders', compact('orders', 'stats'));
    }

    /**
     * Update the status of a breakfast order (AJAX PATCH).
     *
     * Staff-allowed transitions:
     *   pending     → in_progress | cancelled
     *   in_progress → delivering  | cancelled
     *   delivering  → completed
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:in_progress,delivering,completed,cancelled',
        ]);

        $order = ServiceRequest::where('service_type', 'food')->findOrFail($id);

        $allowedTransitions = [
            'pending'     => ['in_progress', 'cancelled'],
            'in_progress' => ['delivering',  'cancelled'],
            'delivering'  => ['completed'],
        ];

        $allowed = $allowedTransitions[$order->request_status] ?? [];

        if (! in_array($request->status, $allowed)) {
            return response()->json([
                'message' => "Cannot transition from \"{$order->request_status}\" to \"{$request->status}\".",
            ], 422);
        }

        $order->request_status = $request->status;

        if ($request->status === 'completed') {
            $order->completed_at = Carbon::now();
        }

        $order->save();

        return response()->json([
            'item'  => $this->orderPayload($order),
            'stats' => $this->buildOrderStats(),
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────

    private function orderPayload(ServiceRequest $order): array
    {
        return [
            'service_request_id' => $order->service_request_id,
            'request_status'     => $order->request_status,
            'status_url'         => route('staff.breakfast.orders.status', $order->service_request_id),
            'completed_at'       => $order->completed_at,
        ];
    }

    private function buildMenuStats($items): array
    {
        $collection = collect($items);

        return [
            'total_items'       => $collection->count(),
            'available_items'   => $collection->where('is_available', true)->count(),
            'unavailable_items' => $collection->where('is_available', false)->count(),
            'avg_price'         => round($collection->avg('price') ?? 0, 2),
        ];
    }

    private function buildOrderStats(): array
    {
        $counts = ServiceRequest::where('service_type', 'food')
            ->selectRaw('request_status, COUNT(*) as count')
            ->groupBy('request_status')
            ->pluck('count', 'request_status')
            ->toArray();

        return [
            'pending'     => $counts['pending']     ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
            'delivering'  => $counts['delivering']  ?? 0,
            'completed'   => $counts['completed']   ?? 0,
            'cancelled'   => $counts['cancelled']   ?? 0,
        ];
    }
}