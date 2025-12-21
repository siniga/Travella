<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryProvider extends Model {
    protected $table = 'country_provider';
    protected $fillable = ['country_id','provider_id','is_default','settings'];
    protected $casts = ['settings' => 'array'];
    public function country(){ return $this->belongsTo(Country::class); }
    public function provider(){ return $this->belongsTo(Provider::class); }
    public function bundles(){ return $this->hasMany(Bundle::class, 'country_provider_id'); }
  }
  
