<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\RoomServiceItem;
use App\Models\ServiceRequest;
use App\Models\ServiceRoomOrderItem;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomServiceController extends Controller
{
    private function activeRegistration(): ?Registration
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

    // 1. GET /guest/room-service
    public function index()
    {
        $registration = $this->activeRegistration();
        $guest        = $this->guestContext($registration);

        $items      = RoomServiceItem::orderBy('item_name')->get()->groupBy('category');
        $categories = ['housekeeping', 'toiletries', 'technical', 'comfort'];

        return view('content.room-service.room-service', compact('items', 'categories', 'guest'));
    }

    // 2. POST /guest/room-service/request
    public function store(Request $request)
    {
        $request->validate([
            'description'      => 'nullable|string|max:500',
            'items'            => 'required|array|min:1',
            'items.*.item_id'  => 'required|integer|exists:room_service_items,item_id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
        ]);

        $registration = $this->activeRegistration();

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'You must be checked in to submit a room service request.'], 403);
        }

        $itemIds      = collect($request->items)->pluck('item_id')->unique()->toArray();
        $catalogItems = RoomServiceItem::whereIn('item_id', $itemIds)->where('is_available', true)->get()->keyBy('item_id');

        foreach ($request->items as $item) {
            if (! $catalogItems->has($item['item_id'])) {
                return response()->json(['success' => false, 'error' => 'One or more selected items are currently unavailable.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $serviceRequest = ServiceRequest::create([
                'registration_id'      => $registration->registration_id,
                'requested_by_user_id' => Auth::id(),
                'service_type'         => 'room_service',
                'description'          => filled($request->description) ? $request->description : 'Room service request',
                'request_status'       => 'pending',
            ]);

            foreach ($request->items as $item) {
                $catalogItem = $catalogItems->get($item['item_id']);
                ServiceRoomOrderItem::create([
                    'service_request_id' => $serviceRequest->service_request_id,
                    'item_id'            => $item['item_id'],
                    'quantity'           => $catalogItem->requires_quantity ? $item['quantity'] : 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'            => true,
                'message'            => 'Room service request submitted successfully!',
                'service_request_id' => $serviceRequest->service_request_id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Room service request failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(), 'registration_id' => $registration->registration_id,
            ]);
            return response()->json(['success' => false, 'error' => 'Something went wrong. Please try again.'], 500);
        }
    }

    // 3. GET /guest/room-service/my-requests
    public function myRequests()
    {
        $registration = $this->activeRegistration();
        $guest        = $this->guestContext($registration);
        $requests     = collect();

        if ($registration) {
            $requests = ServiceRequest::where('registration_id', $registration->registration_id)
                ->where('service_type', 'room_service')
                ->with(['roomOrderItems.roomServiceItem'])
                ->latest('requested_at')
                ->get()
                ->map(fn ($req) => $this->formatRequest($req));
        }

        return view('content.room-service.my-requests', compact('guest', 'requests'));
    }

    // 4. GET /guest/room-service/requests-json
    public function myRequestsJson()
    {
        $registration = $this->activeRegistration();

        if (! $registration) {
            return response()->json(['success' => false, 'error' => 'Not checked in.'], 403);
        }

        $requests = ServiceRequest::where('registration_id', $registration->registration_id)
            ->where('service_type', 'room_service')
            ->with(['roomOrderItems.roomServiceItem'])
            ->latest('requested_at')
            ->get()
            ->map(fn ($req) => $this->formatRequest($req));

        return response()->json(['success' => true, 'requests' => $requests]);
    }

    private function formatRequest(ServiceRequest $req): array
    {
        return [
            'service_request_id' => $req->service_request_id,
            'description'        => $req->description,
            'status'             => $req->request_status,
            'requested_at'       => $req->requested_at?->format('M d, Y h:i A'),
            'items'              => $req->roomOrderItems->map(fn ($o) => [
                'name'              => $o->roomServiceItem?->item_name,
                'category'          => $o->roomServiceItem?->category,
                'icon'              => $o->roomServiceItem?->icon ?? 'ri-service-line',
                'requires_quantity' => (bool) ($o->roomServiceItem?->requires_quantity ?? true),
                'quantity'          => $o->quantity,
            ]),
        ];
    }
}