<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Country;
use App\Models\CountryProvider;
use App\Models\Bundle;
use App\Models\BundleType;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
  public function bundles(Request $r, Provider $provider) {
    $r->validate([
      'country' => ['required','string','size:2'],
      'type'    => ['nullable','string','in:DATA,VOICE,SMS,COMBO'],
      'active'  => ['nullable','boolean']
    ]);

    $country = Country::where('iso2', strtoupper($r->country))->firstOrFail();
    $pivot   = CountryProvider::where('country_id',$country->id)->where('provider_id',$provider->id)->firstOrFail();

    $q = Bundle::with('type')
      ->where('country_provider_id',$pivot->id);

    if ($r->filled('type')) {
      $typeId = BundleType::where('code', strtoupper($r->type))->value('id');
      $q->where('bundle_type_id', $typeId);
    }
    if ($r->filled('active')) {
      $q->where('active', (bool)$r->boolean('active'));
    }

    // Optional filters: min/max price, search by name
    if ($search = $r->query('search')) $q->where('name','ILIKE',"%{$search}%");
    if ($min = $r->query('min_price')) $q->where('price','>=',$min);
    if ($max = $r->query('max_price')) $q->where('price','<=',$max);

    $bundles = $q->orderBy('price')->get()->map(function($b){
      return [
        'id'            => $b->id,
        'name'          => $b->name,
        'type'          => $b->type->code,
        'validity_days' => $b->validity_days,
        'data_mb'       => $b->data_mb,
        'voice_minutes' => $b->voice_minutes,
        'sms'           => $b->sms,
        'price'         => $b->price,
        'currency'      => $b->currency,
        'active'        => $b->active,
      ];
    });

    return response()->json([
      'country'  => ['name'=>$country->name,'iso2'=>$country->iso2],
      'provider' => ['id'=>$provider->id,'name'=>$provider->name,'slug'=>$provider->slug],
      'bundles'  => $bundles
    ]);
  }
}

