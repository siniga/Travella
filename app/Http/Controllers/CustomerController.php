<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * List all users with the customer role (`user`).
     */
    public function index(): JsonResponse
    {
        $customers = User::query()
            ->where('role', 'user')
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at']);

        return response()->json([
            'success' => true,
            'total' => $customers->count(),
            'data' => $customers,
        ]);
    }
}
