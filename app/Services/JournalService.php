<?php

namespace App\Services;

use App\Models\Journal;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Record a journal entry with running balance calculation.
     * Uses a DB lock to ensure balance consistency under concurrency.
     */
    public static function record(
        int $companyId,
        string $type,
        float|int $amount,
        ?string $category = null,
        ?string $remarks = null,
        ?object $reference = null,
        ?int $userId = null,
    ): Journal {
        return DB::transaction(function () use ($companyId, $type, $amount, $category, $remarks, $reference, $userId) {
            // Lock the latest journal row for this company to prevent race conditions
            $lastEntry = Journal::where('company_id', $companyId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $currentBalance = $lastEntry ? (float) $lastEntry->balance_after : 0.00;

            $balanceAfter = $type === 'credit'
                ? $currentBalance + $amount
                : $currentBalance - $amount;

            return Journal::create([
                'company_id' => $companyId,
                'user_id' => $userId ?? (auth()->id() ?? null),
                'type' => $type,
                'amount' => abs($amount),
                'balance_after' => $balanceAfter,
                'category' => $category,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id ?? null,
                'remarks' => $remarks,
            ]);
        });
    }

    /**
     * Get the current balance for a company (from latest journal entry).
     */
    public static function balance(int $companyId): float
    {
        $last = Journal::where('company_id', $companyId)
            ->orderByDesc('id')
            ->value('balance_after');

        return (float) ($last ?? 0);
    }

    /**
     * Keep a record's net journal contribution in sync with a target credit amount.
     *
     * Use this in store()/update() so the ledger always reflects the current value.
     * It computes the net already posted for the reference and posts only the delta.
     * Safe to call repeatedly — it is idempotent for a given target.
     *
     * @param  int         $companyId
     * @param  object      $reference     The related model (e.g. Shareholder, ProjectInvestor)
     * @param  float       $targetCredit  Desired net credit (money-in) for this record
     * @param  string      $category
     * @param  string|null $remarks
     * @param  int|null    $userId
     * @return Journal|null  The adjustment entry, or null if nothing changed
     */
    public static function syncReference(
        int $companyId,
        object $reference,
        float $targetCredit,
        string $category,
        ?string $remarks = null,
        ?int $userId = null,
    ): ?Journal {
        $currentNet = self::referenceNet($companyId, $reference);
        $delta = round($targetCredit - $currentNet, 2);

        if ($delta == 0.0) {
            return null;
        }

        $type = $delta > 0 ? 'credit' : 'debit';

        return self::record(
            companyId: $companyId,
            type: $type,
            amount: abs($delta),
            category: $category,
            remarks: $remarks,
            reference: $reference,
            userId: $userId,
        );
    }

    /**
     * Reverse a record's entire journal contribution (used on delete).
     */
    public static function reverseReference(
        int $companyId,
        object $reference,
        string $category,
        ?string $remarks = null,
        ?int $userId = null,
    ): ?Journal {
        return self::syncReference($companyId, $reference, 0.0, $category, $remarks, $userId);
    }

    /**
     * Net journal contribution (credits - debits) already posted for a reference.
     */
    public static function referenceNet(int $companyId, object $reference): float
    {
        return (float) Journal::where('company_id', $companyId)
            ->where('reference_type', get_class($reference))
            ->where('reference_id', $reference->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as net")
            ->value('net');
    }

    /**
     * Get total credits for a company (optionally filtered by category).
     */
    public static function totalCredits(int $companyId, ?string $category = null): float
    {
        $query = Journal::where('company_id', $companyId)->where('type', 'credit');

        if ($category) {
            $query->where('category', $category);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get total debits for a company (optionally filtered by category).
     */
    public static function totalDebits(int $companyId, ?string $category = null): float
    {
        $query = Journal::where('company_id', $companyId)->where('type', 'debit');

        if ($category) {
            $query->where('category', $category);
        }

        return (float) $query->sum('amount');
    }
}
