<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StorePreorderDraftRequest;
use App\Http\Requests\StoreFinalizeOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Counter;
use App\Models\Trip;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{
    
    public function storeDraft(StorePreorderDraftRequest $request): JsonResponse
    {
    try {
        DB::beginTransaction();

        $v = $request->validated();

        // Generate a draft id (choose one of the two styles below)
        $draftId = $this->nextDraftId();               // DRAFT-2025-001 style
        // $draftId = 'DRAFT-'.Str::uuid()->toString(); // UUID fallback if you prefer

        // Create a minimal order row (NO user_id, NO KYC yet)
        $order = \App\Models\Order::create([
            'draft_id'        => $draftId,
            'user_id'         => null, // make sure column is nullable in DB
            'status'          => 'draft',

            // flat pricing columns from your table
            'subtotal'        => $v['pricing']['subtotal'],
            'discount_amount' => $v['pricing']['discount_amount'],
            'discount_code'   => $v['pricing']['discount_code'] ?? null,
            'total_amount'    => $v['pricing']['total_amount'],
            'currency'        => $v['pricing']['currency'],

            // app/source columns in your table
            'source'          => $v['order_metadata']['source'],
            'platform'        => $v['order_metadata']['platform'],

            // keep audit/meta
            'metadata'        => json_encode([
                'created_at' => $v['order_metadata']['created_at'],
                'status'     => 'draft',
            ], JSON_UNESCAPED_SLASHES),
        ]);

        // Trip + items are allowed at draft time
        $this->createTrip($order, $v['trip']);
        $this->createOrderItems($order, $v['items']);

        DB::commit();

        $order->load(['trip', 'orderItems']); // no user/kyc yet

        return response()->json([
            'success' => true,
            'message' => 'Preorder draft created',
            'data' => [
                'draft_id' => $order->draft_id,
                'status'   => $order->status,          // 'draft'
                'trip'     => $order->trip,
                'items'    => $order->orderItems,
                'pricing'  => [
                    'subtotal'        => (float) $v['pricing']['subtotal'],
                    'discount_amount' => (float) $v['pricing']['discount_amount'],
                    'discount_code'   => $v['pricing']['discount_code'],
                    'total_amount'    => (float) $v['pricing']['total_amount'],
                    'currency'        => $v['pricing']['currency'],
                ],
                'order_metadata' => [
                    'source'     => $v['order_metadata']['source'],
                    'platform'   => $v['order_metadata']['platform'],
                    'created_at' => $v['order_metadata']['created_at'],
                    'status'     => 'draft',
                ],
            ],
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to create preorder draft',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    public function finalizeOrder(StoreFinalizeOrderRequest $request): JsonResponse
    {
        $v = $request->validated();
    
        try {
            DB::beginTransaction();
    
            // 1) Lock the draft
            $order = \App\Models\Order::where('draft_id', $v['draft_id'])
                ->lockForUpdate()
                ->firstOrFail();
    
            if ($order->status !== 'draft') {
                DB::commit();
                $order->load(['trip','orderItems','user','kyc']);
                return response()->json([
                    'success' => true,
                    'message' => 'Order already finalized',
                    'data' => [
                        'order'        => $order,
                        'draft_id'     => $order->draft_id,
                        'status'       => $order->status,
                        'total_amount' => $order->total_amount,
                        'currency'     => $order->currency,
                    ],
                ]);
            }
    
            // 2) Ensure draft has trip + items (created at draft time)
            $order->load(['trip','orderItems']);
            if (!$order->trip) {
                throw new \RuntimeException('Draft is missing trip information.');
            }
            if ($order->orderItems->isEmpty()) {
                throw new \RuntimeException('Draft has no items.');
            }
    
            // 3) Build a "validated-like" payload for your existing helpers
            $validatedForHelpers = [
                'user_id' => $v['user_id'],
                'kyc'     => $v['kyc'], // your StoreFinalizeOrderRequest enforces this
    
                // pull TRIP from the draft
                'trip' => [
                    'destination_country' => $order->trip->destination_country,
                    'arrival_date'        => optional($order->trip->arrival_date)->toDateString() ?? $order->trip->arrival_date,
                    'departure_date'      => optional($order->trip->departure_date)->toDateString() ?? $order->trip->departure_date,
                    'duration_days'       => (int) $order->trip->duration_days,
                ],
    
                // pull ITEMS from the draft (map to the structure your helpers expect)
                'items' => $order->orderItems->map(function ($it) {
                    return [
                        'type'          => $it->type,
                        'bundle_id'     => $it->bundle_id,
                        'bundle_name'   => $it->bundle_name,
                        'data_amount'   => $it->data_amount,
                        'validity_days' => $it->validity_days,
                        'price'         => (float) $it->price,
                        'currency'      => $it->currency,
                    ];
                })->values()->all(),
    
                // pricing from flat columns on orders table
                'pricing' => [
                    'subtotal'        => (float) $order->subtotal,
                    'discount_amount' => (float) $order->discount_amount,
                    'discount_code'   => $order->discount_code,
                    'total_amount'    => (float) $order->total_amount,
                    'currency'        => $order->currency,
                ],
    
                // metadata from your flat cols + JSON metadata
                'order_metadata' => [
                    'source'     => $order->source,
                    'platform'   => $order->platform,
                    'created_at' => optional(json_decode($order->metadata, true))['created_at'] ?? now()->toIso8601String(),
                    'status'     => 'pending_payment',
                ],
            ];
    
            // 4) Run your existing KYC helper (expects $validated['kyc'])
            $kyc = $this->createOrUpdateKyc($validatedForHelpers);
    
            // 5) Promote draft → actual order on the same row (do NOT recreate trip/items)
            $newStatus = ($request->input('payment.status') === 'paid') ? 'paid' : 'pending_payment';
    
            // augment metadata safely
            $meta = json_decode($order->metadata ?? '{}', true);
            $meta['finalized_at'] = now()->toIso8601String();
            $meta['status']       = $newStatus;
            if ($request->filled('payment')) {
                $meta['payment'] = array_filter([
                    'status'    => $request->input('payment.status'),
                    'reference' => $request->input('payment.reference'),
                    'method'    => $request->input('payment.method'),
                    'paid_at'   => $request->input('payment.paid_at'),
                ]);
            }
    
            $order->update([
                'user_id'  => $v['user_id'],
                'status'   => $newStatus,
                'metadata' => json_encode($meta, JSON_UNESCAPED_SLASHES),
                // if you persist kyc_id on orders:
                // 'kyc_id' => $kyc->id,
            ]);
    
            DB::commit();
    
            // 6) Match your store() response shape
            $order->load(['trip', 'orderItems', 'user', 'kyc']);
    
            return response()->json([
                'success' => true,
                'message' => 'Order finalized successfully',
                'data' => [
                    'order'        => $order,
                    'draft_id'     => $order->draft_id,
                    'status'       => $order->status,
                    'total_amount' => $order->total_amount,
                    'currency'     => $order->currency,
                ]
            ], 200);
    
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize order',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the sp
     * ecified order.
     */
    public function show(string $draftId): JsonResponse
    {
        $order = Order::with(['trip', 'orderItems.bundle', 'user', 'kyc'])
            ->where('draft_id', $draftId)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(StoreOrderRequest $request, string $draftId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = Order::where('draft_id', $draftId)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $validated = $request->validated();

            // Update KYC record
            $this->createOrUpdateKyc($validated);

            // Update the order
            $this->updateOrder($order, $validated);

            // Update trip record
            $this->updateTrip($order, $validated['trip']);

            // Update order items (delete old ones and create new ones)
            $order->orderItems()->delete();
            $this->createOrderItems($order, $validated['items']);

            DB::commit();

            // Load relationships for response
            $order->load(['trip', 'orderItems', 'user', 'kyc']);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(string $draftId): JsonResponse
    {
        try {
            $order = Order::where('draft_id', $draftId)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update KYC record
     */
    private function createOrUpdateKyc(array $validated): Kyc
    {
        $kycData = $validated['kyc'];
        
        $kyc = Kyc::updateOrCreate(
            ['user_id' => $validated['user_id']],
            [
                'passport_id' => $kycData['passport_id'],
                'passport_country' => $kycData['passport_country'],
                'nationality' => $kycData['nationality'],
                'gender' => $kycData['gender'],
                'reason' => $kycData['reason_for_travel'],
                'arrival_date' => $validated['trip']['arrival_date'],
                'departure_date' => $validated['trip']['departure_date'],
            ]
        );

        return $kyc;
    }

    /**
     * Create order record
     */
    private function createOrder(array $validated): Order
    {
        $pricing = $validated['pricing'];
        $metadata = $validated['order_metadata'] ?? [];

        return Order::create([
            'draft_id' => $validated['draft_id'],
            'user_id' => $validated['user_id'],
            'status' => $metadata['status'] ?? 'pending_payment',
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'discount_code' => $pricing['discount_code'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
            'source' => $metadata['source'] ?? null,
            'platform' => $metadata['platform'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Update order record
     */
    private function updateOrder(Order $order, array $validated): void
    {
        $pricing = $validated['pricing'];
        $metadata = $validated['order_metadata'] ?? [];

        $order->update([
            'status' => $metadata['status'] ?? $order->status,
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'discount_code' => $pricing['discount_code'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
            'source' => $metadata['source'] ?? $order->source,
            'platform' => $metadata['platform'] ?? $order->platform,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create trip record
     */
    private function createTrip(Order $order, array $tripData): Trip
    {
        return Trip::create([
            'order_id' => $order->id,
            'destination_country' => $tripData['destination_country'],
            'arrival_date' => $tripData['arrival_date'],
            'departure_date' => $tripData['departure_date'],
            'duration_days' => $tripData['duration_days'],
        ]);
    }

    /**
     * Update trip record
     */
    private function updateTrip(Order $order, array $tripData): void
    {
        $trip = $order->trip;
        
        if ($trip) {
            $trip->update([
                'destination_country' => $tripData['destination_country'],
                'arrival_date' => $tripData['arrival_date'],
                'departure_date' => $tripData['departure_date'],
                'duration_days' => $tripData['duration_days'],
            ]);
        } else {
            $this->createTrip($order, $tripData);
        }
    }

    /**
     * Create order items
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'type' => $item['type'],
                'bundle_id' => $item['bundle_id'] ?? null,
                'bundle_name' => $item['bundle_name'],
                'data_amount' => $item['data_amount'] ?? null,
                'validity_days' => $item['validity_days'] ?? null,
                'price' => $item['price'],
                'currency' => $item['currency'],
            ]);
        }
    }
    
    private function nextDraftId(): string
    {
        $year = now()->year;
    
        // Read last draft for this year and increment
        $last = \App\Models\Order::whereYear('created_at', $year)
            ->where('draft_id', 'LIKE', "DRAFT-$year-%")
            ->orderByDesc('id')
            ->value('draft_id'); // e.g. DRAFT-2025-007
    
        $n = 0;
        if ($last && preg_match('/^DRAFT-'.$year.'-(\d{3})$/', $last, $m)) {
            $n = (int) $m[1];
        }
        return sprintf('DRAFT-%d-%03d', $year, $n + 1);
    }

    
}

