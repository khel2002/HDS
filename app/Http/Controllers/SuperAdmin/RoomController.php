<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        // Get the room type filter from query parameter
        $selectedRoomType = $request->query('room_type');

        // Base query for rooms
        $query = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->select(
                'rooms.room_id',
                'rooms.room_number',
                'rooms.room_type_id',
                'rooms.status',
                'rooms.image_path',
                'room_types.room_type_name',
                'room_types.description',
                'room_types.rate_per_night',
                'room_types.max_pax'
            );

        // Apply room type filter if provided and not empty
        if ($selectedRoomType && $selectedRoomType !== '') {
            $query->where('rooms.room_type_id', $selectedRoomType);
        }

        $rooms = $query->get();

        // Get amenities for each room
        foreach ($rooms as $room) {
            $room->amenities = DB::table('room_amenities')
                ->join('amenities', 'room_amenities.amenity_id', '=', 'amenities.amenity_id')
                ->where('room_amenities.room_id', $room->room_id)
                ->pluck('amenities.amenity_name')
                ->toArray();
        }

        // Calculate stats based on ALL rooms (not filtered)
        $allRoomsStats = [
            'total_rooms' => DB::table('rooms')->count(),
            'available_rooms' => DB::table('rooms')->where('status', 'available')->count(),
            'occupied_rooms' => DB::table('rooms')->where('status', 'occupied')->count(),
            'maintenance_rooms' => DB::table('rooms')->where('status', 'maintenance')->count(),
        ];

        $stats = $allRoomsStats;

        $roomTypes = DB::table('room_types')->get();
        $amenities = DB::table('amenities')->get();

        return view('content.super-admin.rooms.index', compact('rooms', 'stats', 'roomTypes', 'amenities', 'selectedRoomType'));
    }

    public function show($id)
    {
        $room = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->where('rooms.room_id', $id)
            ->select(
                'rooms.room_id',
                'rooms.room_number',
                'rooms.room_type_id',
                'rooms.status',
                'rooms.image_path',
                'room_types.room_type_name',
                'room_types.description',
                'room_types.rate_per_night',
                'room_types.max_pax'
            )
            ->first();

        if (!$room) {
            abort(404);
        }

        $room->amenities = DB::table('room_amenities')
            ->join('amenities', 'room_amenities.amenity_id', '=', 'amenities.amenity_id')
            ->where('room_amenities.room_id', $room->room_id)
            ->pluck('amenities.amenity_name');

        return view('content.super-admin.rooms.show', compact('room'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string|max:10|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,room_type_id',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,amenity_id'
        ]);

        try {
            DB::beginTransaction();

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('rooms', 'public');
            }

            // Insert room - each room can only have ONE room type
            $roomId = DB::table('rooms')->insertGetId([
                'room_number' => $request->room_number,
                'room_type_id' => $request->room_type_id, // One room type per room
                'status' => $request->status,
                'image_path' => $imagePath,
            ]);

            // Insert amenities
            if ($request->has('amenities') && is_array($request->amenities)) {
                // Get amenity names for description
                $amenityNames = DB::table('amenities')
                    ->whereIn('amenity_id', $request->amenities)
                    ->pluck('amenity_name')
                    ->toArray();

                // Generate description from selected amenities
                if (!empty($amenityNames)) {
                    $description = 'This room features: ' . implode(', ', $amenityNames) . '.';

                    // Update room_type description
                    DB::table('room_types')
                        ->where('room_type_id', $request->room_type_id)
                        ->update(['description' => $description]);
                }

                // Insert room amenities
                foreach ($request->amenities as $amenityId) {
                    DB::table('room_amenities')->insert([
                        'room_id' => $roomId,
                        'amenity_id' => $amenityId
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('super_admin.rooms.index')
                ->with('success', 'Room added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded image if exists
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to add room. Please try again.')
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $room = DB::table('rooms')->where('room_id', $id)->first();

        if (!$room) {
            abort(404);
        }

        $request->validate([
            'room_number' => 'required|string|max:10|unique:rooms,room_number,' . $id . ',room_id',
            'room_type_id' => 'required|exists:room_types,room_type_id',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,amenity_id'
        ]);

        try {
            DB::beginTransaction();

            // Each room can only have ONE room type
            $updateData = [
                'room_number' => $request->room_number,
                'room_type_id' => $request->room_type_id, // One room type per room
                'status' => $request->status,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($room->image_path && Storage::disk('public')->exists($room->image_path)) {
                    Storage::disk('public')->delete($room->image_path);
                }

                $updateData['image_path'] = $request->file('image')->store('rooms', 'public');
            }

            // Update room
            DB::table('rooms')
                ->where('room_id', $id)
                ->update($updateData);

            // Delete existing amenities
            DB::table('room_amenities')->where('room_id', $id)->delete();

            // Insert new amenities and update room_type description
            if ($request->has('amenities') && is_array($request->amenities)) {
                // Get amenity names for description
                $amenityNames = DB::table('amenities')
                    ->whereIn('amenity_id', $request->amenities)
                    ->pluck('amenity_name')
                    ->toArray();

                // Generate description from selected amenities
                if (!empty($amenityNames)) {
                    $description = 'This room features: ' . implode(', ', $amenityNames) . '.';

                    // Update room_type description
                    DB::table('room_types')
                        ->where('room_type_id', $request->room_type_id)
                        ->update(['description' => $description]);
                }

                // Insert room amenities
                foreach ($request->amenities as $amenityId) {
                    DB::table('room_amenities')->insert([
                        'room_id' => $id,
                        'amenity_id' => $amenityId
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('super_admin.rooms.index')
                ->with('success', 'Room updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Failed to update room. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $room = DB::table('rooms')->where('room_id', $id)->first();

            if (!$room) {
                abort(404);
            }

            // Prevent deleting occupied rooms
            if ($room->status === 'occupied') {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete an occupied room. Please change the status first.');
            }

            // Delete room amenities
            DB::table('room_amenities')->where('room_id', $id)->delete();

            // Delete room image
            if ($room->image_path && Storage::disk('public')->exists($room->image_path)) {
                Storage::disk('public')->delete($room->image_path);
            }

            // Delete room
            DB::table('rooms')->where('room_id', $id)->delete();

            DB::commit();

            return redirect()
                ->route('super_admin.rooms.index')
                ->with('success', 'Room deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Failed to delete room. Please try again.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        try {
            $room = DB::table('rooms')->where('room_id', $id)->first();

            if (!$room) {
                abort(404);
            }

            DB::table('rooms')
                ->where('room_id', $id)
                ->update(['status' => $request->status]);

            return redirect()
                ->route('super_admin.rooms.index')
                ->with('success', 'Room status updated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update room status. Please try again.');
        }
    }
}
