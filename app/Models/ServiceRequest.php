<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';
    protected $primaryKey = 'service_request_id';

    protected $fillable = [
        'registration_id',
        'requested_by_user_id',
        'service_type',
        'description',
        'request_status',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const CREATED_AT = 'requested_at';
    const UPDATED_AT = null;

    /**
     * Get the registration for this service request
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id', 'registration_id');
    }

    /**
     * Get the user who made the service request
     */
    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id', 'user_id');
    }

    /**
     * Get the breakfast orders for this service request
     */
    public function breakfastOrders()
    {
        return $this->hasMany(ServiceBreakfastOrder::class, 'service_request_id', 'service_request_id');
    }

    /**
     * Scope a query to only include pending requests
     */
    public function scopePending($query)
    {
        return $query->where('request_status', 'pending');
    }

    /**
     * Scope a query to only include in progress requests
     */
    public function scopeInProgress($query)
    {
        return $query->where('request_status', 'in_progress');
    }

    /**
     * Scope a query to only include completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('request_status', 'completed');
    }

    /**
     * Scope a query to only include cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('request_status', 'cancelled');
    }

    /**
     * Scope a query to only include food service requests
     */
    public function scopeFood($query)
    {
        return $query->where('service_type', 'food');
    }

    /**
     * Scope a query to only include room service requests
     */
    public function scopeRoomService($query)
    {
        return $query->where('service_type', 'room_service');
    }
}
