<?php

namespace App\Domains\Company\Services;

use App\Domains\Shareholder\Services\ShareOwnershipService;
use App\Models\CompanyMetrics;
use App\Models\Shareholder;

/**
 * Company-level, read-optimised metrics.
 *
 * Designed for companies with thousands of shareholders: it reads aggregate
 * values and the cached ownership percentages rather than recomputing them.
 */
class CompanyMetricsService
{
    public function __construct(
        private readonly int $companyId,
        private readonly ShareOwnershipService $shareOwnership = new ShareOwnershipService(),
    ) {}

    /**
     * Total issued shares for the company, read from the cached metrics row.
     */
    public function totalShares(): float
    {
        $cached = CompanyMetrics::where('company_id', $this->companyId)->value('total_shares');

        if ($cached !== null) {
            return (float) $cached;
        }

        // Fallback for companies without a metrics row yet.
        return (float) Shareholder::query()
            ->where('company_id', $this->companyId)
            ->where('status', 'active')
            ->sum('shares_owned');
    }

    /**
     * Current price per share, read from the cached metrics row.
     */
    public function currentSharePrice(): float
    {
        return (float) (CompanyMetrics::where('company_id', $this->companyId)->value('current_share_price') ?? 0);
    }

    /**
     * Current company valuation, read from the cached metrics row.
     */
    public function currentValuation(): float
    {
        return (float) (CompanyMetrics::where('company_id', $this->companyId)->value('current_valuation') ?? 0);
    }

    /**
     * Ownership breakdown per active shareholder, using cached percentages.
     *
     * @return array<int, array{user_id: int|null, shares: float, percentage: float}>
     */
    public function ownershipSummary(): array
    {
        return $this->shareOwnership->getCompanyOwnershipSummary($this->companyId);
    }
}
