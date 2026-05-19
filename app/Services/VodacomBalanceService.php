<?php

namespace App\Services;

use App\Models\Esim;
use App\Models\UserEsim;
use Illuminate\Support\Facades\Log;

class VodacomBalanceService
{
    /**
     * @param  array<string, mixed>  $raw
     * @return array{AIRTIME: float|null, DATA: float|null, SMS: float|null}
     */
    public function normalizeBalances(array $raw): array
    {
        $normalized = [];

        foreach (['AIRTIME', 'DATA', 'SMS'] as $key) {
            $value = $raw[$key] ?? null;
            $normalized[$key] = ($value === null || $value === '') ? null : (float) $value;
        }

        return $normalized;
    }

    /**
     * @param  array{msisdn?: string, balances?: array<string, mixed>, balance?: float|int|string, currency?: string}  $item
     * @return array{esim_id: int, assignment_updated: bool}|null
     */
    public function applyPayload(array $item): ?array
    {
        $msisdn = $item['msisdn'] ?? null;

        if (! is_string($msisdn) || $msisdn === '') {
            return null;
        }

        $esim = Esim::findByMsisdn($msisdn);

        if (! $esim) {
            Log::warning('Vodacom balance: SIM not in inventory', ['msisdn' => $msisdn, 'payload' => $item]);

            return null;
        }

        $balances = isset($item['balances']) && is_array($item['balances'])
            ? $this->normalizeBalances($item['balances'])
            : ['AIRTIME' => isset($item['balance']) ? (float) $item['balance'] : null, 'DATA' => null, 'SMS' => null];

        $currency = is_string($item['currency'] ?? null) ? $item['currency'] : 'TZS';
        $fetchedAt = now();

        $esim->update([
            'balances' => $balances,
            'balance_fetched_at' => $fetchedAt,
        ]);

        $assignment = UserEsim::where('esim_id', $esim->id)->first();
        $assignmentUpdated = false;

        if ($assignment) {
            $assignment->update([
                'balances' => $balances,
                'balance' => $balances['AIRTIME'],
                'balance_currency' => $currency,
                'balance_fetched_at' => $fetchedAt,
            ]);
            $assignmentUpdated = true;
        } else {
            Log::info('Vodacom balance: stored on esims only (no user assignment)', [
                'esim_id' => $esim->id,
                'msisdn' => $esim->msisdn,
            ]);
        }

        return [
            'esim_id' => $esim->id,
            'assignment_updated' => $assignmentUpdated,
        ];
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $payload
     * @return list<array{esim_id: int, assignment_updated: bool}>
     */
    public function syncFromVodacomPayload(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $results = [];

        if (isset($payload['msisdn'])) {
            $result = $this->applyPayload($payload);
            if ($result) {
                $results[] = $result;
            }

            return $results;
        }

        $items = $payload['data'] ?? $payload['items'] ?? $payload['sims'] ?? null;

        if ($items === null && array_is_list($payload)) {
            $items = $payload;
        }

        if (! is_array($items)) {
            Log::warning('Vodacom balance: unrecognized payload shape', ['keys' => array_keys($payload)]);

            return [];
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $result = $this->applyPayload($item);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }
}
