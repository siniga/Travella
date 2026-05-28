<?php

namespace Tests\Unit;

use App\Services\VodacomRechargePayload;
use Tests\TestCase;

class VodacomRechargePayloadTest extends TestCase
{
    public function test_normalize_matches_vodacom_documented_shape(): void
    {
        config(['services.vodacom_sim.recharge_reference_prefix' => 'RECHARGE']);

        $payload = VodacomRechargePayload::normalize([
            'airtime_amount' => '100.25',
            'msisdn' => '25583479408',
            'network_id' => 1,
            'product_id' => 66,
            'reference' => 'RECHARGE123',
        ]);

        $this->assertSame([
            'airtime_amount' => '100.25',
            'msisdn' => '+25583479408',
            'network_id' => 1,
            'product_id' => 66,
            'reference' => 'RECHARGE123',
        ], $payload);
    }

    public function test_normalize_formats_integer_airtime_as_decimal_string(): void
    {
        $payload = VodacomRechargePayload::normalize([
            'msisdn' => '+255768632087',
            'network_id' => 1,
            'product_id' => 66,
            'reference' => 'RECHARGE153335',
            'airtime_amount' => 500,
        ]);

        $this->assertSame('+255768632087', $payload['msisdn']);
        $this->assertSame('500.00', $payload['airtime_amount']);
        $this->assertSame('RECHARGE153335', $payload['reference']);
    }

    public function test_generate_reference_is_stable_for_same_order_item(): void
    {
        config(['services.vodacom_sim.recharge_reference_prefix' => 'RECHARGE']);

        $first = VodacomRechargePayload::generateReference(15, 3335);
        $second = VodacomRechargePayload::generateReference(15, 3335);

        $this->assertStringStartsWith('RECHARGE', $first);
        $this->assertSame($first, $second);
    }

    public function test_generate_reference_changes_when_retry_seed_differs(): void
    {
        config(['services.vodacom_sim.recharge_reference_prefix' => 'RECHARGE']);

        $first = VodacomRechargePayload::generateReference(78, 78, '78:78');
        $retry = VodacomRechargePayload::generateReference(78, 78, '78:78:retry');

        $this->assertStringStartsWith('RECHARGE', $first);
        $this->assertNotSame($first, $retry);
    }
}
