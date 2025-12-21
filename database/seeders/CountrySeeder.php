<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Tanzania', 
                'iso2' => 'TZ', 
                'iso3' => 'TZA', 
                'currency' => 'TZS',
                'flag_img' => 'https://flagcdn.com/w320/tz.png',
                'background_img' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=1200'
            ],
            [
                'name' => 'Kenya', 
                'iso2' => 'KE', 
                'iso3' => 'KEN', 
                'currency' => 'KES',
                'flag_img' => 'https://flagcdn.com/w320/ke.png',
                'background_img' => 'https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=1200'
            ],
            [
                'name' => 'Uganda', 
                'iso2' => 'UG', 
                'iso3' => 'UGA', 
                'currency' => 'UGX',
                'flag_img' => 'https://flagcdn.com/w320/ug.png',
                'background_img' => 'https://images.unsplash.com/photo-1619504644413-8a2b21e91a99?w=1200'
            ],
            [
                'name' => 'Rwanda', 
                'iso2' => 'RW', 
                'iso3' => 'RWA', 
                'currency' => 'RWF',
                'flag_img' => 'https://flagcdn.com/w320/rw.png',
                'background_img' => 'https://images.unsplash.com/photo-1606146127878-52c79046d9a0?w=1200'
            ],
            [
                'name' => 'Burundi', 
                'iso2' => 'BI', 
                'iso3' => 'BDI', 
                'currency' => 'BIF',
                'flag_img' => 'https://flagcdn.com/w320/bi.png',
                'background_img' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=1200'
            ],
            [
                'name' => 'South Africa', 
                'iso2' => 'ZA', 
                'iso3' => 'ZAF', 
                'currency' => 'ZAR',
                'flag_img' => 'https://flagcdn.com/w320/za.png',
                'background_img' => 'https://images.unsplash.com/photo-1484318571209-661cf29a69c3?w=1200'
            ],
            [
                'name' => 'Nigeria', 
                'iso2' => 'NG', 
                'iso3' => 'NGA', 
                'currency' => 'NGN',
                'flag_img' => 'https://flagcdn.com/w320/ng.png',
                'background_img' => 'https://images.unsplash.com/photo-1568722805923-ff26d501ad38?w=1200'
            ],
            [
                'name' => 'Ghana', 
                'iso2' => 'GH', 
                'iso3' => 'GHA', 
                'currency' => 'GHS',
                'flag_img' => 'https://flagcdn.com/w320/gh.png',
                'background_img' => 'https://images.unsplash.com/photo-1568471173432-3fe0c02fb23b?w=1200'
            ],
            [
                'name' => 'Egypt', 
                'iso2' => 'EG', 
                'iso3' => 'EGY', 
                'currency' => 'EGP',
                'flag_img' => 'https://flagcdn.com/w320/eg.png',
                'background_img' => 'https://images.unsplash.com/photo-1572252009286-268acec5ca0a?w=1200'
            ],
            [
                'name' => 'Morocco', 
                'iso2' => 'MA', 
                'iso3' => 'MAR', 
                'currency' => 'MAD',
                'flag_img' => 'https://flagcdn.com/w320/ma.png',
                'background_img' => 'https://images.unsplash.com/photo-1489749798305-4fea3ae63d43?w=1200'
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso2' => $country['iso2']],
                $country
            );
        }
    }
}

