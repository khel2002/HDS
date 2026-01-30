<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $table = 'amenities';
    protected $primaryKey = 'amenity_id';
    public $timestamps = false;

    protected $fillable = [
        'amenity_name',
    ];

    /**
     * Get the rooms that have this amenity
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenities', 'amenity_id', 'room_id');
    }
}
