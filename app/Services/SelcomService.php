<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SelcomService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey    = config('services.selcom.key');
        $this->apiSecret = config('services.selcom.secret');
        $this->baseUrl   = config('services.selcom.url');
    }

    protected function computeDigest(string $timestamp, array $signedFields, array $data): string
    {
        $signatureString = "timestamp=$timestamp";

        foreach ($signedFields as $field) {
            // Use data_get to retrieve nested values (e.g. 'billing.firstname')
            $value = data_get($data, $field);
            
            if ($value !== null) {
                $signatureString .= "&$field=" . $value;
            }
        }

        return base64_encode(
            hash_hmac('sha256', $signatureString, $this->apiSecret, true)
        );
    }

    public function headers(array $signedFields, array $data): array
    {
        // Filter out fields that are not present in the data to avoid mismatch
        $signedFields = array_filter($signedFields, function ($field) use ($data) {
            return data_get($data, $field) !== null;
        });

        // Re-index array to ensure clean implode
        $signedFields = array_values($signedFields);

        $timestamp = gmdate('Y-m-d\TH:i:sP');

        $digest = $this->computeDigest($timestamp, $signedFields, $data);

        return [
            'Authorization'  => 'SELCOM ' . base64_encode($this->apiKey),
            'Digest-Method'  => 'HS256',
            'Digest'         => $digest,
            'Timestamp'      => $timestamp,
            'Signed-Fields'  => implode(',', $signedFields),
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ];
    }

    public function post(string $endpoint, array $data, array $signedFields)
    {
        return Http::withHeaders(
            $this->headers($signedFields, $data)
        )->post($this->baseUrl . $endpoint, $data);
    }

    public function createOrder(array $data)
    {
        // Typical fields required for signing a create-order request
        $signedFields = [
            'vendor', 
            'order_id', 
            'buyer_email', 
            'buyer_name', 
            'buyer_phone', 
            'amount', 
            'currency', 
            'payment_methods',
            'redirect_url',
            'cancel_url',
            'no_of_items',
            'billing.firstname',
            'billing.lastname',
            'billing.address_1',
            'billing.city',
            'billing.state_or_region',
            'billing.postcode_or_pobox',
            'billing.country',
            'billing.phone',
            'buyer_remarks',
            'merchant_remarks'
        ];

        return $this->post('/checkout/create-order', $data, $signedFields);
    }
}


