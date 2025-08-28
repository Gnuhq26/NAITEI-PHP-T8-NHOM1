<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = ['customer_id', 'order_date', 'total_cost', 'shipping_fee', 'status'];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    public function statusOrders(): HasMany
    {
        return $this->hasMany(StatusOrder::class, 'order_id');
    }

    public function deliveryInfo(): HasOne
    {
        return $this->hasOne(DeliveryInfo::class, 'order_id', 'order_id');
    }
}
