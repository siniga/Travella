<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name','iso2','iso3','currency','flag_img','background_img'];
    public function providers() {
      return $this->belongsToMany(Provider::class, 'country_provider')
        ->withPivot(['id','is_default','settings'])
        ->withTimestamps();
    }
}
