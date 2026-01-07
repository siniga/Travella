<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\BundleTypeController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\OrderController;  
use App\Services\SelcomService;


Http::get('https://google.com')->successful();


Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/forgot-password', [PasswordResetController::class, 'sendLink']);
  Route::post('/reset-password', [PasswordResetController::class, 'reset']);
  Route::post('/verify-email', [VerifyEmailController::class, 'verify']);
  Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
    ->middleware('auth:sanctum');
});

Route::prefix('public')->group(function () {
//kyc
  Route::get('/kyc', [KycController::class, 'show']);     // get my KYC
  Route::post('/kyc', [KycController::class, 'store']);   // create/update
  Route::delete('/kyc', [KycController::class, 'destroy']);


  Route::get('/countries', [CountryController::class, 'index']);
  Route::get('/countries/{iso2}/providers', [CountryController::class, 'providers']);

  Route::get('/bundle-types', [BundleTypeController::class, 'index']);
  Route::get('/providers/{provider}/bundles', [ProviderController::class, 'bundles']); // ?country=TZ&type=DATA&active=1
  
  //orders
 Route::post('/preorders/drafts', [OrderController::class, 'storeDraft']);  
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('/me', [AuthController::class, 'me']);
  Route::post('/logout', [AuthController::class, 'logout']);

  // Order routes
  // routes/api.php
  Route::post('/orders/finalize', [OrderController::class, 'finalizeOrder']);

  Route::get('/orders/{draft_id}', [OrderController::class, 'show']);
  Route::put('/orders/{draft_id}', [OrderController::class, 'update']);
  Route::delete('/orders/{draft_id}', [OrderController::class, 'destroy']);
});

// Http::withoutVerifying()->get('selcom-api-url');
Route::get('/selcom-test', function (SelcomService $selcom) {
    try {
        // 1. Prepare dummy order data required by Selcom
        $data = [
            'vendor' => 'VENDORTEXT', // Replace with your actual vendor ID if needed
            'order_id' => uniqid(),
            'buyer_email' => 'test@example.com',
            'buyer_name'  => 'Test User',
            'buyer_phone' => '255700000000',
            'amount'      => 1000,
            'currency'    => 'TZS',
            'no_of_items' => 1,
        ];

        // 2. Call the createOrder method
        $response = $selcom->createOrder($data);

        // 3. Return detailed debug info
        return [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
            'raw_body' => $response->body(),
            'headers' => $response->headers()
        ];
    } catch (\Exception $e) {
        return [
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});