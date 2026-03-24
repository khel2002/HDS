<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomServiceItem extends Model
{
    protected $table      = 'room_service_items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'category',
        'item_name',
        'description',
        'icon',
        'requires_quantity',
        'is_available',
    ];

    protected $casts = [
        'requires_quantity' => 'boolean',
        'is_available'      => 'boolean',
    ];

    public static array $categoryLabels = [
        'housekeeping' => 'Housekeeping',
        'toiletries'   => 'Toiletries',
        'technical'    => 'Technical',
        'comfort'      => 'Comfort',
    ];

    public static array $categoryIcons = [
        'housekeeping' => 'ri-brush-line',
        'toiletries'   => 'ri-flask-line',
        'technical'    => 'ri-tools-line',
        'comfort'      => 'ri-sofa-line',
    ];

    public static array $categoryBadges = [
        'housekeeping' => 'bg-label-info',
        'toiletries'   => 'bg-label-success',
        'technical'    => 'bg-label-warning',
        'comfort'      => 'bg-label-primary',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(ServiceRoomOrderItem::class, 'item_id', 'item_id');
    }
}