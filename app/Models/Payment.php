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
        'payment_date',
        'payment_status',
        'stripe_session_id',      // NEW: Stripe checkout session ID
        'stripe_payment_intent',  // NEW: Stripe payment intent ID
        'paid_at',               // NEW: Timestamp when payment was completed
    ];

    public $timestamps = false;

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'paid_at' => 'datetime',  // NEW: Cast paid_at to datetime
    ];

    // Relationships
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'payment_id', 'payment_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'payment_id', 'payment_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeOnline($query)
    {
        return $query->where('payment_method', 'online');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }

    // NEW: Scope for Stripe payments
    public function scopeStripe($query)
    {
        return $query->whereNotNull('stripe_session_id');
    }

    // NEW: Scope for paid payments
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    // NEW: Check if payment was made via Stripe
    public function isStripePayment()
    {
        return !empty($this->stripe_session_id);
    }

    // NEW: Check if payment has been completed
    public function isPaid()
    {
        return !is_null($this->paid_at);
    }

    // NEW: Get payment provider name
    public function getProviderAttribute()
    {
        return $this->isStripePayment() ? 'Stripe' : 'Cash';
    }
}
