<?php

namespace App\Domains\Shareholder\Services;

use App\Models\Shareholder;
use Illuminate\Support\Facades\DB;

/**
 * Calculates and maintains shareholder ownership percentages.
 *
 * Rules:
 *  - `shares_owned` is the source of truth.
 *  - `ownership_percentage` is a cached, derived value for fast querying/reporting.
 *  - `ownership_percentage` must never be edited manually.
 *  - ownership_percentage = (shares_owned / total_company_shares) * 100
 *
 * Recalculation is triggered explicitly by share lifecycle events (add, issue,
 * transfer, buy-back, cancel) — never on every page load.
 */
class ShareOwnershipService
{
    /**
     * Chunk size used when walking large shareholder sets.
     */
    private const CHUNK_SIZE = 1000;

    /**
     * Number of decimals to keep for the cached percentage.
     */
    private const PERCENTAGE_PRECISION = 6;

    /**
     * Recalculate and persist ownership percentages for every shareholder of a
     * company. Only active shareholders hold shares and count toward the total;
     * inactive/transferred holders are zeroed so the cache stays consistent.
     */
    public function recalculateCompanyOwnerships(int $companyId): void
    {
        DB::transaction(function () use ($companyId) {
            $totalShares = (float) Shareholder::query()
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->sum('shares_owned');

            Shareholder::query()
                ->where('company_id', $companyId)
                ->chunkById(self::CHUNK_SIZE, function ($shareholders) use ($totalShares) {
                    foreach ($shareholders as $shareholder) {
                        $percentage = 0.0;

                        // Guard against division by zero and only credit active holders.
                        if ($totalShares > 0 && $shareholder->status === 'active') {
                            $percentage = round(
                                ((float) $shareholder->shares_owned / $totalShares) * 100,
                                self::PERCENTAGE_PRECISION,
                            );
                        }

                        // Persist without firing model events (avoids recursion) and
                        // bypasses mass-assignment guards on the derived column.
                        $shareholder->forceFill(['ownership_percentage' => $percentage])->saveQuietly();
                    }
                });
        });
    }

    /**
     * Read-optimised ownership summary sourced from the cached percentages.
     *
     * @return array<int, array{user_id: int|null, shares: float, percentage: float}>
     */
    public function getCompanyOwnershipSummary(int $companyId): array
    {
        $summary = [];

        Shareholder::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->select(['id', 'user_id', 'shares_owned', 'ownership_percentage'])
            ->chunkById(self::CHUNK_SIZE, function ($shareholders) use (&$summary) {
                foreach ($shareholders as $shareholder) {
                    $summary[] = [
                        'user_id' => $shareholder->user_id,
                        'shares' => (float) $shareholder->shares_owned,
                        'percentage' => (float) $shareholder->ownership_percentage,
                    ];
                }
            });

        return $summary;
    }
}
