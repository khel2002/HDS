<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';
    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'reservation_id',
        'guest_details_id',
        'user_id',
        'payment_id',
        'check_in_at',
        'check_out_date',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_date' => 'date',
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Get the reservation for this registration
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Get the guest details for this registration
     */
    public function guestDetails()
    {
        return $this->belongsTo(GuestDetail::class, 'guest_details_id', 'guest_details_id');
    }

    /**
     * Get the user for this registration
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the payment for this registration
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    /**
     * Get the service requests for this registration
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'registration_id', 'registration_id');
    }
}
