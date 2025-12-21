<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'TTCL',
                'slug' => 'ttcl',
                'metadata' => json_encode([
                    'full_name' => 'Tanzania Telecommunications Company Limited',
                    'logo_url' => 'https://example.com/logos/ttcl.png',
                    'website' => 'https://www.ttcl.co.tz'
                ])
            ],
            [
                'name' => 'Airtel',
                'slug' => 'airtel',
                'metadata' => json_encode([
                    'full_name' => 'Airtel Tanzania',
                    'logo_url' => 'https://example.com/logos/airtel.png',
                    'website' => 'https://www.airtel.co.tz'
                ])
            ],
            [
                'name' => 'Vodacom',
                'slug' => 'vodacom',
                'metadata' => json_encode([
                    'full_name' => 'Vodacom Tanzania',
                    'logo_url' => 'https://example.com/logos/vodacom.png',
                    'website' => 'https://www.vodacom.co.tz'
                ])
            ],
            [
                'name' => 'Halotel',
                'slug' => 'halotel',
                'metadata' => json_encode([
                    'full_name' => 'Halotel Tanzania',
                    'logo_url' => 'https://example.com/logos/halotel.png',
                    'website' => 'https://www.halotel.co.tz'
                ])
            ],
            [
                'name' => 'Safaricom',
                'slug' => 'safaricom',
                'metadata' => json_encode([
                    'full_name' => 'Safaricom Kenya',
                    'logo_url' => 'https://example.com/logos/safaricom.png',
                    'website' => 'https://www.safaricom.co.ke'
                ])
            ],
            [
                'name' => 'MTN',
                'slug' => 'mtn',
                'metadata' => json_encode([
                    'full_name' => 'MTN Group',
                    'logo_url' => 'https://example.com/logos/mtn.png',
                    'website' => 'https://www.mtn.com'
                ])
            ],
        ];

        foreach ($providers as $provider) {
            Provider::updateOrCreate(
                ['slug' => $provider['slug']],
                $provider
            );
        }
    }
}

