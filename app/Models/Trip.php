<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = [
        'order_id',
        'destination_country',
        'arrival_date',
        'departure_date',
        'duration_days',
        'metadata'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'duration_days' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the order that owns the trip.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
