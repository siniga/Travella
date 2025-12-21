<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
  // Countries
  $tz = \App\Models\Country::updateOrCreate(['iso2'=>'TZ'], [
    'name'=>'Tanzania', 
    'iso3'=>'TZA', 
    'currency'=>'TZS',
    'flag_img' => 'https://flagcdn.com/w320/tz.png',
    'background_img' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=1200'
  ]);

  // Providers
  $ttcl   = \App\Models\Provider::updateOrCreate(['slug'=>'ttcl'],   ['name'=>'TTCL']);
  $airtel = \App\Models\Provider::updateOrCreate(['slug'=>'airtel'], ['name'=>'Airtel']);
  $voda   = \App\Models\Provider::updateOrCreate(['slug'=>'vodacom'],['name'=>'Vodacom']);

  // Pivot (TTCL default in TZ)
  $tz_ttcl = \App\Models\CountryProvider::updateOrCreate(
    ['country_id'=>$tz->id,'provider_id'=>$ttcl->id],
    ['is_default'=>true]
  );
  $tz_airtel = \App\Models\CountryProvider::updateOrCreate(
    ['country_id'=>$tz->id,'provider_id'=>$airtel->id], ['is_default'=>false]
  );
  $tz_voda = \App\Models\CountryProvider::updateOrCreate(
    ['country_id'=>$tz->id,'provider_id'=>$voda->id],   ['is_default'=>false]
  );

  // Bundle Types
  $DATA  = \App\Models\BundleType::updateOrCreate(['code'=>'DATA'],  ['name'=>'Data only']);
  $VOICE = \App\Models\BundleType::updateOrCreate(['code'=>'VOICE'], ['name'=>'Voice only']);
  $SMS   = \App\Models\BundleType::updateOrCreate(['code'=>'SMS'],   ['name'=>'SMS only']);
  $COMBO = \App\Models\BundleType::updateOrCreate(['code'=>'COMBO'], ['name'=>'Data + Voice + SMS']);

  // Sample Bundles (TZ - TTCL)
  \App\Models\Bundle::updateOrCreate([
    'country_provider_id'=>$tz_ttcl->id, 'name'=>'Daily 1GB'
  ], [
    'bundle_type_id'=>$DATA->id, 'validity_days'=>1,
    'data_mb'=>1024, 'price'=>2000, 'currency'=>'TZS', 'active'=>true
  ]);

  \App\Models\Bundle::updateOrCreate([
    'country_provider_id'=>$tz_ttcl->id, 'name'=>'Weekly Combo'
  ], [
    'bundle_type_id'=>$COMBO->id, 'validity_days'=>7,
    'data_mb'=>5120, 'voice_minutes'=>100, 'sms'=>100,
    'price'=>12000, 'currency'=>'TZS', 'active'=>true
  ]);
}

}
