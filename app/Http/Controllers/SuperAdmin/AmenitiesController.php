<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AmenitiesController extends Controller
{
    public function index()
    {

        $amenities = DB::table('amenities')->get();


        foreach ($amenities as $amenity) {
            $amenity->rooms = DB::table('room_amenities')
                ->join('rooms', 'room_amenities.room_id', '=', 'rooms.room_id')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('room_amenities.amenity_id', $amenity->amenity_id)
                ->select(
                    'rooms.room_id',
                    'rooms.room_number',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->get();
        }


        $rooms = DB::table('rooms')->get();


        $stats = [
            'total_amenities' => $amenities->count(),
            'in_use_amenities' => $amenities->filter(function($amenity) {
                return $amenity->rooms->count() > 0;
            })->count(),
            'rooms_with_amenities' => DB::table('room_amenities')
                ->distinct('room_id')
                ->count('room_id')
        ];

        return view('content.super-admin.amenities.index', compact('amenities', 'rooms', 'stats'));
    }

    public function show($id)
    {
        $amenity = DB::table('amenities')
            ->where('amenity_id', $id)
            ->first();

        if (!$amenity) {
            return redirect()->route('super_admin.amenities.index')
                ->with('error', 'Amenity not found');
        }


        $rooms = DB::table('room_amenities')
            ->join('rooms', 'room_amenities.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->where('room_amenities.amenity_id', $id)
            ->select(
                'rooms.*',
                'room_types.room_type_name',
                'room_types.rate_per_night',
                'room_types.max_pax'
            )
            ->get();

        return view('content.super-admin.amenities.show', compact('amenity', 'rooms'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'amenity_name' => 'required|string|max:45|unique:amenities,amenity_name',
            ]);

            DB::table('amenities')->insert([
                'amenity_name' => $validated['amenity_name'],
            ]);

            return redirect()->route('super_admin.amenities.index')
                ->with('success', 'Amenity created successfully!');

        } catch (\Exception $e) {
            return redirect()->route('super_admin.amenities.index')
                ->with('error', 'Failed to create amenity: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amenity_name' => 'required|string|max:45|unique:amenities,amenity_name,' . $id . ',amenity_id',
            ]);

            $updated = DB::table('amenities')
                ->where('amenity_id', $id)
                ->update([
                    'amenity_name' => $validated['amenity_name'],
                ]);

            if (!$updated) {
                return redirect()->route('super_admin.amenities.index')
                    ->with('error', 'Amenity not found');
            }

            return redirect()->route('super_admin.amenities.index')
                ->with('success', 'Amenity updated successfully!');

        } catch (\Exception $e) {
            return redirect()->route('super_admin.amenities.index')
                ->with('error', 'Failed to update amenity: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $amenity = DB::table('amenities')->where('amenity_id', $id)->first();

            if (!$amenity) {
                return redirect()->route('super_admin.amenities.index')
                    ->with('error', 'Amenity not found');
            }


            $roomCount = DB::table('room_amenities')
                ->where('amenity_id', $id)
                ->count();


            DB::table('room_amenities')->where('amenity_id', $id)->delete();


            DB::table('amenities')->where('amenity_id', $id)->delete();

            $message = $roomCount > 0
                ? "Amenity deleted successfully! Removed from {$roomCount} room(s)."
                : "Amenity deleted successfully!";

            return redirect()->route('super_admin.amenities.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('super_admin.amenities.index')
                ->with('error', 'Failed to delete amenity: ' . $e->getMessage());
        }
    }
}
