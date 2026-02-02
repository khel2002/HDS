<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomAmenity extends Model
{
    protected $table = 'room_amenities';

    // Pivot table → no auto-increment ID
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'amenity_id',
    ];

    /**
     * Relationship to Room
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Relationship to Amenity
     */
    public function amenity()
    {
        return $this->belongsTo(Amenity::class, 'amenity_id');
    }
}
