<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\BreakfastMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ServiceRequest;
use Carbon\Carbon;

class BreakfastController extends Controller
{
    /**
     * Display a listing of breakfast menu items.
     */
    public function index()
    {
        $breakfastItems = BreakfastMenu::orderBy('meal_name')->get();
        $stats          = $this->buildStats($breakfastItems);

        return view('content.super-admin.breakfast.index', compact('breakfastItems', 'stats'));
    }

    /**
     * Store a newly created breakfast menu item (AJAX).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_name'    => 'required|string|max:150|unique:breakfast_menu,meal_name',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $validated['is_available'] = isset($validated['is_available'])
            ? (bool) $validated['is_available']
            : true;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')
                ->store('img/breakfast', 'public');
        }

        unset($validated['image']);
        $item = BreakfastMenu::create($validated);

        return response()->json([
            'item'     => $this->itemPayload($item),
            'stats'    => $this->buildStats(BreakfastMenu::all()),
            'edit_url' => route('super_admin.breakfast.menu.update', $item->breakfast_id),
        ], 201);
    }

    /**
     * Update the specified breakfast menu item (AJAX).
     */
    public function update(Request $request, $id)
    {
        $item = BreakfastMenu::findOrFail($id);

        $validated = $request->validate([
            'meal_name'    => 'required|string|max:150|unique:breakfast_menu,meal_name,' . $id . ',breakfast_id',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'sometimes|boolean',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'remove_image' => 'sometimes|boolean',
        ]);

        $validated['is_available'] = isset($validated['is_available'])
            ? (bool) $validated['is_available']
            : false;

        if (!empty($validated['remove_image'])) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = $request->file('image')
                ->store('img/breakfast', 'public');
        }

        unset($validated['image'], $validated['remove_image']);
        $item->update($validated);

        return response()->json([
            'item'  => $this->itemPayload($item),
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    /**
     * Toggle availability of a breakfast menu item (AJAX).
     */
    public function toggleAvailability($id)
    {
        $item = BreakfastMenu::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);

        return response()->json([
            'item'  => $this->itemPayload($item),
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    /**
     * Remove the specified breakfast menu item (AJAX).
     */
    public function destroy($id)
    {
        $item = BreakfastMenu::findOrFail($id);

        if ($item->serviceBreakfastOrders()->exists()) {
            return response()->json([
                'message' => "Cannot delete \"{$item->meal_name}\" because it has existing orders.",
            ], 422);
        }

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json([
            'stats' => $this->buildStats(BreakfastMenu::all()),
        ]);
    }

    // ─── Orders ───────────────────────────────────────────────────────────

    /**
     * Display all breakfast orders.
     */
    public function orders()
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

        return view('content.super-admin.breakfast.orders', compact('orders', 'stats'));
    }

    /**
     * Update the status of a breakfast order (AJAX PATCH).
     *
     * Flow: pending → in_progress → delivering → completed
     *       pending → cancelled
     *       in_progress → cancelled
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

        if (!in_array($request->status, $allowed)) {
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

    private function itemPayload(BreakfastMenu $item): array
    {
        return [
            'breakfast_id' => $item->breakfast_id,
            'meal_name'    => $item->meal_name,
            'description'  => $item->description,
            'price'        => $item->price,
            'is_available' => (bool) $item->is_available,
            'image_path'   => $item->image_path,
            'image_url'    => $item->image_path
                                ? asset('storage/' . $item->image_path)
                                : null,
            'toggle_url'   => route('super_admin.breakfast.menu.toggle',  $item->breakfast_id),
            'delete_url'   => route('super_admin.breakfast.menu.destroy', $item->breakfast_id),
        ];
    }

    private function orderPayload(ServiceRequest $order): array
    {
        return [
            'service_request_id' => $order->service_request_id,
            'request_status'     => $order->request_status,
            'status_url'         => route('super_admin.breakfast.orders.status', $order->service_request_id),
            'completed_at'       => $order->completed_at,
        ];
    }

    private function buildStats($items): array
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