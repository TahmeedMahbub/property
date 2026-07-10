<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Generic expense lifecycle for every module. An expense is cash leaving the
 * company, so each one posts a DEBIT (cash out) to the company Journal under
 * the {@see self::CATEGORY} category. Expenses are attached to their source
 * module (booking, plot, project, …) through the polymorphic `expensable`
 * relation, or left standalone (company-level).
 */
class ExpenseService
{
    public const CATEGORY = 'expense';

    /**
     * Record an expense and post its cash-out ledger entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(int $companyId, array $data, ?Model $expensable = null): Expense
    {
        return DB::transaction(function () use ($companyId, $data, $expensable) {
            $data['company_id'] = $companyId;
            $data['created_by'] = $data['created_by'] ?? Auth::id();

            if ($expensable) {
                $data['expensable_type'] = $expensable->getMorphClass();
                $data['expensable_id'] = $expensable->getKey();
            }

            $expense = Expense::create($data);

            $this->syncJournal($expense);

            return $expense;
        });
    }

    /**
     * Update an expense and re-post its cash-out ledger entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data);

            $this->syncJournal($expense->fresh());

            return $expense->refresh();
        });
    }

    /**
     * Delete an expense and reverse its cash-out ledger entry.
     */
    public function delete(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            JournalService::reverseReference(
                companyId: $expense->company_id,
                reference: $expense,
                category: self::CATEGORY,
                remarks: 'Reversed expense ' . ($expense->title ?? $expense->category),
            );

            $expense->delete();
        });
    }

    /**
     * Post the ledger debit (cash out) for an expense.
     */
    private function syncJournal(Expense $expense): void
    {
        JournalService::syncReference(
            companyId: $expense->company_id,
            reference: $expense,
            // Negative target credit ⇒ a debit (cash out) of the expense amount.
            targetCredit: -(float) $expense->amount,
            category: self::CATEGORY,
            remarks: ($expense->title ?: ucfirst($expense->category)) . ' expense',
            userId: $expense->created_by,
        );
    }
}
