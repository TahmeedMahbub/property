<?php

namespace App\Domains\Loan\Services;

use App\Models\Loan;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-optimised loan metrics and reports for dashboards and reporting screens.
 *
 * All figures are derived from the loan principal and its repayments so the
 * numbers are always accurate and never drift out of a cache.
 */
class LoanReportService
{
    /**
     * Company-wide loan dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function companyMetrics(int $companyId): array
    {
        // Principal is aggregated on the loans table directly.
        $loanTotals = Loan::forCompany($companyId)
            ->selectRaw('
                COUNT(*) as loan_count,
                COUNT(CASE WHEN status = "active" THEN 1 END) as active_loans,
                COALESCE(SUM(principal_amount), 0) as principal_borrowed
            ')
            ->first();

        // Repayment figures are aggregated on the repayments table, scoped by company.
        $repaymentTotals = Loan::forCompany($companyId)
            ->join('p_loan_repayments', 'p_loans.id', '=', 'p_loan_repayments.loan_id')
            ->selectRaw('
                COALESCE(SUM(principal_paid), 0) as principal_repaid,
                COALESCE(SUM(interest_paid), 0) as interest_paid,
                COALESCE(SUM(penalty), 0) as penalty_paid
            ')
            ->first();

        $principalBorrowed = (float) $loanTotals->principal_borrowed;
        $principalRepaid = (float) $repaymentTotals->principal_repaid;
        $interestPaid = (float) $repaymentTotals->interest_paid;

        return [
            'total_principal_borrowed' => round($principalBorrowed, 2),
            'total_principal_repaid' => round($principalRepaid, 2),
            'total_interest_paid' => round($interestPaid, 2),
            'total_penalty_paid' => round((float) $repaymentTotals->penalty_paid, 2),
            'total_outstanding' => round($principalBorrowed - $principalRepaid, 2),
            'active_loans' => (int) $loanTotals->active_loans,
            'loan_count' => (int) $loanTotals->loan_count,
            'by_lender_type' => $this->byLenderType($companyId),
            'upcoming_payments' => $this->upcomingPayments($companyId, 30),
        ];
    }

    /**
     * Loan totals grouped by lender type.
     *
     * @return array<int, array{lender_type: string, principal: float, outstanding: float, count: int}>
     */
    public function byLenderType(int $companyId): array
    {
        $principalByType = Loan::forCompany($companyId)
            ->selectRaw('lender_type, COUNT(*) as cnt, COALESCE(SUM(principal_amount), 0) as principal')
            ->groupBy('lender_type')
            ->get()
            ->keyBy('lender_type');

        $repaidByType = Loan::forCompany($companyId)
            ->join('p_loan_repayments', 'p_loans.id', '=', 'p_loan_repayments.loan_id')
            ->selectRaw('lender_type, COALESCE(SUM(principal_paid), 0) as repaid')
            ->groupBy('lender_type')
            ->pluck('repaid', 'lender_type');

        return $principalByType->map(function ($row) use ($repaidByType) {
            $principal = (float) $row->principal;
            $repaid = (float) ($repaidByType[$row->lender_type] ?? 0);

            return [
                'lender_type' => $row->lender_type,
                'count' => (int) $row->cnt,
                'principal' => round($principal, 2),
                'outstanding' => round($principal - $repaid, 2),
            ];
        })->values()->all();
    }

    /**
     * Project-level loan and profitability metrics.
     *
     * Net Project Profit = revenue (sold units) − project budget (cost)
     *                      − interest expense on the project's loans.
     *
     * @return array<string, mixed>
     */
    public function projectMetrics(\App\Models\Project $project): array
    {
        $principal = (float) $project->loans()->sum('principal_amount');

        $repaid = (float) Loan::where('project_id', $project->id)
            ->join('p_loan_repayments', 'p_loans.id', '=', 'p_loan_repayments.loan_id')
            ->sum('principal_paid');

        $interestExpense = (float) Loan::where('project_id', $project->id)
            ->join('p_loan_repayments', 'p_loans.id', '=', 'p_loan_repayments.loan_id')
            ->sum('interest_paid');

        $revenue = (float) Unit::where('project_id', $project->id)
            ->whereIn('status', ['sold', 'handovered'])
            ->sum('price');

        $budget = (float) $project->budget;
        $netProfit = round($revenue - $budget - $interestExpense, 2);

        return [
            'project_loan_amount' => round($principal, 2),
            'outstanding_loan' => round($principal - $repaid, 2),
            'interest_expense' => round($interestExpense, 2),
            'revenue' => round($revenue, 2),
            'budget' => round($budget, 2),
            'net_project_profit' => $netProfit,
        ];
    }

