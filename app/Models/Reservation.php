<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';

    // Disable automatic timestamp management since the table doesn't have updated_at
    public $timestamps = false;

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
        'no_of_pax',
        'no_nights',
        'check_in_date',
        'check_out_date',
        'reservation_fee_paid',
        'created_at', // Add this to fillable since we're managing it manually
    ];

    protected $casts = [
        'booking_date' => 'date',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'reservation_fee_paid' => 'boolean',
        'reservation_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'adults' => 'integer',
        'children' => 'integer',
        'no_nights' => 'integer',
        'created_at' => 'datetime',
        // Remove 'updated_at' from casts since it doesn't exist
    ];

    // Rest of your model code remains the same...

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function guestDetails()
    {
        return $this->belongsTo(GuestDetail::class, 'guest_details_id', 'guest_details_id');
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

    public function getArrivalDateAttribute()
    {
        return $this->check_in_date;
    }

    public function getDepartureDateAttribute()
    {
        return $this->check_out_date;
    }

    public function getFormattedCheckInAttribute()
    {
        return $this->check_in_date ? $this->check_in_date->format('F d, Y') : null;
    }

    public function getFormattedCheckOutAttribute()
    {
        return $this->check_out_date ? $this->check_out_date->format('F d, Y') : null;
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->reservation_fee_paid ? $this->balance : $this->total_amount;
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

    public function scopeCheckingInBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_in_date', [$startDate, $endDate]);
    }

    public function scopeCheckingOutBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_out_date', [$startDate, $endDate]);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('reservation_status', ['pending', 'approved'])
                     ->where('check_out_date', '>=', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('check_in_date', '>', today())
                     ->whereIn('reservation_status', ['pending', 'approved']);
    }

    public function scopeCurrent($query)
    {
        return $query->where('check_in_date', '<=', today())
                     ->where('check_out_date', '>=', today())
                     ->whereIn('reservation_status', ['approved']);
    }

    public function scopePast($query)
    {
        return $query->where('check_out_date', '<', today());
    }

    // Helper Methods
    public function calculateNights()
    {
        if ($this->check_in_date && $this->check_out_date) {
            return $this->check_in_date->diffInDays($this->check_out_date);
        }
        return 0;
    }

    public function isActive()
    {
        return in_array($this->reservation_status, ['pending', 'approved'])
               && $this->check_out_date >= today();
    }

    public function isUpcoming()
    {
        return $this->check_in_date > today()
               && in_array($this->reservation_status, ['pending', 'approved']);
    }

    public function isCurrent()
    {
        return $this->check_in_date <= today()
               && $this->check_out_date >= today()
               && $this->reservation_status === 'approved';
    }

    public function isPast()
    {
        return $this->check_out_date < today();
    }

    public function canBeCancelled()
    {
        return in_array($this->reservation_status, ['pending', 'approved'])
               && $this->check_in_date > today();
    }

    public function canBeModified()
    {
        return $this->reservation_status === 'pending'
               && $this->check_in_date > today()->addDays(2);
    }
}
