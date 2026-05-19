<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $q = Bundle::query();

        // Hide bundles not meant for storefront listing.
        // $q->where(function ($w) {
        //     $w->whereNull('alias')->orWhere('alias', '!=', 'Nomad');
        // });
        // $q->where('bundle_size', '!=', 15);

        // if ($request->filled('active')) {
        //     $q->where('active', $request->boolean('active'));
        // }

        $bundles = $q->orderBy('price_usd')->get()->map(function (Bundle $bundle) {
            $data = $bundle->toArray();
            $data['price'] = $data['price_usd'];
            unset($data['price_usd']);

            return $data;
        });

        return response()->json([
            'bundles' => $bundles,
        ]);
    }
}

