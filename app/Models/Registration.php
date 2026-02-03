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

    public $timestamps = false;

    // Relationships
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function guestDetails()
    {
        return $this->belongsTo(GuestDetail::class, 'guest_details_id', 'guest_details_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'registration_id', 'registration_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('check_out_date');
    }

    public function scopeCheckedOut($query)
    {
        return $query->whereNotNull('check_out_date');
    }

    public function scopeCheckingOutToday($query)
    {
        return $query->whereDate('check_out_date', today());
    }

    // Methods
    public function isActive()
    {
        return is_null($this->check_out_date);
    }

    public function isCheckedOut()
    {
        return !is_null($this->check_out_date);
    }
}
