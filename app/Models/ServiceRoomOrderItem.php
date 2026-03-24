<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRoomOrderItem extends Model
{
    protected $table = 'service_room_order_items';

    protected $fillable = [
        'service_request_id',
        'item_id',
        'quantity',
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'service_request_id');
    }

    public function roomServiceItem(): BelongsTo
    {
        return $this->belongsTo(RoomServiceItem::class, 'item_id', 'item_id');
    }
}