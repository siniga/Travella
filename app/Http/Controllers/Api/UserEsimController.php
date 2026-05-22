<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeEsimRechargeRequest;
use App\Models\Esim;
use App\Models\UserEsim;
use App\Services\VodacomSimManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserEsimController extends Controller
{
    public function __construct(private readonly VodacomSimManagerService $vodacom)
    {
    }

    public function index(Request $request)
    {
        $esims = $request->user()
            ->esims()
            ->with('esim')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $esims,
        ]);
    }

    /**
     * Assign the authenticated user a SIM from inventory if they do not already have one.
     */
    public function register(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $existing = UserEsim::query()
            ->where('user_id', $userId)
            ->whereHas('esim', fn ($q) => $q->whereNotNull('msisdn')->where('msisdn', '!=', ''))
            ->with('esim')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'SIM already assigned',
                'data' => $existing,
            ]);
        }

        try {
            $result = DB::transaction(function () use ($userId) {
                $existing = UserEsim::query()
                    ->where('user_id', $userId)
                    ->whereHas('esim', fn ($q) => $q->whereNotNull('msisdn')->where('msisdn', '!=', ''))
                    ->with('esim')
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return ['assignment' => $existing, 'created' => false];
                }

                $esim = Esim::query()
                    ->whereNotNull('msisdn')
                    ->where('msisdn', '!=', '')
                    ->whereNotIn('id', UserEsim::query()->select('esim_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (! $esim || UserEsim::where('esim_id', $esim->id)->exists()) {
                    return ['assignment' => null, 'created' => false];
                }

                $assignment = UserEsim::create([
                    'user_id' => $userId,
                    'esim_id' => $esim->id,
                ]);

                $esim->update(['status' => 'MANAGED']);

                return ['assignment' => $assignment->load('esim'), 'created' => true];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign SIM',
                'error' => $e->getMessage(),
            ], 500);
        }

        if (! $result['assignment']) {
            return response()->json([
                'success' => false,
                'message' => 'No unassigned SIMs available in inventory',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['created'] ? 'SIM assigned successfully' : 'SIM already assigned',
            'data' => $result['assignment'],
        ], $result['created'] ? 201 : 200);
    }

    public function recharges(Request $request)
    {
        $data = $request->validate([
            'msisdn' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $esim = $this->requireOwnedEsim($request, $data['msisdn']);

        $query = array_filter($request->only(['msisdn', 'start_date', 'end_date', 'page', 'page_size']), fn ($v) => $v !== null && $v !== '');
        return $this->proxy($this->vodacom->get('/api/recharge', $query));
    }

    public function usage(Request $request)
    {
        $data = $request->validate([
            'msisdn' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $esim = $this->requireOwnedEsim($request, $data['msisdn']);

        $query = array_filter($request->only(['msisdn', 'start_date', 'end_date', 'page', 'page_size']), fn ($v) => $v !== null && $v !== '');
        return $this->proxy($this->vodacom->get('/api/usage', $query));
    }

    public function usageDetails(Request $request)
    {
        $data = $request->validate([
            'msisdn' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $esim = $this->requireOwnedEsim($request, $data['msisdn']);

        $query = array_filter($request->only(['msisdn', 'start_date', 'end_date', 'page', 'page_size']), fn ($v) => $v !== null && $v !== '');
        return $this->proxy($this->vodacom->get('/api/usage-details', $query));
    }

    public function recharge(MeEsimRechargeRequest $request)
    {
        $data = $request->validated();

        $esim = $this->requireOwnedEsim($request, $data['msisdn']);

        if (! is_null($esim->esim?->network_id) && (int) $esim->esim->network_id !== (int) $data['network_id']) {
            return response()->json(['message' => 'You do not have access to this eSIM.'], 403);
        }

        $payload = array_filter($request->only(['airtime_amount', 'msisdn', 'network_id', 'reference', 'product_id']), fn ($v) => $v !== null && $v !== '');
        return $this->proxy($this->vodacom->post('/api/recharge', [], $payload));
    }

    private function requireOwnedEsim(Request $request, string $msisdn): UserEsim
    {
        $esim = $request->user()
            ->esims()
            ->whereHas('esim', fn ($q) => $q->where('msisdn', $msisdn))
            ->with('esim')
            ->first();

        if (! $esim) {
            abort(response()->json(['message' => 'You do not have access to this eSIM.'], 403));
        }

        return $esim;
    }

    private function proxy($vodacomResponse)
    {
        $contentType = $vodacomResponse->header('Content-Type', 'application/json');
        $body = $vodacomResponse->body();

        return response($body, $vodacomResponse->status())
            ->header('Content-Type', $contentType ?: 'application/json');
    }
}

