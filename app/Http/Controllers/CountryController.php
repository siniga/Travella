<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
  public function index() {
    $countries = Country::query()->orderBy('name')->get();
    return response()->json(['countries' => $countries]);
  }

  public function providers($iso2) {
    $country = Country::where('iso2', strtoupper($iso2))->firstOrFail();
    $rows = $country->providers()->withPivot('id','is_default')->get()
      ->map(fn($p) => [
        'id'         => $p->id,
        'name'       => $p->name,
        'slug'       => $p->slug,
        'country_provider_id' => $p->pivot->id,
        'is_default' => (bool)$p->pivot->is_default,
      ])->sortByDesc('is_default')->values();
    return response()->json([
      'country' => ['name'=>$country->name,'iso2'=>$country->iso2,'currency'=>$country->currency],
      'providers' => $rows
    ]);
  }
}

