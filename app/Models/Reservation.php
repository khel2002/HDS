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
        'booking_date' => 'date',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'reservation_fee_paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function guestDetails()
    {
        return $this->belongsTo(GuestDetails::class, 'guest_details_id', 'guest_details_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'reservation_id', 'reservation_id');
    }

    // Accessors
    public function getTotalPaxAttribute()
    {
        return $this->adults + $this->children;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('reservation_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('reservation_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('reservation_status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('reservation_status', 'cancelled');
    }

    public function scopeCheckingInToday($query)
    {
        return $query->whereDate('check_in_date', today());
    }

    public function scopeCheckingOutToday($query)
    {
        return $query->whereDate('check_out_date', today());
    }
}
