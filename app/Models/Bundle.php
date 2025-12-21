<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model {
    protected $fillable = [
      'bundle_type_id','country_provider_id','name','validity_days',
      'data_mb','voice_minutes','sms','price','currency','active','metadata'
    ];
    protected $casts = ['metadata' => 'array', 'active' => 'boolean'];
    public function type(){ return $this->belongsTo(BundleType::class, 'bundle_type_id'); }
    public function countryProvider(){ return $this->belongsTo(CountryProvider::class); }
  }
  