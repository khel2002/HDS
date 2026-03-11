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
        'payment_type',
        'amount',
        'payment_date',
        'payment_status',
        'stripe_session_id',
        'stripe_payment_intent',
        'paid_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'payment_id', 'payment_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'payment_id', 'payment_id');
    }

    // ── Scopes ────────────────────────────────────────────────

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

    public function scopeStripe($query)
    {
        return $query->whereNotNull('stripe_session_id');
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
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

    // ── Helpers ───────────────────────────────────────────────

    public function isStripePayment(): bool
    {
        return !empty($this->stripe_session_id);
    }

    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    public function getProviderAttribute(): string
    {
        return $this->isStripePayment() ? 'Stripe' : 'Cash';
    }
}