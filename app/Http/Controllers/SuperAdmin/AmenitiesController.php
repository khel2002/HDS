<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Room;
use Illuminate\Http\Request;

class AmenitiesController extends Controller
{

    public function index()
    {

        $amenities = Amenity::with(['rooms' => function($query) {
            $query->select('rooms.*', 'room_types.room_type_name')
                  ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id');
        }])->get();


        $rooms = Room::with('roomType')->get();


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
