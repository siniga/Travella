<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model {
    protected $fillable = ['name','slug','metadata'];
    protected $casts = ['metadata' => 'array'];
    
    public function getRouteKeyName() {
        return 'slug';
    }
    
    public function countries() {
      return $this->belongsToMany(Country::class, 'country_provider')
        ->withPivot(['id','is_default','settings'])
        ->withTimestamps();
    }
    public function bundles() {
      // through pivot table
      return $this->hasManyThrough(Bundle::class, \App\Models\CountryProvider::class, 'provider_id', 'country_provider_id');
    }
  }