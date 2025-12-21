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
