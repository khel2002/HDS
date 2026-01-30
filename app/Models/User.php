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

    protected $fillable = [
        'role_id',
        'email',
        'password',
        'temporary_act',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'temporary_act' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get the role of the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the guest details for the user
     */
    public function guestDetails()
    {
        return $this->hasOne(GuestDetail::class, 'user_id', 'user_id');
    }

    /**
     * Get the reservations for the user
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id', 'user_id');
    }

    /**
     * Get the registrations for the user
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'user_id', 'user_id');
    }

    /**
     * Get the service requests made by the user
     */
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'requested_by_user_id', 'user_id');
    }
}
