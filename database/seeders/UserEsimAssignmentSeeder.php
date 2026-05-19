<?php

namespace Database\Seeders;

use App\Models\Esim;
use App\Models\User;
use App\Models\UserEsim;
use Illuminate\Database\Seeder;

class UserEsimAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'john.doe@example.com')->first();

        if (! $user) {
            $this->command?->warn('UserEsimAssignmentSeeder: john.doe@example.com not found, skipping.');

            return;
        }

        $testMsisdns = [
            '255797053059',
            '255798092059',
        ];

        foreach ($testMsisdns as $msisdn) {
            $esim = Esim::where('msisdn', $msisdn)->first();

            if (! $esim) {
                continue;
            }

            UserEsim::firstOrCreate(
                ['esim_id' => $esim->id],
                ['user_id' => $user->id]
            );

            $esim->update(['status' => 'MANAGED']);
        }
    }
}
