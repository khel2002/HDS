<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakfastMenu extends Model
{
    use HasFactory;

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

    public function serviceBreakfastOrders()
    {
        return $this->hasMany(ServiceBreakfastOrder::class, 'breakfast_id', 'breakfast_id');
    }
}