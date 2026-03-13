<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\BreakfastMenu;
use App\Models\ServiceRequest;
use App\Models\ServiceBreakfastOrder;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BreakfastController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Shared helper – resolve the logged-in guest's active registration
    // ─────────────────────────────────────────────────────────────────────────
    private function activeRegistration()
    {
        return Registration::where('user_id', Auth::id())
            ->whereNull('check_out_date')
            ->with(['reservation.room', 'guestDetails'])
            ->latest('check_in_at')
            ->first();
    }

    private function guestContext(?Registration $registration): array
    {
        return [
            'status'          => $registration ? 'checked-in' : 'not-checked-in',
            'registration_id' => $registration?->registration_id,
            'room_number'     => $registration?->reservation?->room?->room_number ?? null,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1.  GET /guest/breakfast/menu   – Browse menu & place order (merged view)
    //     Also handles /guest/breakfast/order via route alias (see routes file)
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $menuItems    = BreakfastMenu::orderBy('meal_name')->get();
        $registration = $this->activeRegistration();
        $guest        = $this->guestContext($registration);

        return view('content.breakfast.breakfast-menu', compact('menuItems', 'guest'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2.  POST /guest/breakfast/order – Submit breakfast order (AJAX)
    //
    //  Expected JSON body:
    //  {
    //    "service_type": "food",
    //    "description":  "Breakfast order",
    //    "items": [
    //      { "breakfast_id": 1, "quantity": 2 },
    //      { "breakfast_id": 3, "quantity": 1 }
    //    ]
    //  }
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'service_type'         => 'required|in:food,room_service',
            'description'          => 'required|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.breakfast_id' => 'required|integer|exists:breakfast_menu,breakfast_id',
            'items.*.quantity'     => 'required|integer|min:1|max:20',
        ]);

        $registration = $this->activeRegistration();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'error'   => 'You must be checked in to place an order.',
            ], 403);
        }

        // Verify all items are currently available
        $breakfastIds = collect($request->items)->pluck('breakfast_id')->unique()->toArray();
        $menuItems    = BreakfastMenu::whereIn('breakfast_id', $breakfastIds)
                            ->where('is_available', true)
                            ->get()
                            ->keyBy('breakfast_id');

        foreach ($request->items as $item) {
            if (! $menuItems->has($item['breakfast_id'])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'One or more selected items are currently unavailable.',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $serviceRequest = ServiceRequest::create([
                'registration_id'      => $registration->registration_id,
                'requested_by_user_id' => Auth::id(),
                'service_type'         => $request->service_type,
                'description'          => $request->description,
                'request_status'       => 'pending',
            ]);

            foreach ($request->items as $item) {
                $menuItem = $menuItems->get($item['breakfast_id']);

                ServiceBreakfastOrder::create([
                    'service_request_id' => $serviceRequest->service_request_id,
                    'breakfast_id'       => $item['breakfast_id'],
                    'quantity'           => $item['quantity'],
                    'price_at_order'     => $menuItem->price,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'            => true,
                'message'            => 'Breakfast order placed successfully!',
                'service_request_id' => $serviceRequest->service_request_id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Breakfast order failed: ' . $e->getMessage(), [
                'user_id'         => Auth::id(),
                'registration_id' => $registration->registration_id,
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3.  GET /guest/breakfast/my-orders  – My Orders page (renders view)
    // ─────────────────────────────────────────────────────────────────────────
    public function myOrders()
    {
        $registration = $this->activeRegistration();
        $guest        = $this->guestContext($registration);

        // Eagerly load orders for SSR; the view can also poll /orders (JSON)
        $orders = collect();

        if ($registration) {
            $orders = ServiceRequest::where('registration_id', $registration->registration_id)
                ->where('service_type', 'food')
                ->with(['breakfastOrders.breakfastMenu'])
                ->latest('requested_at')
                ->get()
                ->map(fn ($req) => $this->formatOrder($req));
        }

        return view('content.breakfast.my-orders', compact('guest', 'orders'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4.  GET /guest/breakfast/orders  – JSON endpoint (AJAX polling)
    // ─────────────────────────────────────────────────────────────────────────
    public function myOrdersJson()
    {
        $registration = $this->activeRegistration();

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'Not checked in.'], 403);
        }

        $orders = ServiceRequest::where('registration_id', $registration->registration_id)
            ->where('service_type', 'food')
            ->with(['breakfastOrders.breakfastMenu'])
            ->latest('requested_at')
            ->get()
            ->map(fn ($req) => $this->formatOrder($req));

        return response()->json(['success' => true, 'orders' => $orders]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private – format a ServiceRequest into the standard order array
    // ─────────────────────────────────────────────────────────────────────────
    private function formatOrder(ServiceRequest $req): array
    {
        return [
            'service_request_id' => $req->service_request_id,
            'description'        => $req->description,
            'status'             => $req->request_status,
            'requested_at'       => $req->requested_at?->format('M d, Y h:i A'),
            'items'              => $req->breakfastOrders->map(fn ($o) => [
                'name'     => $o->breakfastMenu?->meal_name,
                'qty'      => $o->quantity,
                'price'    => $o->price_at_order,
                'subtotal' => $o->quantity * $o->price_at_order,
            ]),
            'total' => $req->breakfastOrders->sum(fn ($o) => $o->quantity * $o->price_at_order),
        ];
    }
}