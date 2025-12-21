<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'bundle_id',
        'bundle_name',
        'data_amount',
        'validity_days',
        'price',
        'currency',
        'metadata'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'data_amount' => 'integer',
        'validity_days' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the bundle associated with the order item.
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }
}
