<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    public function index()
    {
        // Get all room types
        $roomTypes = DB::table('room_types')->get();

        // Get all rooms with their type information
        $rooms = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->select(
                'rooms.*',
                'room_types.room_type_name'
            )
            ->get();

        // Calculate statistics
        $stats = [
            'total_types' => $roomTypes->count(),
            'total_rooms' => DB::table('rooms')->count(),
            'avg_rate' => DB::table('room_types')->avg('rate_per_night') ?? 0,
            'avg_capacity' => DB::table('room_types')->avg('max_pax') ?? 0,
        ];

        return view('content.super-admin.rooms.room_type', compact('roomTypes', 'rooms', 'stats'));
    }

    public function show($id)
    {
        // Get room type details
        $roomType = DB::table('room_types')
            ->where('room_type_id', $id)
            ->first();

        if (!$roomType) {
            abort(404);
        }

        // Get all rooms of this type
        $rooms = DB::table('rooms')
            ->where('room_type_id', $id)
            ->get();

        // Calculate statistics for this room type
        $stats = [
            'total_rooms' => $rooms->count(),
            'available_rooms' => $rooms->where('status', 'available')->count(),
            'occupied_rooms' => $rooms->where('status', 'occupied')->count(),
            'maintenance_rooms' => $rooms->where('status', 'maintenance')->count(),
        ];

        return view('content.super-admin.rooms.show', compact('roomType', 'rooms', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate_per_night' => 'required|numeric|min:0',
            'max_pax' => 'required|integer|min:1',
        ]);

        $roomTypeId = DB::table('room_types')->insertGetId([
            'room_type_name' => $validated['room_type_name'],
            'description' => $validated['description'] ?? null,
            'rate_per_night' => $validated['rate_per_night'],
            'max_pax' => $validated['max_pax'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room type created successfully',
            'room_type_id' => $roomTypeId
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'room_type_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate_per_night' => 'required|numeric|min:0',
            'max_pax' => 'required|integer|min:1',
        ]);

        $updated = DB::table('room_types')
            ->where('room_type_id', $id)
            ->update([
                'room_type_name' => $validated['room_type_name'],
                'description' => $validated['description'] ?? null,
                'rate_per_night' => $validated['rate_per_night'],
                'max_pax' => $validated['max_pax'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room type updated successfully'
        ]);
    }

    public function destroy($id)
    {
        // Check if there are rooms using this room type
        $roomCount = DB::table('rooms')
            ->where('room_type_id', $id)
            ->count();

        if ($roomCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete room type. There are {$roomCount} room(s) using this type."
            ], 400);
        }

        $deleted = DB::table('room_types')
            ->where('room_type_id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room type deleted successfully'
        ]);
    }
}
