<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'role_id',
        'email',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'temporary_act',
        'last_login_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'last_login_at' => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'temporary_act' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function guestDetails()
    {
        return $this->hasOne(GuestDetail::class, 'user_id', 'user_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id', 'user_id');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Map ->id to ->user_id so blade can use $user->id
     */
    public function getIdAttribute(): int
    {
        return $this->attributes['user_id'];
    }

    /**
     * Map ->name to full name so blade can use $user->name
     */
    public function getNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->attributes['first_name'] ?? null,
            $this->attributes['middle_name'] ?? null,
            $this->attributes['last_name']   ?? null,
        ])));
    }

    /**
     * Map ->status to the STATUS column
     */
    public function getStatusAttribute(): ?string
    {
        return $this->attributes['STATUS'] ?? null;
    }

    /**
     * Map ->role to the role_name string (e.g. 'super_admin', 'admin', 'staff', 'guest')
     */
    public function getRoleAttribute(): ?string
    {
        static $cache = [];
        $id = $this->attributes['role_id'] ?? null;
        if (!$id) return null;
        if (!isset($cache[$id])) {
            $cache[$id] = Role::find($id)?->role_name;
        }
        return $cache[$id];
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    public function setStatusAttribute($value): void
    {
        $this->attributes['STATUS'] = $value;
    }

    // ── Helper Methods ───────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->getNameAttribute();
    }

    public function isActive(): bool
    {
        return strtolower($this->attributes['STATUS'] ?? '') === 'active';
    }

    public function hasRole(int $roleId): bool
    {
        return (int)($this->attributes['role_id'] ?? 0) === $roleId;
    }

    public function isGuest(): bool
    {
        return $this->hasRole(1);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(2);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(3);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(4);
    }
}