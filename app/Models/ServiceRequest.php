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
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false;

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_id', 'registration_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id', 'user_id');
    }

    public function breakfastOrders()
    {
        return $this->hasMany(ServiceBreakfastOrder::class, 'service_request_id', 'service_request_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('request_status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('request_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('request_status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('request_status', 'cancelled');
    }

    public function scopeFood($query)
    {
        return $query->where('service_type', 'food');
    }

    public function scopeRoomService($query)
    {
        return $query->where('service_type', 'room_service');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('requested_at', today());
    }

    // Methods
    public function isPending()
    {
        return $this->request_status === 'pending';
    }

    public function isCompleted()
    {
        return $this->request_status === 'completed';
    }

    public function markAsCompleted()
    {
        $this->update([
            'request_status' => 'completed',
            'completed_at' => now(),
        ]);
    }
    public function roomOrderItems()
    {
        return $this->hasMany(
            ServiceRoomOrderItem::class,
            'service_request_id',
            'service_request_id'
        );
    }
}
