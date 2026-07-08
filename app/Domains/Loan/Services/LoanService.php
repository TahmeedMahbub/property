<?php

namespace App\Domains\Loan\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
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

            $this->syncRepaymentJournal($loan, $repayment);
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
     * Post the ledger debit for a repayment (principal + interest + penalty out).
     */
    private function syncRepaymentJournal(Loan $loan, LoanRepayment $repayment): void
    {
        $total = (float) $repayment->principal_paid
            + (float) $repayment->interest_paid
            + (float) $repayment->penalty;

        JournalService::syncReference(
            companyId: $loan->company_id,
            reference: $repayment,
            targetCredit: -$total,
            category: self::CATEGORY_REPAYMENT,
            remarks: 'Loan repayment to ' . $loan->lender_name,
            userId: $repayment->created_by,
        );
    }
}
