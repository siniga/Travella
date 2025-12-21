<?php

// app/Models/Counter.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Counter extends Model
{
    protected $fillable = ['key','year','value'];

    public static function next(string $key, int $year): int
    {
        return DB::transaction(function () use ($key, $year) {
            $counter = self::where('key', $key)->where('year', $year)->lockForUpdate()->first();

            if (!$counter) {
                $counter = self::create(['key' => $key, 'year' => $year, 'value' => 0]);
                // lock row after create
                $counter = self::where('id', $counter->id)->lockForUpdate()->first();
            }

            $counter->value += 1;
            $counter->save();

            return $counter->value;
        }, 3);
    }
}
