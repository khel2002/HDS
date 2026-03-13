<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakfastMenu extends Model
{
    protected $table      = 'breakfast_menu';
    protected $primaryKey = 'breakfast_id';
    public    $timestamps = false;

    protected $fillable = [
        'meal_name',
        'description',
        'price',
        'is_available',
        'image_path',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /* ── Relationships ──────────────────────────────────────────── */

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceBreakfastOrder::class, 'breakfast_id', 'breakfast_id');
    }
}