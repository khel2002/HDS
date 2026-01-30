<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';
    protected $primaryKey = 'room_type_id';
    public $timestamps = false;

    protected $fillable = [
        'room_type_name',
        'description',
        'rate_per_night',
        'max_pax',
    ];

    protected $casts = [
        'rate_per_night' => 'decimal:2',
        'max_pax' => 'integer',
    ];

    /**
     * Get the rooms of this type
     */
    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id', 'room_type_id');
    }
}
