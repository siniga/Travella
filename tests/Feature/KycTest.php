<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Crypt;

class KycTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a verified user for testing
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        
        // Generate token for authenticated requests
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_create_kyc(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'passport_id',
                     'passport_country',
                     'arrival_date',
                     'departure_date',
                     'reason',
                     'created_at',
                     'updated_at'
                 ]);

        $this->assertDatabaseHas('kycs', [
            'user_id' => $this->user->id,
            'passport_country' => 'US',
            'reason' => 'Tourism'
        ]);
    }

    public function test_authenticated_user_can_retrieve_kyc(): void
    {
        // Create a KYC record first
        $kyc = Kyc::create([
            'user_id' => $this->user->id,
            'passport_id_encrypted' => Crypt::encryptString('A1234567'),
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1),
            'departure_date' => now()->addDays(7),
            'reason' => 'Tourism'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/kyc');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $kyc->id,
                     'passport_id' => 'A1234567',
                     'passport_country' => 'US',
                     'reason' => 'Tourism'
                 ]);
    }

    public function test_authenticated_user_gets_404_when_no_kyc_exists(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/kyc');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'KYC not found']);
    }

    public function test_authenticated_user_can_update_kyc(): void
    {
        // Create initial KYC
        $kyc = Kyc::create([
            'user_id' => $this->user->id,
            'passport_id_encrypted' => Crypt::encryptString('A1234567'),
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1),
            'departure_date' => now()->addDays(7),
            'reason' => 'Tourism'
        ]);

        $updatedData = [
            'passport_id' => 'B7654321',
            'passport_country' => 'CA',
            'arrival_date' => now()->addDays(2)->format('Y-m-d'),
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Business'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $updatedData);

        $response->assertStatus(201)
                 ->assertJson([
                     'passport_id' => 'B7654321',
                     'passport_country' => 'CA',
                     'reason' => 'Business'
                 ]);

        $this->assertDatabaseHas('kycs', [
            'user_id' => $this->user->id,
            'passport_country' => 'CA',
            'reason' => 'Business'
        ]);
    }

    public function test_authenticated_user_can_delete_kyc(): void
    {
        // Create a KYC record first
        $kyc = Kyc::create([
            'user_id' => $this->user->id,
            'passport_id_encrypted' => Crypt::encryptString('A1234567'),
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1),
            'departure_date' => now()->addDays(7),
            'reason' => 'Tourism'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->deleteJson('/api/kyc');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'KYC deleted']);

        $this->assertDatabaseMissing('kycs', ['id' => $kyc->id]);
    }

    public function test_kyc_validation_requires_passport_id(): void
    {
        $kycData = [
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['passport_id']);
    }

    public function test_kyc_validation_requires_arrival_date(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['arrival_date']);
    }

    public function test_kyc_validation_requires_departure_date(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['departure_date']);
    }

    public function test_kyc_validation_departure_date_must_be_after_arrival_date(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(7)->format('Y-m-d'),
            'departure_date' => now()->addDays(1)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['departure_date']);
    }

    public function test_kyc_validation_arrival_date_must_be_today_or_later(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->subDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['arrival_date']);
    }

    public function test_kyc_validation_passport_id_format(): void
    {
        $kycData = [
            'passport_id' => 'invalid@passport#id',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['passport_id']);
    }

    public function test_kyc_validation_passport_country_size(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'USA', // Should be 2 characters
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['passport_country']);
    }

    public function test_kyc_validation_reason_max_length(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => str_repeat('a', 101) // Exceeds 100 character limit
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['reason']);
    }

    public function test_kyc_passport_id_encryption(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(201);

        // Check that passport_id is encrypted in database
        $kyc = Kyc::where('user_id', $this->user->id)->first();
        $this->assertNotEquals('A1234567', $kyc->passport_id_encrypted);
        $this->assertNotNull($kyc->passport_id_encrypted);

        // Check that we can decrypt it
        $this->assertEquals('A1234567', $kyc->passport_id);
    }

    public function test_kyc_passport_id_normalization(): void
    {
        $kycData = [
            'passport_id' => '  a1234567  ', // Lowercase with spaces
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/kyc', $kycData);

        $response->assertStatus(201)
                 ->assertJson(['passport_id' => 'A1234567']); // Should be uppercase and trimmed
    }

    public function test_unauthenticated_user_cannot_access_kyc(): void
    {
        $response = $this->getJson('/api/kyc');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_create_kyc(): void
    {
        $kycData = [
            'passport_id' => 'A1234567',
            'passport_country' => 'US',
            'arrival_date' => now()->addDays(1)->format('Y-m-d'),
            'departure_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Tourism'
        ];

        $response = $this->postJson('/api/kyc', $kycData);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_kyc(): void
    {
        $response = $this->deleteJson('/api/kyc');
        $response->assertStatus(401);
    }
}
