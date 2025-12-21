<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Trip;
use App\Models\Kyc;
use App\Models\User;
use App\Models\Bundle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to create orders for
        $users = User::take(5)->get();
        $bundles = Bundle::take(10)->get();

        if ($users->isEmpty() || $bundles->isEmpty()) {
            $this->command->warn('No users or bundles found. Please run other seeders first.');
            return;
        }

        // Sample order data
        $sampleOrders = [
            [
                'draft_id' => 'DRAFT-2025-001',
                'user_id' => $users[0]->id,
                'status' => 'pending_payment',
                'subtotal' => 25000.00,
                'discount_amount' => 0.00,
                'discount_code' => null,
                'total_amount' => 25000.00,
                'currency' => 'TZS',
                'source' => 'mobile_app',
                'platform' => 'react_native',
                'metadata' => [
                    'created_at' => '2025-10-21T11:30:00Z',
                    'status' => 'pending_payment'
                ],
                'kyc' => [
                    'passport_id' => 'A1234567',
                    'passport_country' => 'TZ',
                    'nationality' => 'Tanzanian',
                    'gender' => 'Female',
                    'reason_for_travel' => 'tourism'
                ],
                'trip' => [
                    'destination_country' => 'Kenya',
                    'arrival_date' => '2025-10-25',
                    'departure_date' => '2025-10-30',
                    'duration_days' => 5
                ],
                'items' => [
                    [
                        'type' => 'bundle',
                        'bundle_id' => $bundles[0]->id,
                        'bundle_name' => 'Monthly 20GB',
                        'data_amount' => 20480,
                        'validity_days' => 30,
                        'price' => 25000.00,
                        'currency' => 'TZS'
                    ]
                ]
            ],
            [
                'draft_id' => 'DRAFT-2025-002',
                'user_id' => $users[1]->id,
                'status' => 'paid',
                'subtotal' => 15000.00,
                'discount_amount' => 2000.00,
                'discount_code' => 'WELCOME20',
                'total_amount' => 13000.00,
                'currency' => 'TZS',
                'source' => 'web_app',
                'platform' => 'react_js',
                'metadata' => [
                    'created_at' => '2025-10-20T14:15:00Z',
                    'status' => 'paid',
                    'payment_method' => 'mobile_money'
                ],
                'kyc' => [
                    'passport_id' => 'B9876543',
                    'passport_country' => 'TZ',
                    'nationality' => 'Tanzanian',
                    'gender' => 'Male',
                    'reason_for_travel' => 'business'
                ],
                'trip' => [
                    'destination_country' => 'Uganda',
                    'arrival_date' => '2025-11-01',
                    'departure_date' => '2025-11-05',
                    'duration_days' => 4
                ],
                'items' => [
                    [
                        'type' => 'bundle',
                        'bundle_id' => $bundles[1]->id,
                        'bundle_name' => 'Weekly 10GB',
                        'data_amount' => 10240,
                        'validity_days' => 7,
                        'price' => 15000.00,
                        'currency' => 'TZS'
                    ]
                ]
            ],
            [
                'draft_id' => 'DRAFT-2025-003',
                'user_id' => $users[2]->id,
                'status' => 'completed',
                'subtotal' => 40000.00,
                'discount_amount' => 0.00,
                'discount_code' => null,
                'total_amount' => 40000.00,
                'currency' => 'TZS',
                'source' => 'mobile_app',
                'platform' => 'flutter',
                'metadata' => [
                    'created_at' => '2025-10-19T09:45:00Z',
                    'status' => 'completed',
                    'payment_method' => 'credit_card',
                    'processed_at' => '2025-10-19T10:00:00Z'
                ],
                'kyc' => [
                    'passport_id' => 'C5555555',
                    'passport_country' => 'KE',
                    'nationality' => 'Kenyan',
                    'gender' => 'Male',
                    'reason_for_travel' => 'tourism'
                ],
                'trip' => [
                    'destination_country' => 'Tanzania',
                    'arrival_date' => '2025-10-22',
                    'departure_date' => '2025-10-28',
                    'duration_days' => 6
                ],
                'items' => [
                    [
                        'type' => 'bundle',
                        'bundle_id' => $bundles[2]->id,
                        'bundle_name' => 'Monthly Super Combo',
                        'data_amount' => 30720,
                        'validity_days' => 30,
                        'price' => 40000.00,
                        'currency' => 'TZS'
                    ]
                ]
            ],
            [
                'draft_id' => 'DRAFT-2025-004',
                'user_id' => $users[3]->id,
                'status' => 'processing',
                'subtotal' => 8000.00,
                'discount_amount' => 1000.00,
                'discount_code' => 'FIRST10',
                'total_amount' => 7000.00,
                'currency' => 'KES',
                'source' => 'mobile_app',
                'platform' => 'react_native',
                'metadata' => [
                    'created_at' => '2025-10-18T16:20:00Z',
                    'status' => 'processing',
                    'payment_method' => 'mobile_money'
                ],
                'kyc' => [
                    'passport_id' => 'D1111111',
                    'passport_country' => 'KE',
                    'nationality' => 'Kenyan',
                    'gender' => 'Female',
                    'reason_for_travel' => 'business'
                ],
                'trip' => [
                    'destination_country' => 'Rwanda',
                    'arrival_date' => '2025-11-10',
                    'departure_date' => '2025-11-12',
                    'duration_days' => 2
                ],
                'items' => [
                    [
                        'type' => 'bundle',
                        'bundle_id' => $bundles[3]->id,
                        'bundle_name' => 'Weekly 6GB',
                        'data_amount' => 6144,
                        'validity_days' => 7,
                        'price' => 8000.00,
                        'currency' => 'KES'
                    ]
                ]
            ],
            [
                'draft_id' => 'DRAFT-2025-005',
                'user_id' => $users[4]->id,
                'status' => 'cancelled',
                'subtotal' => 12000.00,
                'discount_amount' => 0.00,
                'discount_code' => null,
                'total_amount' => 12000.00,
                'currency' => 'TZS',
                'source' => 'web_app',
                'platform' => 'vue_js',
                'metadata' => [
                    'created_at' => '2025-10-17T13:30:00Z',
                    'status' => 'cancelled',
                    'cancelled_at' => '2025-10-17T14:00:00Z',
                    'cancellation_reason' => 'customer_request'
                ],
                'kyc' => [
                    'passport_id' => 'E9999999',
                    'passport_country' => 'TZ',
                    'nationality' => 'Tanzanian',
                    'gender' => 'Other',
                    'reason_for_travel' => 'tourism'
                ],
                'trip' => [
                    'destination_country' => 'South Africa',
                    'arrival_date' => '2025-12-01',
                    'departure_date' => '2025-12-10',
                    'duration_days' => 9
                ],
                'items' => [
                    [
                        'type' => 'bundle',
                        'bundle_id' => $bundles[4]->id,
                        'bundle_name' => 'Weekly Combo',
                        'data_amount' => 5120,
                        'validity_days' => 7,
                        'price' => 12000.00,
                        'currency' => 'TZS'
                    ]
                ]
            ]
        ];

        foreach ($sampleOrders as $orderData) {
            // Create or update KYC record
            $kyc = Kyc::updateOrCreate(
                ['user_id' => $orderData['user_id']],
                [
                    'passport_id' => $orderData['kyc']['passport_id'],
                    'passport_country' => $orderData['kyc']['passport_country'],
                    'nationality' => $orderData['kyc']['nationality'],
                    'gender' => $orderData['kyc']['gender'],
                    'reason' => $orderData['kyc']['reason_for_travel'],
                    'arrival_date' => $orderData['trip']['arrival_date'],
                    'departure_date' => $orderData['trip']['departure_date'],
                ]
            );

            // Create the order
            $order = Order::updateOrCreate(
                ['draft_id' => $orderData['draft_id']],
                [
                    'user_id' => $orderData['user_id'],
                    'status' => $orderData['status'],
                    'subtotal' => $orderData['subtotal'],
                    'discount_amount' => $orderData['discount_amount'],
                    'discount_code' => $orderData['discount_code'],
                    'total_amount' => $orderData['total_amount'],
                    'currency' => $orderData['currency'],
                    'source' => $orderData['source'],
                    'platform' => $orderData['platform'],
                    'metadata' => $orderData['metadata'],
                ]
            );

            // Create trip record
            Trip::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'destination_country' => $orderData['trip']['destination_country'],
                    'arrival_date' => $orderData['trip']['arrival_date'],
                    'departure_date' => $orderData['trip']['departure_date'],
                    'duration_days' => $orderData['trip']['duration_days'],
                ]
            );

            // Create order items
            foreach ($orderData['items'] as $itemData) {
                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'bundle_id' => $itemData['bundle_id'],
                        'type' => $itemData['type']
                    ],
                    [
                        'bundle_name' => $itemData['bundle_name'],
                        'data_amount' => $itemData['data_amount'],
                        'validity_days' => $itemData['validity_days'],
                        'price' => $itemData['price'],
                        'currency' => $itemData['currency'],
                    ]
                );
            }
        }

        $this->command->info('Order seeder completed successfully!');
    }
}
