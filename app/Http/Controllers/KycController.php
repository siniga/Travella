<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\KycUpsertRequest;
use Illuminate\Http\Request;

class KycController extends Controller
{
  public function show(Request $r) {
    $kyc = $r->user()->kyc;
    return $kyc
      ? response()->json(['kyc' => $kyc])
      : response()->json(['message'=>'KYC not found'], 404);
  }

  public function store(KycUpsertRequest $req) {
    $user = $req->user();

    $data = $req->validated();

    // Optional fingerprint for audits/search (do NOT expose)
    $data['passport_hash'] = hash('sha256', $data['passport_id']);

    // Store the passport_id temporarily
    $passportId = $data['passport_id'];
    unset($data['passport_id']); // Remove from data array since it's not a direct column

    // Create or update the KYC record
    $kyc = $user->kyc()->updateOrCreate([], $data);

    // Set the encrypted passport_id via accessor and save
    $kyc->passport_id = $passportId;
    $kyc->save();

    return response()->json(['kyc' => $kyc->fresh()], 201);
  }

  public function destroy(Request $r) {
    optional($r->user()->kyc)->delete();
    return response()->json(['message' => 'KYC deleted']);
  }
}
