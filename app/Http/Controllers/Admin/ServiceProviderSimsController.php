<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Esim;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceProviderSimsController extends Controller
{
    /**
     * Service-provider SIM inventory overview + table.
     *
     * Query:
     * - tab: all|inventory|issued|suspended
     * - sim_type: all|physical|esim
     * - search: matches msisdn/iccid/imsi/description/customer
     * - page/page_size: paginate table (default 1/50, max 200)
     */
    public function index(Provider $provider, Request $request): JsonResponse
    {
        $tab = strtolower((string) $request->query('tab', 'all'));
        $simType = strtolower((string) $request->query('sim_type', 'all'));
        $search = trim((string) $request->query('search', ''));

        $page = max((int) $request->query('page', 1), 1);
        $pageSize = min(max((int) $request->query('page_size', 50), 1), 200);

        // Temporary mapping: provider -> network_id for SIM inventory.
        // You can later move this into providers.metadata (e.g. {"network_id": 1}).
        $networkId = $this->providerNetworkId($provider);

        // Base rows: local inventory in `esims`, joined to assignment + user.
        $base = DB::table('esims')
            ->leftJoin('user_esims', 'user_esims.esim_id', '=', 'esims.id')
            ->leftJoin('users', 'users.id', '=', 'user_esims.user_id')
            ->where('esims.network_id', '=', $networkId);

        if ($simType === Esim::SIM_TYPE_PHYSICAL || $simType === Esim::SIM_TYPE_ESIM) {
            $base->where('esims.sim_type', '=', $simType);
        }

        if ($tab === 'inventory') {
            $base->whereNull('user_esims.id');
        } elseif ($tab === 'issued') {
            $base->whereNotNull('user_esims.id');
        } elseif ($tab === 'suspended') {
            $base->where('esims.provider_status', '=', Esim::PROVIDER_STATUS_SUSPENDED);
        } else {
            // all / inventory / issued should hide suspended unless explicitly requested
            $base->where('esims.provider_status', '=', Esim::PROVIDER_STATUS_ACTIVE);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $base->where(function ($q) use ($like) {
                $q->where('esims.msisdn', 'like', $like)
                    ->orWhere('esims.iccid', 'like', $like)
                    ->orWhere('esims.imsi', 'like', $like)
                    ->orWhere('esims.description', 'like', $like)
                    ->orWhere('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like);
            });
        }

        $total = (clone $base)->count('esims.id');

        $rows = (clone $base)
            ->orderByDesc('esims.created_at')
            ->forPage($page, $pageSize)
            ->get([
                'esims.id as esim_id',
                'esims.msisdn',
                'esims.iccid',
                'esims.imsi',
                'esims.status as inventory_status',
                'esims.sim_type',
                'esims.provider_status',
                'esims.created_at as added_at',
                'user_esims.id as assignment_id',
                'users.id as user_id',
                'users.name as assigned_to',
            ])
            ->map(function ($r) {
                $assigned = ! is_null($r->assignment_id);
                $msisdn = (string) $r->msisdn;
                $identifier = str_starts_with($msisdn, '+') ? $msisdn : '+'.$msisdn;

                $isSuspended = ($r->provider_status ?? null) === Esim::PROVIDER_STATUS_SUSPENDED;

                return [
                    'esim_id' => (int) $r->esim_id,
                    'identifier' => $identifier,
                    'iccid' => $r->iccid,
                    'imsi' => $r->imsi,
                    'last_5' => substr(preg_replace('/\D+/', '', $msisdn), -5),
                    'status' => $isSuspended ? 'SUSPENDED' : ($assigned ? 'ASSIGNED' : 'INVENTORY'),
                    'sim_type' => $r->sim_type,
                    'provider_status' => $r->provider_status ?? Esim::PROVIDER_STATUS_ACTIVE,
                    'assigned_to' => $r->assigned_to,
                    'assigned_to_user_id' => $r->user_id ? (int) $r->user_id : null,
                    'deactivated' => null,
                    'added_at' => $r->added_at,
                ];
            })
            ->values();

        $counts = $this->counts($networkId);

        return response()->json([
            'success' => true,
            'provider' => [
                'id' => (int) $provider->id,
                'name' => $provider->name,
                'slug' => $provider->slug,
                'network_id' => $networkId,
            ],
            'counts' => $counts,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => (int) $total,
                'total_pages' => (int) ceil(max($total, 1) / $pageSize),
            ],
            'data' => $rows,
        ]);
    }

    private function providerNetworkId(Provider $provider): int
    {
        // Default: vodacom=1 (matches seed data), otherwise fall back to 1.
        if ($provider->slug === 'vodacom') {
            return 1;
        }

        $meta = is_array($provider->metadata) ? $provider->metadata : [];
        $id = $meta['network_id'] ?? null;

        return is_numeric($id) ? (int) $id : 1;
    }

    /**
     * @return array{total_pool:int, inventory:int, sold_assigned:int, suspended:int, physical:int, esim:int}
     */
    private function counts(int $networkId): array
    {
        $totalPool = (int) DB::table('esims')->where('network_id', $networkId)->count();
        $inventory = (int) DB::table('esims')
            ->leftJoin('user_esims', 'user_esims.esim_id', '=', 'esims.id')
            ->where('esims.network_id', $networkId)
            ->where('esims.provider_status', Esim::PROVIDER_STATUS_ACTIVE)
            ->whereNull('user_esims.id')
            ->count('esims.id');
        $soldAssigned = (int) DB::table('esims')
            ->join('user_esims', 'user_esims.esim_id', '=', 'esims.id')
            ->where('esims.network_id', $networkId)
            ->where('esims.provider_status', Esim::PROVIDER_STATUS_ACTIVE)
            ->count('esims.id');

        $suspended = (int) DB::table('esims')
            ->where('network_id', $networkId)
            ->where('provider_status', Esim::PROVIDER_STATUS_SUSPENDED)
            ->count();

        $physical = (int) DB::table('esims')
            ->where('network_id', $networkId)
            ->where('sim_type', Esim::SIM_TYPE_PHYSICAL)
            ->count();
        $esim = (int) DB::table('esims')
            ->where('network_id', $networkId)
            ->where('sim_type', Esim::SIM_TYPE_ESIM)
            ->count();

        return [
            'total_pool' => $totalPool,
            'inventory' => $inventory,
            'sold_assigned' => $soldAssigned,
            'suspended' => $suspended,
            'physical' => $physical,
            'esim' => $esim,
        ];
    }
}

