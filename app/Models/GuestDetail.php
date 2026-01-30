<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestDetail extends Model
{
    use HasFactory;

    protected $table = 'guest_details';
    protected $primaryKey = 'guest_details_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'dob',
        'arrival_date',
        'departure_date',
    ];

    protected $casts = [
        'dob' => 'date',
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get the user that owns the guest details
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the reservations for the guest
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'guest_details_id', 'guest_details_id');
    }

    /**
     * Get the registrations for the guest
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'guest_details_id', 'guest_details_id');
    }

    /**
     * Get the guest's full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }
}
