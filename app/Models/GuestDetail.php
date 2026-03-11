<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestDetail extends Model
{
    use HasFactory;

    protected $table = 'guest_details';
    protected $primaryKey = 'guest_details_id';

    protected $fillable = [
        'user_id',
        'reservation_id',   // which reservation (room) this guest belongs to
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'dob',
        'arrival_date',
        'departure_date',
        'is_primary',       // 1 = lead guest of this room, 0 = additional guest
    ];

    protected $casts = [
        'dob'            => 'date',
        'arrival_date'   => 'date',
        'departure_date' => 'date',
        'created_at'     => 'datetime',
        'is_primary'     => 'boolean',
    ];

    public $timestamps = false;

    // ── Relationships ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'guest_details_id', 'guest_details_id');
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopePrimary($query)
    {
        return $query->where('is_primary', 1);
    }

    public function scopeAdditional($query)
    {
        return $query->where('is_primary', 0);
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]));
    }
}