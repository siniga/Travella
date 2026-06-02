<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentLocationRequest;
use App\Models\AgentLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AgentController extends Controller
{
    /**
     * List all agents with linked user account details.
     */
    public function index(): JsonResponse
    {
        $agents = AgentLocation::query()
            ->with([
                'user:id,name,email,role,email_verified_at,created_at,updated_at',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $agents->count(),
            'data' => $agents,
        ]);
    }

    /**
     * Create an agent profile (and user account when user_id is omitted).
     */
    public function store(StoreAgentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $agent = DB::transaction(function () use ($data) {
            if (! empty($data['user_id'])) {
                $user = User::query()->lockForUpdate()->findOrFail($data['user_id']);

                if ($user->agentLocation()->exists()) {
                    throw ValidationException::withMessages([
                        'user_id' => ['This user already has an agent profile.'],
                    ]);
                }

                if ($user->role !== 'agent') {
                    $user->update(['role' => 'agent']);
                }
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => 'agent',
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);
            }

            return AgentLocation::create([
                'user_id' => $user->id,
                'phone' => $data['phone'],
                'work_station' => $data['work_station'],
                'current_location' => $data['current_location'] ?? null,
                'current_location_updated_at' => isset($data['current_location'])
                    ? now()
                    : null,
            ]);
        });

        $agent->load([
            'user:id,name,email,role,email_verified_at,created_at,updated_at',
        ]);

        $user = $agent->user;

        return response()->json([
            'success' => true,
            'message' => 'Agent created successfully.',
            'data' => [
                'id' => $agent->id,
                'user_id' => $agent->user_id,
                'phone' => $agent->phone,
                'work_station' => $agent->work_station,
                'current_location' => $agent->current_location,
                'current_location_updated_at' => $agent->current_location_updated_at,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                ] : null,
            ],
        ], 201);
    }

    /**
     * Agent updates their current location when moved (work_station unchanged).
     */
    public function updateMyLocation(UpdateAgentLocationRequest $request): JsonResponse
    {
        $profile = $request->user()->agentLocation;

        if (! $profile) {
            return response()->json([
                'message' => 'Agent profile not found.',
            ], 404);
        }

        $profile->update([
            'current_location' => $request->validated('current_location'),
            'current_location_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
            'agent_location' => $profile->fresh()->only([
                'id',
                'phone',
                'work_station',
                'current_location',
                'current_location_updated_at',
            ]),
        ]);
    }
}
