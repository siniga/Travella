<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentLocation extends Model
{
    protected $table = 'agent_location';

    protected $fillable = [
        'user_id',
        'phone',
        'work_station',
        'current_location',
        'current_location_updated_at',
    ];

    protected $casts = [
        'current_location_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
