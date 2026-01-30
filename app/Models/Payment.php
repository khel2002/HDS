<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'payment_method',
        'amount',
        'payment_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    const CREATED_AT = 'payment_date';
    const UPDATED_AT = null;

    /**
     * Get the reservations associated with this payment
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'payment_id', 'payment_id');
    }

    /**
     * Get the registrations associated with this payment
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'payment_id', 'payment_id');
    }
}
