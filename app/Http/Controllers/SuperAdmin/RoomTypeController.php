<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTypeController extends Controller
{
    public function index()
    {

        $roomTypes = DB::table('room_types')->get();


        $rooms = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->select(
                'rooms.*',
                'room_types.room_type_name'
            )
            ->get();


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

        $roomType = DB::table('room_types')
            ->where('room_type_id', $id)
            ->first();

        if (!$roomType) {
            return redirect()->route('super_admin.room-types.index')
                ->with('error', 'Room type not found');
        }


        $rooms = DB::table('rooms')
            ->where('room_type_id', $id)
            ->get();


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
        try {
            $validated = $request->validate([
                'room_type_name' => 'required|string|max:255|unique:room_types,room_type_name',
                'description' => 'nullable|string',
                'rate_per_night' => 'required|numeric|min:0',
                'max_pax' => 'required|integer|min:1',
            ]);

            DB::table('room_types')->insert([
                'room_type_name' => $validated['room_type_name'],
                'description' => $validated['description'] ?? null,
                'rate_per_night' => $validated['rate_per_night'],
                'max_pax' => $validated['max_pax'],
            ]);

            return redirect()->route('super_admin.room-types.index')
                ->with('success', 'Room type created successfully!');

        } catch (\Exception $e) {
            return redirect()->route('super_admin.room-types.index')
                ->with('error', 'Failed to create room type: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'room_type_name' => 'required|string|max:255|unique:room_types,room_type_name,' . $id . ',room_type_id',
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
                ]);

            if (!$updated) {
                return redirect()->route('super_admin.room-types.index')
                    ->with('error', 'Room type not found');
            }

            return redirect()->route('super_admin.room-types.index')
                ->with('success', 'Room type updated successfully!');

        } catch (\Exception $e) {
            return redirect()->route('super_admin.room-types.index')
                ->with('error', 'Failed to update room type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $roomCount = DB::table('rooms')
                ->where('room_type_id', $id)
                ->count();

            if ($roomCount > 0) {
                return redirect()->route('super_admin.room-types.index')
                    ->with('error', "Cannot delete room type. There are {$roomCount} room(s) using this type.");
            }

            $deleted = DB::table('room_types')
                ->where('room_type_id', $id)
                ->delete();

            if (!$deleted) {
                return redirect()->route('super_admin.room-types.index')
                    ->with('error', 'Room type not found');
            }

            return redirect()->route('super_admin.room-types.index')
                ->with('success', 'Room type deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->route('super_admin.room-types.index')
                ->with('error', 'Failed to delete room type: ' . $e->getMessage());
        }
    }
}
