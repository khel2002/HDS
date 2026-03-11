<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class FrontpageController extends Controller
{
    public function index()
    {
        try {

            $roomsFirst = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->where('rooms.status', 'available')
                ->orderBy('rooms.room_id')
                ->take(3)
                ->get()
                ->map(function ($room) {

                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'description' => $room->description,
                            'rate_per_night' => $room->rate_per_night,
                            'max_pax' => $room->max_pax
                        ]
                    ];
                });


            $roomsSecond = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->where('rooms.status', 'available')
                ->orderBy('rooms.room_id')
                ->skip(3)
                ->take(3)
                ->get()
                ->map(function ($room) {
                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'description' => $room->description,
                            'rate_per_night' => $room->rate_per_night,
                            'max_pax' => $room->max_pax
                        ]
                    ];
                });


            $roomsMobFirst = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->where('rooms.status', 'available')
                ->orderBy('rooms.room_id')
                ->take(1)
                ->get()
                ->map(function ($room) {
                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'description' => $room->description,
                            'rate_per_night' => $room->rate_per_night,
                            'max_pax' => $room->max_pax
                        ]
                    ];
                });


            $roomsMobSecond = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->where('rooms.status', 'available')
                ->orderBy('rooms.room_id')
                ->skip(1)
                ->take(5)
                ->get()
                ->map(function ($room) {
                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'description' => $room->description,
                            'rate_per_night' => $room->rate_per_night,
                            'max_pax' => $room->max_pax
                        ]
                    ];
                });

            return view('frontpages.landingpage', compact('roomsFirst', 'roomsSecond', 'roomsMobFirst', 'roomsMobSecond'));

        } catch (\Exception $e) {
            \Log::error('Error loading landing page: ' . $e->getMessage());
            return view('frontpages.landingpage')->with('error', 'Unable to load rooms');
        }
    }

    public function roomDetails($room_id)
    {
        try {

            $roomData = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.room_id', $room_id)
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->first();

            if (!$roomData) {
                return redirect()->route('frontpage.index')->with('error', 'Room not found');
            }


            $room = (object) [
                'room_id' => $roomData->room_id,
                'room_type_id' => $roomData->room_type_id,
                'image_path' => $roomData->image_path,
                'status' => $roomData->status,
                'roomType' => (object) [
                    'room_type_name' => $roomData->room_type_name,
                    'description' => $roomData->description,
                    'rate_per_night' => $roomData->rate_per_night,
                    'max_pax' => $roomData->max_pax
                ]
            ];


            $amenities = DB::table('room_amenities')
                ->join('amenities', 'room_amenities.amenity_id', '=', 'amenities.amenity_id')
                ->where('room_amenities.room_id', $room_id)
                ->pluck('amenities.amenity_name')
                ->toArray();


            $similarRooms = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.room_type_id', $room->room_type_id)
                ->where('rooms.room_id', '!=', $room_id)
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->take(3)
                ->get()
                ->map(function ($room) {
                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'description' => $room->description,
                            'rate_per_night' => $room->rate_per_night,
                            'max_pax' => $room->max_pax
                        ]
                    ];
                });


            $allRooms = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.status', 'available')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.rate_per_night'
                )
                ->get()
                ->map(function ($room) {
                    return (object) [
                        'room_id' => $room->room_id,
                        'room_type_id' => $room->room_type_id,
                        'image_path' => $room->image_path,
                        'status' => $room->status,
                        'roomType' => (object) [
                            'room_type_name' => $room->room_type_name,
                            'rate_per_night' => $room->rate_per_night
                        ]
                    ];
                });

            return view('frontpages.room-details', compact('room', 'amenities', 'similarRooms', 'allRooms'));

        } catch (\Exception $e) {
            \Log::error('Error loading room details: ' . $e->getMessage());
            return redirect()->route('frontpage.index')->with('error', 'Unable to load room details');
        }
    }
    public function availableRooms(Request $request)
    {
        try {
            // ── Base query ──────────────────────────────────────────
            $query = DB::table('rooms')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_type_id',
                    'rooms.image_path',
                    'rooms.status',
                    'room_types.room_type_name',
                    'room_types.description',
                    'room_types.rate_per_night',
                    'room_types.max_pax'
                )
                ->where('rooms.status', 'available');

            // ── Filter: room types ───────────────────────────────────
            if ($request->filled('room_types')) {
                $query->whereIn('rooms.room_type_id', (array) $request->room_types);
            }

            // ── Filter: minimum capacity ─────────────────────────────
            if ($request->filled('min_pax')) {
                $query->where('room_types.max_pax', '>=', (int) $request->min_pax);
            }

            // ── Filter: guests from hero search ─────────────────────
            if ($request->filled('guests')) {
                $guests = (int) filter_var($request->guests, FILTER_SANITIZE_NUMBER_INT);
                if ($guests > 0) {
                    $query->where('room_types.max_pax', '>=', $guests);
                }
            }

            // ── Filter: max price ────────────────────────────────────
            if ($request->filled('max_price')) {
                $query->where('room_types.rate_per_night', '<=', (float) $request->max_price);
            }

            // ── Sort ─────────────────────────────────────────────────
            match ($request->input('sort', 'price_asc')) {
                'price_desc' => $query->orderBy('room_types.rate_per_night', 'desc'),
                'name_asc'   => $query->orderBy('room_types.room_type_name', 'asc'),
                default      => $query->orderBy('room_types.rate_per_night', 'asc'),
            };

            // ── Paginate & reshape ───────────────────────────────────
            $rooms = $query->paginate(9)->through(fn($room) => (object) [
                'room_id'      => $room->room_id,
                'room_type_id' => $room->room_type_id,
                'image_path'   => $room->image_path,
                'status'       => $room->status,
                'roomType'     => (object) [
                    'room_type_name' => $room->room_type_name,
                    'description'    => $room->description,
                    'rate_per_night' => $room->rate_per_night,
                    'max_pax'        => $room->max_pax,
                ],
            ]);

            // ── Sidebar: room type counts ────────────────────────────
            $roomTypes = DB::table('room_types')
                ->leftJoin('rooms', function ($join) {
                    $join->on('rooms.room_type_id', '=', 'room_types.room_type_id')
                        ->where('rooms.status', 'available');
                })
                ->select('room_types.room_type_id', 'room_types.room_type_name',
                        DB::raw('COUNT(rooms.room_id) as room_count'))
                ->groupBy('room_types.room_type_id', 'room_types.room_type_name')
                ->having('room_count', '>', 0)
                ->get();

            // ── Sidebar: price range ─────────────────────────────────
            $priceRange = DB::table('room_types')
                ->join('rooms', 'rooms.room_type_id', '=', 'room_types.room_type_id')
                ->where('rooms.status', 'available')
                ->selectRaw('MIN(rate_per_night) as min_price, MAX(rate_per_night) as max_price')
                ->first();

            $priceMin = (int) ($priceRange->min_price ?? 0);
            $priceMax = (int) ($priceRange->max_price ?? 10000);

            return view('frontpages.available-rooms',
                compact('rooms', 'roomTypes', 'priceMin', 'priceMax'));

        } catch (\Exception $e) {
            \Log::error('Error loading available rooms: ' . $e->getMessage());
            return redirect()->route('frontpage.index')->with('error', 'Unable to load rooms.');
        }
    }
    public function sampleLanding()
    {
        return view('frontpages.landingpage-sample');
    }
}