    /**
     * Loans with an upcoming installment or maturity within $days.
     *
     * @return Collection<int, array{loan: Loan, due_date: Carbon, days_left: int, kind: string}>
     */
    public function upcomingPayments(int $companyId, int $days = 30): Collection
    {
        $today = Carbon::today();
        $limit = $today->copy()->addDays($days);

        return Loan::forCompany($companyId)
            ->active()
            ->with('project')
            ->get()
            ->map(function (Loan $loan) use ($today) {
                $due = $this->nextDueDate($loan, $today);

                return $due ? [
                    'loan' => $loan,
                    'due_date' => $due,
                    'days_left' => $today->diffInDays($due, false),
                    'kind' => ($loan->end_date && $due->equalTo($loan->end_date->startOfDay())) ? 'maturity' : 'installment',
                ] : null;
            })
            ->filter()
            ->filter(fn ($row) => $row['due_date']->lessThanOrEqualTo($limit) && $row['due_date']->greaterThanOrEqualTo($today))
            ->sortBy(fn ($row) => $row['due_date']->timestamp)
            ->values();
    }

    /**
     * The next scheduled installment date on/after $from, based on the loan's
     * start date and repayment frequency, capped at the maturity date.
     */
    public function nextDueDate(Loan $loan, ?Carbon $from = null): ?Carbon
    {
        $from = $from ?? Carbon::today();

        if (! $loan->start_date) {
            return $loan->end_date ? $loan->end_date->copy()->startOfDay() : null;
        }

        $step = match ($loan->repayment_frequency) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };

        $date = $loan->start_date->copy()->startOfDay();
        $guard = 0;

        while ($date->lessThan($from) && $guard < 1200) {
            $date->addMonths($step);
            $guard++;
        }

        if ($loan->end_date && $date->greaterThan($loan->end_date->startOfDay())) {
            return $loan->end_date->copy()->startOfDay();
        }

        return $date;
    }

    /**
     * Outstanding loan report rows (loans with a remaining balance).
     *
     * @return Collection<int, Loan>
     */
    public function outstandingReport(int $companyId): Collection
    {
        return Loan::forCompany($companyId)
            ->with(['project', 'repayments'])
            ->orderByDesc('principal_amount')
            ->get()
            ->filter(fn (Loan $loan) => $loan->outstanding_balance > 0)
            ->values();
    }

    /**
     * All company loans eager-loaded with repayments for summary reporting.
     *
     * @return Collection<int, Loan>
     */
    public function summaryReport(int $companyId): Collection
    {
        return Loan::forCompany($companyId)
            ->with(['project', 'repayments'])
            ->orderByDesc('start_date')
            ->get();
    }

    /**
     * Full repayment ledger for the company, newest first.
     *
     * @return Collection<int, \App\Models\LoanRepayment>
     */
    public function repaymentReport(int $companyId): Collection
    {
        return \App\Models\LoanRepayment::whereHas('loan', fn ($q) => $q->where('company_id', $companyId))
            ->with('loan')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Interest expense per loan (with monthly total for the current year).
     *
     * @return Collection<int, array{loan: Loan, interest: float}>
     */
    public function interestExpenseReport(int $companyId): Collection
    {
        return Loan::forCompany($companyId)
            ->with(['project', 'repayments'])
            ->get()
            ->map(fn (Loan $loan) => [
                'loan' => $loan,
                'interest' => $loan->total_interest_paid,
            ])
            ->filter(fn ($row) => $row['interest'] > 0)
            ->sortByDesc('interest')
            ->values();
    }

    /**
     * Project-wise loan report for every project that has loans.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function projectWiseReport(int $companyId): Collection
    {
        return \App\Models\Project::where('company_id', $companyId)
            ->whereHas('loans')
            ->orderBy('name')
            ->get()
            ->map(fn (\App\Models\Project $project) => array_merge(
                ['project' => $project],
                $this->projectMetrics($project),
            ));
    }
}

