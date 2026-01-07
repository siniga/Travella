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
            if (isset($data[$field])) {
                $signatureString .= "&$field=" . $data[$field];
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
            return isset($data[$field]);
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
        // Adjust this list based on exactly what fields you send in $data
        $signedFields = [
            'vendor', 
            'order_id', 
            'buyer_email', 
            'buyer_name', 
            'buyer_phone', 
            'amount', 
            'currency', 
            'payment_methods',
            'no_of_items'
        ];

        return $this->post('/checkout/create-order', $data, $signedFields);
    }
}


