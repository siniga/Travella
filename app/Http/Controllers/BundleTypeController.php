<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BundleType;
use Illuminate\Http\Request;

class BundleTypeController extends Controller
{
  public function index() {
    $bundleTypes = BundleType::query()->select('id','code','name')->orderBy('code')->get();
    return response()->json(['bundle_types' => $bundleTypes]);
  }
}

