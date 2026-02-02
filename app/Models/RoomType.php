<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';
    protected $primaryKey = 'room_type_id';

    protected $fillable = [
        'room_type_name',
        'description',
        'rate_per_night',
        'max_pax',
    ];

    public $timestamps = false;

    protected $casts = [
        'rate_per_night' => 'decimal:2',
    ];

    // Relationships
    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id', 'room_type_id');
    }

    // Methods
    public function getAvailableRoomsCount()
    {
        return $this->rooms()->where('status', 'available')->count();
    }

    public function getOccupiedRoomsCount()
    {
        return $this->rooms()->where('status', 'occupied')->count();
    }

    public function getTotalRoomsCount()
    {
        return $this->rooms()->count();
    }
}
