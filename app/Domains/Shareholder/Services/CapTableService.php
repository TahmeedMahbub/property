<?php

namespace App\Domains\Shareholder\Services;

use App\Models\CompanyMetrics;
use App\Models\ShareTransaction;
use App\Models\Shareholder;
use App\Services\JournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Cap-table engine.
 *
 * Architecture (event-sourced):
 *  - `p_share_transactions` is the immutable ledger and the ultimate source of truth.
 *  - `Shareholder.shares_owned` is a running cache (sum of a holder's transactions).
 *  - `Shareholder.ownership_percentage` is a cached projection, never edited manually.
 *  - `CompanyMetrics` caches company-wide aggregates (total shares, price, valuation).
 *
 * Pricing:
 *  - share_price = current_valuation / total_shares (par price for the first issue).
 *  - Investing *at the current price* does not change the price — it only dilutes
 *    everyone's ownership percentage. Price only moves on an explicit revaluation.
 */
class CapTableService
{
    /**
     * Par price used for the very first issuance, when no shares exist yet.
     */
    private const INITIAL_SHARE_PRICE = 1.00;

    private const SHARE_PRECISION = 6;

    private const MONEY_PRECISION = 2;

    public function __construct(
        private readonly ShareOwnershipService $shareOwnership = new ShareOwnershipService(),
    ) {}

    /**
     * Current price per share for a company.
     */
    public function calculateSharePrice(int $companyId): float
    {
        $metrics = $this->metrics($companyId);

        if ((float) $metrics->total_shares <= 0) {
            return self::INITIAL_SHARE_PRICE;
        }

        return round((float) $metrics->current_valuation / (float) $metrics->total_shares, self::SHARE_PRECISION);
    }

    /**
     * Issue new shares to a holder in exchange for an investment (primary raise).
     * shares_issued = investment_amount / current_share_price.
     */
    public function issueShares(
        Shareholder $shareholder,
        float $investmentAmount,
        ?string $notes = null,
        ?int $userId = null,
    ): ShareTransaction {
        if ($investmentAmount <= 0) {
            throw new InvalidArgumentException('Investment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($shareholder, $investmentAmount, $notes, $userId) {
            $companyId = $shareholder->company_id;
            $price = $this->calculateSharePrice($companyId);
            $sharesIssued = round($investmentAmount / $price, self::SHARE_PRECISION);

            $transaction = ShareTransaction::create([
                'company_id' => $companyId,
                'shareholder_id' => $shareholder->id,
                'user_id' => $userId ?? Auth::id(),
                'type' => 'issue',
                'investment_amount' => round($investmentAmount, self::MONEY_PRECISION),
                'share_price' => $price,
                'shares_issued' => $sharesIssued,
                'notes' => $notes,
            ]);

            $this->adjustHolderShares($shareholder, $sharesIssued);

            // New money in + new shares out. Price is preserved by construction.
            $metrics = $this->metrics($companyId);
            $metrics->total_shares = round((float) $metrics->total_shares + $sharesIssued, self::SHARE_PRECISION);
            $metrics->current_valuation = round((float) $metrics->current_valuation + $investmentAmount, self::MONEY_PRECISION);
            $this->refreshPrice($metrics);
            $metrics->save();

            // Ledger: investment is money-in for the company.
            JournalService::record(
                companyId: $companyId,
                type: 'credit',
                amount: round($investmentAmount, self::MONEY_PRECISION),
                category: 'shareholder_investment',
                remarks: 'Share issue to ' . $shareholder->name,
                reference: $transaction,
                userId: $userId,
            );

            $this->recalculateOwnerships($companyId);

            return $transaction;
        });
    }

    /**
     * Transfer existing shares from one holder to another (secondary — no new money,
     * total shares unchanged). Recorded as two linked ledger rows.
     *
     * @return array{0: ShareTransaction, 1: ShareTransaction} [outgoing, incoming]
     */
    public function transferShares(
        Shareholder $from,
        Shareholder $to,
        float $shares,
        ?string $notes = null,
        ?int $userId = null,
    ): array {
        if ($shares <= 0) {
            throw new InvalidArgumentException('Transfer must be greater than zero shares.');
        }

        if ($from->company_id !== $to->company_id) {
            throw new InvalidArgumentException('Cannot transfer shares between different companies.');
        }

        if (round((float) $from->shares_owned, self::SHARE_PRECISION) < round($shares, self::SHARE_PRECISION)) {
            throw new InvalidArgumentException('Holder does not own enough shares to transfer.');
        }

        return DB::transaction(function () use ($from, $to, $shares, $notes, $userId) {
            $companyId = $from->company_id;
            $price = $this->calculateSharePrice($companyId);
            $shares = round($shares, self::SHARE_PRECISION);

            $outgoing = ShareTransaction::create([
                'company_id' => $companyId,
                'shareholder_id' => $from->id,
                'related_shareholder_id' => $to->id,
                'user_id' => $userId ?? Auth::id(),
                'type' => 'transfer',
                'investment_amount' => null,
                'share_price' => $price,
                'shares_issued' => -$shares,
                'notes' => $notes,
            ]);

            $incoming = ShareTransaction::create([
                'company_id' => $companyId,
                'shareholder_id' => $to->id,
                'related_shareholder_id' => $from->id,
                'user_id' => $userId ?? Auth::id(),
                'type' => 'transfer',
                'investment_amount' => null,
                'share_price' => $price,
                'shares_issued' => $shares,
                'notes' => $notes,
            ]);

            $this->adjustHolderShares($from, -$shares);
            $this->adjustHolderShares($to, $shares);

            // Total shares & valuation unchanged; percentages shift.
            $this->recalculateOwnerships($companyId);

            return [$outgoing, $incoming];
        });
    }

    /**
     * Company repurchases shares from a holder (money out, total shares reduced).
     */
    public function buybackShares(
        Shareholder $shareholder,
        float $shares,
        ?string $notes = null,
        ?int $userId = null,
    ): ShareTransaction {
        if ($shares <= 0) {
            throw new InvalidArgumentException('Buyback must be greater than zero shares.');
        }

        if (round((float) $shareholder->shares_owned, self::SHARE_PRECISION) < round($shares, self::SHARE_PRECISION)) {
            throw new InvalidArgumentException('Holder does not own enough shares to buy back.');
        }

        return DB::transaction(function () use ($shareholder, $shares, $notes, $userId) {
            $companyId = $shareholder->company_id;
            $price = $this->calculateSharePrice($companyId);
            $shares = round($shares, self::SHARE_PRECISION);
            $amount = round($shares * $price, self::MONEY_PRECISION);

            $transaction = ShareTransaction::create([
                'company_id' => $companyId,
                'shareholder_id' => $shareholder->id,
                'user_id' => $userId ?? Auth::id(),
                'type' => 'buyback',
                'investment_amount' => $amount,
                'share_price' => $price,
                'shares_issued' => -$shares,
                'notes' => $notes,
            ]);

            $this->adjustHolderShares($shareholder, -$shares);

            $metrics = $this->metrics($companyId);
            $metrics->total_shares = round((float) $metrics->total_shares - $shares, self::SHARE_PRECISION);
            $metrics->current_valuation = round((float) $metrics->current_valuation - $amount, self::MONEY_PRECISION);
            $this->refreshPrice($metrics);
            $metrics->save();

            // Ledger: buyback is money-out for the company.
            JournalService::record(
                companyId: $companyId,
                type: 'debit',
                amount: $amount,
                category: 'shareholder_investment',
                remarks: 'Share buyback from ' . $shareholder->name,
                reference: $transaction,
                userId: $userId,
            );

            $this->recalculateOwnerships($companyId);

            return $transaction;
        });
    }

    /**
     * Withdraw a cash amount for a holder by buying back the equivalent shares at the
     * current price. Convenience wrapper around buybackShares for money-denominated UIs.
     */
    public function withdrawAmount(
        Shareholder $shareholder,
        float $amount,
        ?string $notes = null,
        ?int $userId = null,
    ): ShareTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        $price = $this->calculateSharePrice($shareholder->company_id);
        $shares = round($amount / $price, self::SHARE_PRECISION);

        // Guard against rounding pushing the request just past the full balance.
        $owned = round((float) $shareholder->shares_owned, self::SHARE_PRECISION);
        if ($shares > $owned) {
            $shares = $owned;
        }

        return $this->buybackShares(
            shareholder: $shareholder,
            shares: $shares,
            notes: $notes ?? 'Withdrawal',
            userId: $userId,
        );
    }

    /**
     * Cancel a holder's shares without payment (total shares reduced, valuation kept).
     */
    public function cancelShares(
        Shareholder $shareholder,
        float $shares,
        ?string $notes = null,
        ?int $userId = null,
    ): ShareTransaction {
        if ($shares <= 0) {
            throw new InvalidArgumentException('Cancellation must be greater than zero shares.');
        }

        if (round((float) $shareholder->shares_owned, self::SHARE_PRECISION) < round($shares, self::SHARE_PRECISION)) {
            throw new InvalidArgumentException('Holder does not own enough shares to cancel.');
        }

        return DB::transaction(function () use ($shareholder, $shares, $notes, $userId) {
            $companyId = $shareholder->company_id;
            $price = $this->calculateSharePrice($companyId);
            $shares = round($shares, self::SHARE_PRECISION);

            $transaction = ShareTransaction::create([
                'company_id' => $companyId,
                'shareholder_id' => $shareholder->id,
                'user_id' => $userId ?? Auth::id(),
                'type' => 'cancellation',
                'investment_amount' => null,
                'share_price' => $price,
                'shares_issued' => -$shares,
                'notes' => $notes,
            ]);

            $this->adjustHolderShares($shareholder, -$shares);

            $metrics = $this->metrics($companyId);
            $metrics->total_shares = round((float) $metrics->total_shares - $shares, self::SHARE_PRECISION);
            // Valuation unchanged → remaining holders' price per share rises.
            $this->refreshPrice($metrics);
            $metrics->save();

            $this->recalculateOwnerships($companyId);

            return $transaction;
        });
    }

    /**
     * Explicitly set the company's valuation (a funding-round mark or NAV appraisal).
     * This is the only operation that moves the share price on its own.
     */
    public function revalueCompany(int $companyId, float $newValuation): CompanyMetrics
    {
        if ($newValuation < 0) {
            throw new InvalidArgumentException('Valuation cannot be negative.');
        }

        return DB::transaction(function () use ($companyId, $newValuation) {
            $metrics = $this->metrics($companyId);
            $metrics->current_valuation = round($newValuation, self::MONEY_PRECISION);
            $this->refreshPrice($metrics);
            $metrics->save();

            // Percentages depend on shares only, so they are unaffected by revaluation.
            return $metrics;
        });
    }

    /**
     * Recalculate and cache ownership percentages for every holder in the company.
     */
    public function recalculateOwnerships(int $companyId): void
    {
        $this->shareOwnership->recalculateCompanyOwnerships($companyId);
    }

    /**
     * Fetch (or lazily create) the metrics row for a company, locked for update
     * inside the surrounding transaction to keep aggregates consistent.
     */
    private function metrics(int $companyId): CompanyMetrics
    {
        return CompanyMetrics::firstOrCreate(['company_id' => $companyId]);
    }

    /**
     * Apply a signed share delta to a holder's cached balance without firing events.
     */
    private function adjustHolderShares(Shareholder $shareholder, float $delta): void
    {
        $newBalance = round((float) $shareholder->shares_owned + $delta, self::SHARE_PRECISION);
        $shareholder->forceFill(['shares_owned' => $newBalance])->saveQuietly();
    }

    /**
     * Recompute the cached price from valuation / total shares.
     */
    private function refreshPrice(CompanyMetrics $metrics): void
    {
        $totalShares = (float) $metrics->total_shares;
        $metrics->current_share_price = $totalShares > 0
            ? round((float) $metrics->current_valuation / $totalShares, self::SHARE_PRECISION)
            : self::INITIAL_SHARE_PRICE;
    }
}
