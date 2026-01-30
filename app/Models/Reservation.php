<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';

    protected $fillable = [
        'user_id',
        'guest_details_id',
        'payment_id',
        'room_id',
        'reservation_fee',
        'purpose',
        'total_amount',
        'balance',
        'reservation_status',
        'booking_date',
        'adults',
        'children',
        'no_nights',
        'check_in_date',
        'check_out_date',
        'reservation_fee_paid',
    ];

    protected $casts = [
        'reservation_fee' => 'integer',
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'booking_date' => 'date',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'no_nights' => 'integer',
        'reservation_fee_paid' => 'boolean',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get the user that made the reservation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the guest details for the reservation
     */
    public function guestDetails()
    {
        return $this->belongsTo(GuestDetail::class, 'guest_details_id', 'guest_details_id');
    }

    /**
     * Get the payment for the reservation
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    /**
     * Get the room for the reservation
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * Get the registration for this reservation
     */
    public function registration()
    {
        return $this->hasOne(Registration::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Get the total number of guests (computed field)
     */
    public function getNoOfPaxAttribute()
    {
        return $this->adults + $this->children;
    }

    /**
     * Scope a query to only include pending reservations
     */
    public function scopePending($query)
    {
        return $query->where('reservation_status', 'pending');
    }

    /**
     * Scope a query to only include approved reservations
     */
    public function scopeApproved($query)
    {
        return $query->where('reservation_status', 'approved');
    }

    /**
     * Scope a query to only include rejected reservations
     */
    public function scopeRejected($query)
    {
        return $query->where('reservation_status', 'rejected');
    }

    /**
     * Scope a query to only include cancelled reservations
     */
    public function scopeCancelled($query)
    {
        return $query->where('reservation_status', 'cancelled');
    }
}
