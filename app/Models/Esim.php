<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Esim extends Model
{
    protected $fillable = [
        'sim_id',
        'msisdn',
        'network_id',
        'iccid',
        'imsi',
        'description',
        'status',
        'balances',
        'balance_fetched_at',
    ];

    protected $casts = [
        'network_id' => 'integer',
        'balances' => 'array',
        'balance_fetched_at' => 'datetime',
    ];

    public static function normalizeMsisdn(string $msisdn): string
    {
        return ltrim(preg_replace('/\s+/', '', trim($msisdn)), '+');
    }

    public static function findByMsisdn(string $msisdn): ?self
    {
        $normalized = self::normalizeMsisdn($msisdn);

        return static::query()
            ->whereIn('msisdn', [$normalized, '+'.$normalized])
            ->first();
    }
}

