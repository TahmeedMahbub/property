<?php

namespace App\Domains\Loan\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use App\Services\JournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Loan lifecycle: create / update loans and record repayments.
 *
 * A loan is a liability, NOT equity. This service never touches the cap table
 * or share transactions. Cash movements ARE posted to the company journal so
 * the ledger stays the single source of truth: the loan principal is a credit
 * (cash in) and every repayment is a debit (cash out).
 */
class LoanService
{
    private const CATEGORY_RECEIVED = 'loan_received';

    private const CATEGORY_REPAYMENT = 'loan_repayment';

    public function __construct(
        private readonly ExpenseService $expenses = new ExpenseService(),
    ) {}

    /**
     * Create a new loan for a company.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): Loan
    {
        $data['company_id'] = $companyId;
        $data['created_by'] = $data['created_by'] ?? Auth::id();
        $data['status'] = $data['status'] ?? 'active';

        return DB::transaction(function () use ($companyId, $data) {
            $loan = Loan::create($data);
            $this->syncLoanJournal($loan);

            return $loan;
        });
    }

    /**
     * Update an existing loan's terms.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Loan $loan, array $data): Loan
    {
        return DB::transaction(function () use ($loan, $data) {
            $loan->update($data);

            // Keep the ledger credit in step with the (possibly changed) principal.
            $this->syncLoanJournal($loan->refresh());

            // Re-evaluate the status against the current balance unless it was
            // explicitly defaulted/closed by the user.
            $this->syncStatus($loan);

            return $loan;
        });
    }

    /**
     * Record a repayment against a loan and keep its status in sync.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordRepayment(Loan $loan, array $data): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $data) {
            $data['loan_id'] = $loan->id;
            $data['created_by'] = $data['created_by'] ?? Auth::id();

            $repayment = LoanRepayment::create($data);

            // Principal reduces the loan balance (financing), while interest and
            // penalty are true costs — so they are recorded as expenses. Each
            // posts its own cash-out entry, keeping the ledger total unchanged.
            $this->syncRepaymentJournal($loan, $repayment);
            $this->syncRepaymentExpenses($loan, $repayment);
            $this->syncStatus($loan->load('repayments'));

            return $repayment;
        });
    }

    /**
     * Delete a repayment and re-evaluate the loan status.
     */
    public function deleteRepayment(LoanRepayment $repayment): void
    {
        $loan = $repayment->loan;

        DB::transaction(function () use ($loan, $repayment) {
            // Remove the interest/penalty expenses this repayment generated.
            $this->deleteRepaymentExpenses($repayment);

            if ($loan) {
                JournalService::reverseReference(
                    companyId: $loan->company_id,
                    reference: $repayment,
                    category: self::CATEGORY_REPAYMENT,
                    remarks: 'Reversed loan repayment to ' . $loan->lender_name,
                );
            }

            $repayment->delete();

            if ($loan) {
                $this->syncStatus($loan->load('repayments'));
            }
        });
    }

    /**
     * Auto-close a fully repaid loan, or re-open it if it still has a balance.
     * A loan explicitly marked "defaulted" is left untouched.
     */
    public function syncStatus(Loan $loan): void
    {
        if ($loan->status === 'defaulted') {
            return;
        }

        $target = $loan->outstanding_balance <= 0 ? 'closed' : 'active';

        if ($loan->status !== $target) {
            $loan->forceFill(['status' => $target])->save();
        }
    }

    /**
     * Delete a loan (and its repayments cascade at the DB level).
     */
    public function delete(Loan $loan): void
    {
        DB::transaction(function () use ($loan) {
            // Unwind every cash movement this loan posted to the ledger.
            foreach ($loan->repayments as $repayment) {
                $this->deleteRepaymentExpenses($repayment);

                JournalService::reverseReference(
                    companyId: $loan->company_id,
                    reference: $repayment,
                    category: self::CATEGORY_REPAYMENT,
                    remarks: 'Reversed loan repayment to ' . $loan->lender_name,
                );
            }

            JournalService::reverseReference(
                companyId: $loan->company_id,
                reference: $loan,
                category: self::CATEGORY_RECEIVED,
                remarks: 'Reversed loan from ' . $loan->lender_name,
            );

            $loan->delete();
        });
    }

    /**
     * Post/adjust the ledger credit for the loan principal (cash received).
     */
    private function syncLoanJournal(Loan $loan): void
    {
        JournalService::syncReference(
            companyId: $loan->company_id,
            reference: $loan,
            targetCredit: (float) $loan->principal_amount,
            category: self::CATEGORY_RECEIVED,
            remarks: 'Loan received from ' . $loan->lender_name,
            userId: $loan->created_by,
        );
    }

    /**
     * Post the ledger debit for a repayment's principal (financing, cash out).
     *
     * Interest and penalty are excluded here — they are posted separately as
     * expenses so they show up in the expense module. The combined cash-out
     * therefore equals principal + interest + penalty, with no double counting.
     */
    private function syncRepaymentJournal(Loan $loan, LoanRepayment $repayment): void
    {
        JournalService::syncReference(
            companyId: $loan->company_id,
            reference: $repayment,
            targetCredit: -(float) $repayment->principal_paid,
            category: self::CATEGORY_REPAYMENT,
            remarks: 'Loan principal repayment to ' . $loan->lender_name,
            userId: $repayment->created_by,
        );
    }

    /**
     * Record the interest and penalty portions of a repayment as expenses.
     *
     * Each expense posts its own cash-out journal entry (via ExpenseService),
     * linked to the repayment so it can be reversed when the repayment is
     * deleted. Zero amounts are skipped.
     */
    private function syncRepaymentExpenses(Loan $loan, LoanRepayment $repayment): void
    {
        $items = [
            'interest' => (float) $repayment->interest_paid,
            'penalty' => (float) $repayment->penalty,
        ];

        foreach ($items as $slug => $amount) {
            if ($amount <= 0) {
                continue;
            }

            $category = ExpenseCategory::forCompany($loan->company_id)
                ->where('slug', $slug)
                ->first();

            $this->expenses->record($loan->company_id, [
                'created_by' => $repayment->created_by,
                'category_id' => $category?->id,
                'category' => $slug,
                'title' => ucfirst($slug) . ' — ' . $loan->lender_name,
                'amount' => $amount,
                'expense_date' => $repayment->payment_date,
                'payment_method' => $repayment->payment_method,
                'reference_no' => $repayment->reference_no,
                'notes' => 'Auto-recorded from loan repayment.',
            ], $repayment);
        }
    }

    /**
     * Remove the expenses (and their journal entries) a repayment generated.
     */
    private function deleteRepaymentExpenses(LoanRepayment $repayment): void
    {
        $expenses = Expense::where('expensable_type', $repayment->getMorphClass())
            ->where('expensable_id', $repayment->id)
            ->get();

        foreach ($expenses as $expense) {
            $this->expenses->delete($expense);
        }
    }
}
