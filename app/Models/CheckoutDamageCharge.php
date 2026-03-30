<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutDamageCharge extends Model
{
    protected $table      = 'checkout_damage_charges';
    protected $primaryKey = 'id';

    protected $fillable = [
        'registration_id',
        'checkout_request_id',
        'added_by',
        'description',
        'amount',
        'is_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function checkoutRequest()
    {
        return $this->belongsTo(CheckoutRequest::class, 'checkout_request_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}