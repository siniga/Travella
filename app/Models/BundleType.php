<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleType extends Model {
    protected $fillable = ['code','name'];
    public function bundles(){ return $this->hasMany(Bundle::class); }
  }
  