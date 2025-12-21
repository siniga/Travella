<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\BundleType;
use App\Models\CountryProvider;
use App\Models\Country;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class BundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get bundle types
        $dataType = BundleType::where('code', 'DATA')->first();
        $voiceType = BundleType::where('code', 'VOICE')->first();
        $smsType = BundleType::where('code', 'SMS')->first();
        $comboType = BundleType::where('code', 'COMBO')->first();

        // Get countries
        $tanzania = Country::where('iso2', 'TZ')->first();
        $kenya = Country::where('iso2', 'KE')->first();

        // Get providers
        $ttcl = Provider::where('slug', 'ttcl')->first();
        $airtel = Provider::where('slug', 'airtel')->first();
        $vodacom = Provider::where('slug', 'vodacom')->first();
        $safaricom = Provider::where('slug', 'safaricom')->first();

        // Tanzania - TTCL Bundles
        if ($tanzania && $ttcl) {
            $tz_ttcl = CountryProvider::where('country_id', $tanzania->id)
                ->where('provider_id', $ttcl->id)
                ->first();

            if ($tz_ttcl && $dataType) {
                // Daily data bundles
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Daily 500MB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 1,
                        'data_mb' => 500,
                        'price' => 1000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Daily 1GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 1,
                        'data_mb' => 1024,
                        'price' => 2000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                // Weekly data bundles
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Weekly 3GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 7,
                        'data_mb' => 3072,
                        'price' => 5000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Weekly 10GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 7,
                        'data_mb' => 10240,
                        'price' => 15000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                // Monthly data bundles
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Monthly 20GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 30,
                        'data_mb' => 20480,
                        'price' => 25000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Monthly 50GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 30,
                        'data_mb' => 51200,
                        'price' => 50000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }

            // Combo bundles
            if ($tz_ttcl && $comboType) {
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Weekly Combo'],
                    [
                        'bundle_type_id' => $comboType->id,
                        'validity_days' => 7,
                        'data_mb' => 5120,
                        'voice_minutes' => 100,
                        'sms' => 100,
                        'price' => 12000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Monthly Super Combo'],
                    [
                        'bundle_type_id' => $comboType->id,
                        'validity_days' => 30,
                        'data_mb' => 30720,
                        'voice_minutes' => 500,
                        'sms' => 500,
                        'price' => 40000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }

            // Voice only bundles
            if ($tz_ttcl && $voiceType) {
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_ttcl->id, 'name' => 'Daily 30 Minutes'],
                    [
                        'bundle_type_id' => $voiceType->id,
                        'validity_days' => 1,
                        'voice_minutes' => 30,
                        'price' => 1500.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }
        }

        // Tanzania - Vodacom Bundles
        if ($tanzania && $vodacom) {
            $tz_vodacom = CountryProvider::where('country_id', $tanzania->id)
                ->where('provider_id', $vodacom->id)
                ->first();

            if ($tz_vodacom && $dataType) {
                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_vodacom->id, 'name' => 'Daily 1GB Special'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 1,
                        'data_mb' => 1024,
                        'price' => 1800.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => true])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_vodacom->id, 'name' => 'Weekly 15GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 7,
                        'data_mb' => 15360,
                        'price' => 18000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $tz_vodacom->id, 'name' => 'Monthly 100GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 30,
                        'data_mb' => 102400,
                        'price' => 80000.00,
                        'currency' => 'TZS',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }
        }

        // Kenya - Safaricom Bundles
        if ($kenya && $safaricom) {
            $ke_safaricom = CountryProvider::where('country_id', $kenya->id)
                ->where('provider_id', $safaricom->id)
                ->first();

            if ($ke_safaricom && $dataType) {
                Bundle::updateOrCreate(
                    ['country_provider_id' => $ke_safaricom->id, 'name' => 'Daily 1GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 1,
                        'data_mb' => 1024,
                        'price' => 100.00,
                        'currency' => 'KES',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $ke_safaricom->id, 'name' => 'Weekly 6GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 7,
                        'data_mb' => 6144,
                        'price' => 500.00,
                        'currency' => 'KES',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );

                Bundle::updateOrCreate(
                    ['country_provider_id' => $ke_safaricom->id, 'name' => 'Monthly 40GB'],
                    [
                        'bundle_type_id' => $dataType->id,
                        'validity_days' => 30,
                        'data_mb' => 40960,
                        'price' => 2000.00,
                        'currency' => 'KES',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }

            if ($ke_safaricom && $comboType) {
                Bundle::updateOrCreate(
                    ['country_provider_id' => $ke_safaricom->id, 'name' => 'Monthly Mega Combo'],
                    [
                        'bundle_type_id' => $comboType->id,
                        'validity_days' => 30,
                        'data_mb' => 25600,
                        'voice_minutes' => 300,
                        'sms' => 300,
                        'price' => 1500.00,
                        'currency' => 'KES',
                        'active' => true,
                        'metadata' => json_encode(['promo' => false])
                    ]
                );
            }
        }
    }
}

