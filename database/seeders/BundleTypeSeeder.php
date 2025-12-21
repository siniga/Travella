<?php

namespace Database\Seeders;

use App\Models\BundleType;
use Illuminate\Database\Seeder;

class BundleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bundleTypes = [
            ['code' => 'DATA', 'name' => 'Data only'],
            ['code' => 'VOICE', 'name' => 'Voice only'],
            ['code' => 'SMS', 'name' => 'SMS only'],
            ['code' => 'COMBO', 'name' => 'Data + Voice + SMS'],
            ['code' => 'DATA_VOICE', 'name' => 'Data + Voice'],
            ['code' => 'DATA_SMS', 'name' => 'Data + SMS'],
        ];

        foreach ($bundleTypes as $type) {
            BundleType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}

