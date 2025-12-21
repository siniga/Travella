<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Provider;
use App\Models\CountryProvider;
use Illuminate\Database\Seeder;

class CountryProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get countries
        $tanzania = Country::where('iso2', 'TZ')->first();
        $kenya = Country::where('iso2', 'KE')->first();
        $uganda = Country::where('iso2', 'UG')->first();
        $southAfrica = Country::where('iso2', 'ZA')->first();

        // Get providers
        $ttcl = Provider::where('slug', 'ttcl')->first();
        $airtel = Provider::where('slug', 'airtel')->first();
        $vodacom = Provider::where('slug', 'vodacom')->first();
        $halotel = Provider::where('slug', 'halotel')->first();
        $safaricom = Provider::where('slug', 'safaricom')->first();
        $mtn = Provider::where('slug', 'mtn')->first();

        // Tanzania providers
        if ($tanzania && $ttcl) {
            CountryProvider::updateOrCreate(
                ['country_id' => $tanzania->id, 'provider_id' => $ttcl->id],
                [
                    'is_default' => true,
                    'settings' => json_encode(['prefix' => '255', 'coverage' => '95%'])
                ]
            );
        }

        if ($tanzania && $airtel) {
            CountryProvider::updateOrCreate(
                ['country_id' => $tanzania->id, 'provider_id' => $airtel->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '255', 'coverage' => '90%'])
                ]
            );
        }

        if ($tanzania && $vodacom) {
            CountryProvider::updateOrCreate(
                ['country_id' => $tanzania->id, 'provider_id' => $vodacom->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '255', 'coverage' => '92%'])
                ]
            );
        }

        if ($tanzania && $halotel) {
            CountryProvider::updateOrCreate(
                ['country_id' => $tanzania->id, 'provider_id' => $halotel->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '255', 'coverage' => '85%'])
                ]
            );
        }

        // Kenya providers
        if ($kenya && $safaricom) {
            CountryProvider::updateOrCreate(
                ['country_id' => $kenya->id, 'provider_id' => $safaricom->id],
                [
                    'is_default' => true,
                    'settings' => json_encode(['prefix' => '254', 'coverage' => '96%'])
                ]
            );
        }

        if ($kenya && $airtel) {
            CountryProvider::updateOrCreate(
                ['country_id' => $kenya->id, 'provider_id' => $airtel->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '254', 'coverage' => '88%'])
                ]
            );
        }

        // Uganda providers
        if ($uganda && $mtn) {
            CountryProvider::updateOrCreate(
                ['country_id' => $uganda->id, 'provider_id' => $mtn->id],
                [
                    'is_default' => true,
                    'settings' => json_encode(['prefix' => '256', 'coverage' => '94%'])
                ]
            );
        }

        if ($uganda && $airtel) {
            CountryProvider::updateOrCreate(
                ['country_id' => $uganda->id, 'provider_id' => $airtel->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '256', 'coverage' => '89%'])
                ]
            );
        }

        // South Africa providers
        if ($southAfrica && $vodacom) {
            CountryProvider::updateOrCreate(
                ['country_id' => $southAfrica->id, 'provider_id' => $vodacom->id],
                [
                    'is_default' => true,
                    'settings' => json_encode(['prefix' => '27', 'coverage' => '98%'])
                ]
            );
        }

        if ($southAfrica && $mtn) {
            CountryProvider::updateOrCreate(
                ['country_id' => $southAfrica->id, 'provider_id' => $mtn->id],
                [
                    'is_default' => false,
                    'settings' => json_encode(['prefix' => '27', 'coverage' => '97%'])
                ]
            );
        }
    }
}

