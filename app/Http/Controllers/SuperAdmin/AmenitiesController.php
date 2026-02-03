<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Room;
use Illuminate\Http\Request;

class AmenitiesController extends Controller
{
    /**
     * Display a listing of amenities
     */
    public function index()
    {
        // Get all amenities with their related rooms
        $amenities = Amenity::with(['rooms' => function($query) {
            $query->select('rooms.*', 'room_types.room_type_name')
                  ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id');
        }])->get();

        // Get all rooms for statistics
        $rooms = Room::with('roomType')->get();

        // Calculate statistics
        $stats = [
            'total_amenities' => $amenities->count(),
            'in_use_amenities' => $amenities->filter(function($amenity) {
                return $amenity->rooms->count() > 0;
            })->count(),
            'rooms_with_amenities' => Room::whereHas('amenities')->distinct()->count()
        ];

        return view('content.super-admin.amenities.index', compact('amenities', 'rooms', 'stats'));
    }
}
