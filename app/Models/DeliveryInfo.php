<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryInfo extends Model
{
    use HasFactory;

    protected $table = 'delivery_info';

    protected $fillable = [
        'user_name',
        'email',
        'phone_number',
        'country',
        'city',
        'district',
        'ward',
        'order_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
