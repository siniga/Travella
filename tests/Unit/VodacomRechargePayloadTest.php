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

    public function test_generate_reference_for_order_and_item(): void
    {
        config(['services.vodacom_sim.recharge_reference_prefix' => 'RECHARGE']);

        $this->assertSame('RECHARGE153335', VodacomRechargePayload::generateReference(15, 3335));
        $this->assertSame('RECHARGE780078', VodacomRechargePayload::generateReference(78, 78));
    }

    public function test_legacy_rch_reference_is_replaced_with_recharge_prefix(): void
    {
        config(['services.vodacom_sim.recharge_reference_prefix' => 'RECHARGE']);

        $reference = VodacomRechargePayload::formatReference('RCH-20260522-78-78');

        $this->assertStringStartsWith('RECHARGE', $reference);
        $this->assertStringNotContainsString('RCH-', $reference);
    }
}
