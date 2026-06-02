<?php

namespace Database\Seeders;

use App\Models\AgentLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentLocationSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'name' => 'Alice Mwangi',
                'email' => 'alice.mwangi@travela.com',
                'phone' => '+254712345001',
                'work_station' => 'Jomo Kenyatta Airport — Counter A',
                'current_location' => 'Terminal 1A, Departures',
            ],
            [
                'name' => 'John Okafor',
                'email' => 'john.okafor@travela.com',
                'phone' => '+2348034567890',
                'work_station' => 'Lagos Murtala Muhammed — Main Desk',
                'current_location' => 'International Terminal, Zone 3',
            ],
            [
                'name' => 'Fatima Hassan',
                'email' => 'fatima.hassan@travela.com',
                'phone' => '+255754321098',
                'work_station' => 'Julius Nyerere Airport — Arrivals',
                'current_location' => 'Terminal 3, Baggage Hall',
            ],
            [
                'name' => 'Samuel Bekele',
                'email' => 'samuel.bekele@travela.com',
                'phone' => '+251911223344',
                'work_station' => 'Addis Ababa Bole — eSIM Kiosk',
                'current_location' => 'Gate 12, Departures',
            ],
            [
                'name' => 'Grace Nkomo',
                'email' => 'grace.nkomo@travela.com',
                'phone' => '+27 82 555 1234',
                'work_station' => 'OR Tambo International — Travela Booth',
                'current_location' => 'Terminal B, Level 2',
            ],
        ];

        foreach ($agents as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'role' => 'agent',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            AgentLocation::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $row['phone'],
                    'work_station' => $row['work_station'],
                    'current_location' => $row['current_location'],
                    'current_location_updated_at' => now(),
                ]
            );
        }
    }
}
