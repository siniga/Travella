<?php

namespace Database\Seeders;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class KycSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users
        $users = User::limit(3)->get();

        if ($users->count() > 0) {
            foreach ($users as $index => $user) {
                // Check if KYC already exists for this user
                if (!Kyc::where('user_id', $user->id)->exists()) {
                    $passportId = 'P' . str_pad((1000 + $index), 7, '0', STR_PAD_LEFT);
                    
                    Kyc::create([
                        'user_id' => $user->id,
                        'passport_id_encrypted' => Crypt::encryptString($passportId),
                        'passport_country' => $index === 0 ? 'TZ' : ($index === 1 ? 'KE' : 'UG'),
                        'arrival_date' => now()->addDays(rand(1, 30)),
                        'departure_date' => now()->addDays(rand(31, 90)),
                        'reason' => ['tourism', 'business', 'education'][rand(0, 2)],
                        'passport_hash' => hash('sha256', $passportId),
                        'verified_at' => rand(0, 1) ? now() : null,
                    ]);
                }
            }
        }

        // Create additional KYC records for any users without them (up to 10 total)
        $usersWithoutKyc = User::whereDoesntHave('kyc')->limit(7)->get();
        
        foreach ($usersWithoutKyc as $index => $user) {
            $passportId = 'P' . str_pad((2000 + $index), 7, '0', STR_PAD_LEFT);
            
            Kyc::create([
                'user_id' => $user->id,
                'passport_id_encrypted' => Crypt::encryptString($passportId),
                'passport_country' => ['TZ', 'KE', 'UG', 'RW', 'ZA'][rand(0, 4)],
                'arrival_date' => now()->addDays(rand(1, 60)),
                'departure_date' => now()->addDays(rand(61, 180)),
                'reason' => ['tourism', 'business', 'education', 'family visit'][rand(0, 3)],
                'passport_hash' => hash('sha256', $passportId),
                'verified_at' => rand(0, 1) ? now() : null,
            ]);
        }
    }
}

