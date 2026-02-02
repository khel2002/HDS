<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'email',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'STATUS',
        'temporary_act',
        'last_login_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'temporary_act' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the guest details for the user.
     */
    public function guestDetails()
    {
        return $this->hasOne(GuestDetails::class, 'user_id', 'user_id');
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id', 'user_id');
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Check if user is active in HRMIS.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return strtolower($this->STATUS ?? '') === 'active';
    }

    /**
     * Check if user has a specific role.
     *
     * @param int $roleId
     * @return bool
     */
    public function hasRole(int $roleId): bool
    {
        return $this->role_id === $roleId;
    }

    /**
     * Check if user is admin (role_id = 1).
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->hasRole(1);
    }

    /**
     * Check if user is staff (role_id = 2).
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->hasRole(2);
    }

    /**
     * Check if user is manager (role_id = 3).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(3);
    }

    /**
     * Check if user is guest (role_id = 4).
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(4);
    }
}
