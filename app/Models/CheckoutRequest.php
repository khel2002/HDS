<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutRequest extends Model
{
    protected $table      = 'checkout_requests';
    protected $primaryKey = 'id';

    protected $fillable = [
        'registration_id',
        'requested_by',
        'status',
        'notes',
        'staff_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function damageCharges()
    {
        return $this->hasMany(CheckoutDamageCharge::class, 'checkout_request_id');
    }
}