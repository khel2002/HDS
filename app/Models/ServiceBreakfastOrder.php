<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBreakfastOrder extends Model
{
    use HasFactory;

    protected $table = 'service_breakfast_orders';
    protected $primaryKey = 'service_order_id';
    public $timestamps = false;

    protected $fillable = [
        'service_request_id',
        'breakfast_id',
        'quantity',
        'price_at_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_order' => 'decimal:2',
    ];

    /**
     * Get the service request for this order
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'service_request_id');
    }

    /**
     * Get the breakfast menu item for this order
     */
    public function breakfastMenu()
    {
        return $this->belongsTo(BreakfastMenu::class, 'breakfast_id', 'breakfast_id');
    }

    /**
     * Get the total price for this order line
     */
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price_at_order;
    }
}
