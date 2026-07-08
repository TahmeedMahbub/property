<?php

namespace App\Domains\Loan\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Loan lifecycle: create / update loans and record repayments.
 *
 * A loan is a liability, NOT equity. This service never touches the cap table,
 * share transactions or the shareholder-investment journal. It only tracks the
 * loan principal, repayments and interest expense.
 */
class LoanService
{
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

        return Loan::create($data);
    }

    /**
     * Update an existing loan's terms.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Loan $loan, array $data): Loan
    {
        $loan->update($data);

        // Re-evaluate the status against the current balance unless it was
        // explicitly defaulted/closed by the user.
        $this->syncStatus($loan->refresh());

        return $loan;
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
        $repayment->delete();

        if ($loan) {
            $this->syncStatus($loan->load('repayments'));
        }
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
        $loan->delete();
    }
}
