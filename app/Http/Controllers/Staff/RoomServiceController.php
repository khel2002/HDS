<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoomServiceController extends Controller
{
    /**
     * GET /staff/room-service/requests
     */
    public function index(Request $request)
    {
        $query = ServiceRequest::where('service_type', 'room_service')
            ->with([
                'roomOrderItems.roomServiceItem',
                'registration.guestDetails',
                'registration.reservation.room.roomType',
            ])
            ->latest('requested_at');

        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'pending'     => ServiceRequest::where('service_type', 'room_service')->where('request_status', 'pending')->count(),
            'in_progress' => ServiceRequest::where('service_type', 'room_service')->where('request_status', 'in_progress')->count(),
            'completed'   => ServiceRequest::where('service_type', 'room_service')->where('request_status', 'completed')->count(),
            'cancelled'   => ServiceRequest::where('service_type', 'room_service')->where('request_status', 'cancelled')->count(),
        ];

        return view('content.staff.room-service.orders', compact('requests', 'stats'));
    }

    /**
     * PATCH /staff/room-service/requests/{id}/status
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $serviceRequest = ServiceRequest::where('service_type', 'room_service')->findOrFail($id);

        $oldStatus = $serviceRequest->request_status;
        $newStatus = $request->status;

        if ($oldStatus === 'completed' || $oldStatus === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be updated further.',
            ], 422);
        }

        $serviceRequest->request_status = $newStatus;

        if ($newStatus === 'completed') {
            $serviceRequest->completed_at = now();
        }

        $serviceRequest->save();

        $statusLabels = [
            'pending'     => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ];

        Log::info("Staff: Room service request #{$id} status changed from {$oldStatus} to {$newStatus} by user #" . auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Request marked as {$statusLabels[$newStatus]}.",
            'status'  => $newStatus,
        ]);
    }
}