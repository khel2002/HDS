<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {

        $rooms = DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->select(
                'rooms.*',
                'room_types.room_type_name',
                'room_types.description',
                'room_types.rate_per_night',
                'room_types.max_pax'
            )
            ->get();


        foreach ($rooms as $room) {
            $room->amenities = DB::table('room_amenities')
                ->join('amenities', 'room_amenities.amenity_id', '=', 'amenities.amenity_id')
                ->where('room_amenities.room_id', $room->room_id)
                ->pluck('amenities.amenity_name')
                ->toArray();
        }


        $stats = [
            'total_rooms' => DB::table('rooms')->count(),
            'available_rooms' => DB::table('rooms')->where('status', 'available')->count(),
            'occupied_rooms' => DB::table('rooms')->where('status', 'occupied')->count(),
            'maintenance_rooms' => DB::table('rooms')->where('status', 'maintenance')->count(),
        ];


        $roomTypes = DB::table('room_types')->get();

        return view('content.super-admin.rooms.index', compact('rooms', 'stats', 'roomTypes'));
    }

   public function show($id)
  {
      $room = DB::table('rooms')
          ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
          ->where('rooms.room_id', $id)
          ->select(
              'rooms.*',
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
}
