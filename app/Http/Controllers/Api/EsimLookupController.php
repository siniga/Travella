<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Esim;
use App\Models\UserEsim;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EsimLookupController extends Controller
{
    /** ICCIDs in inventory share a long prefix; matching starts at the last 2 digits. */
    private const MIN_SUFFIX_LENGTH = 2;

    /**
     * Autocomplete: match ICCID by the last digits the user is typing.
     *
     * Query: iccid or q (digits only), optional sim_type, available_only, limit
     */
    public function searchByIccidSuffix(Request $request): JsonResponse
    {
        $suffix = $this->normalizeIccidSuffix(
            (string) ($request->query('iccid') ?? $request->query('q') ?? '')
        );

        if ($suffix === '') {
            return response()->json([
                'success' => false,
                'message' => 'Provide iccid or q query parameter (last digits of the ICCID).',
                'min_length' => self::MIN_SUFFIX_LENGTH,
                'suggestions' => [],
            ], 422);
        }

        if (strlen($suffix) < self::MIN_SUFFIX_LENGTH) {
            return response()->json([
                'success' => true,
                'query' => $suffix,
                'min_length' => self::MIN_SUFFIX_LENGTH,
                'message' => 'Type at least '.self::MIN_SUFFIX_LENGTH.' digits from the end of the ICCID.',
                'suggestions' => [],
            ]);
        }

        $limit = min(max((int) $request->query('limit', 10), 1), 20);
        $simType = strtolower((string) $request->query('sim_type', Esim::SIM_TYPE_PHYSICAL));
        $availableOnly = $request->boolean('available_only');

        $query = Esim::query()
            ->whereNotNull('iccid')
            ->where(fn (Builder $q) => $this->applySuffixMatch($q, $suffix))
            ->orderBy('iccid');

        if (in_array($simType, [Esim::SIM_TYPE_PHYSICAL, Esim::SIM_TYPE_ESIM], true)) {
            $query->where('sim_type', $simType);
        }

        if ($availableOnly) {
            $query->whereNotIn('id', UserEsim::query()->select('esim_id'));
        }

        $esims = $query->limit($limit)->get();

        $assignedExcluded = 0;
        if ($availableOnly && $esims->isEmpty()) {
            $assignedExcluded = $this->matchingCount($suffix, $simType, true);
        }

        $assignedEsimIds = UserEsim::query()
            ->whereIn('esim_id', $esims->pluck('id'))
            ->pluck('esim_id')
            ->flip();

        $suffixLen = strlen($suffix);

        $suggestions = $esims->map(fn (Esim $esim) => [
            'id' => $esim->id,
            'iccid' => $esim->iccid,
            'iccid_suffix' => $esim->iccid ? substr($esim->iccid, -$suffixLen) : null,
            'iccid_last_two' => $esim->iccid ? substr($esim->iccid, -2) : null,
            'msisdn' => $esim->msisdn,
            'sim_type' => $esim->sim_type,
            'status' => $esim->status,
            'provider_status' => $esim->provider_status,
            'is_assigned' => isset($assignedEsimIds[$esim->id]),
            'label' => trim(($esim->iccid ?? '').($esim->msisdn ? ' · '.$esim->msisdn : '')),
            'value' => $esim->iccid,
        ])->values();

        return response()->json([
            'success' => true,
            'query' => $suffix,
            'min_length' => self::MIN_SUFFIX_LENGTH,
            'match_mode' => 'iccid_suffix',
            'count' => $suggestions->count(),
            'assigned_matches_excluded' => $assignedExcluded,
            'hint' => $assignedExcluded > 0
                ? 'Matching SIM(s) exist but are already assigned. Try another card or search without available_only.'
                : null,
            'suggestions' => $suggestions,
        ]);
    }

    private function matchingCount(string $suffix, string $simType, bool $assignedOnly): int
    {
        $query = Esim::query()
            ->whereNotNull('iccid')
            ->where(fn (Builder $q) => $this->applySuffixMatch($q, $suffix));

        if (in_array($simType, [Esim::SIM_TYPE_PHYSICAL, Esim::SIM_TYPE_ESIM], true)) {
            $query->where('sim_type', $simType);
        }

        if ($assignedOnly) {
            $query->whereIn('id', UserEsim::query()->select('esim_id'));
        }

        return $query->count();
    }

    /**
     * Match on the trailing digits of ICCID (cards differ mainly in the last 2 digits).
     * Full ICCID scans also match exactly.
     */
    private function applySuffixMatch(Builder $query, string $suffix): void
    {
        $escaped = $this->escapeLike($suffix);
        $likeSuffix = '%'.$escaped;

        $query->where(function (Builder $q) use ($suffix, $likeSuffix) {
            $q->where('iccid', 'like', $likeSuffix);

            if (strlen($suffix) >= 15) {
                $q->orWhere('iccid', $suffix);
            }
        });
    }

    private function normalizeIccidSuffix(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?? '';
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
