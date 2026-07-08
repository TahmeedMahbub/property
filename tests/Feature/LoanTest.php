<?php

namespace Tests\Feature;

use App\Domains\Loan\Services\LoanReportService;
use App\Domains\Loan\Services\LoanService;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    private function makeLoan(int $companyId, array $overrides = []): Loan
    {
        return (new LoanService())->create($companyId, array_merge([
            'lender_type' => 'bank',
            'lender_name' => 'City Bank PLC',
            'principal_amount' => 1000000,
            'interest_rate' => 10,
            'interest_type' => 'flat',
            'start_date' => '2025-01-01',
            'end_date' => '2028-01-01',
            'repayment_frequency' => 'monthly',
            'status' => 'active',
        ], $overrides));
    }

    public function test_loan_balance_and_totals_are_computed_from_repayments(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $loan = $this->makeLoan($company->id);

        $service->recordRepayment($loan, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 100000,
            'interest_paid' => 8000,
            'penalty' => 0,
            'payment_method' => 'bank_transfer',
        ]);
        $service->recordRepayment($loan, [
            'payment_date' => '2025-03-01',
            'principal_paid' => 100000,
            'interest_paid' => 8000,
            'penalty' => 500,
            'payment_method' => 'cash',
        ]);

        $loan->refresh()->load('repayments');

        $this->assertEqualsWithDelta(200000.0, $loan->total_principal_paid, 0.01);
        $this->assertEqualsWithDelta(16000.0, $loan->total_interest_paid, 0.01);
        $this->assertEqualsWithDelta(800000.0, $loan->outstanding_balance, 0.01);
        $this->assertEqualsWithDelta(216500.0, $loan->total_paid, 0.01);
    }

    public function test_loan_auto_closes_when_fully_repaid(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $loan = $this->makeLoan($company->id, ['principal_amount' => 500000]);

        $service->recordRepayment($loan, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 500000,
            'interest_paid' => 20000,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertSame('closed', $loan->refresh()->status);
        $this->assertEqualsWithDelta(0.0, $loan->load('repayments')->outstanding_balance, 0.01);
    }

    public function test_deleting_a_repayment_reopens_the_loan(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $loan = $this->makeLoan($company->id, ['principal_amount' => 500000]);

        $repayment = $service->recordRepayment($loan, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 500000,
            'interest_paid' => 0,
            'payment_method' => 'bank_transfer',
        ]);
        $this->assertSame('closed', $loan->refresh()->status);

        $service->deleteRepayment($repayment);
        $this->assertSame('active', $loan->refresh()->status);
    }

    public function test_defaulted_loan_status_is_preserved(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $loan = $this->makeLoan($company->id, ['status' => 'defaulted']);

        $service->recordRepayment($loan, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 1000000,
            'interest_paid' => 0,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertSame('defaulted', $loan->refresh()->status);
    }

    public function test_company_metrics_aggregate_across_loans(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $reports = new LoanReportService();

        $bank = $this->makeLoan($company->id, ['lender_type' => 'bank', 'principal_amount' => 1000000]);
        $director = $this->makeLoan($company->id, ['lender_type' => 'director', 'principal_amount' => 500000]);

        $service->recordRepayment($bank, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 200000,
            'interest_paid' => 15000,
            'payment_method' => 'bank_transfer',
        ]);

        $metrics = $reports->companyMetrics($company->id);

        $this->assertEqualsWithDelta(1500000.0, $metrics['total_principal_borrowed'], 0.01);
        $this->assertEqualsWithDelta(200000.0, $metrics['total_principal_repaid'], 0.01);
        $this->assertEqualsWithDelta(15000.0, $metrics['total_interest_paid'], 0.01);
        $this->assertEqualsWithDelta(1300000.0, $metrics['total_outstanding'], 0.01);
        $this->assertSame(2, $metrics['loan_count']);
    }

    public function test_outstanding_report_excludes_fully_paid_loans(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new LoanService();
        $reports = new LoanReportService();

        $open = $this->makeLoan($company->id, ['principal_amount' => 1000000]);
        $paid = $this->makeLoan($company->id, ['principal_amount' => 300000]);

        $service->recordRepayment($paid, [
            'payment_date' => '2025-02-01',
            'principal_paid' => 300000,
            'interest_paid' => 0,
            'payment_method' => 'cash',
        ]);

        $rows = $reports->outstandingReport($company->id);

        $this->assertCount(1, $rows);
        $this->assertSame($open->id, $rows->first()->id);
    }

    public function test_next_due_date_follows_repayment_frequency(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $reports = new LoanReportService();
        $loan = $this->makeLoan($company->id, [
            'start_date' => '2025-01-10',
            'end_date' => '2027-01-10',
            'repayment_frequency' => 'quarterly',
        ]);

        $due = $reports->nextDueDate($loan, \Carbon\Carbon::parse('2025-02-01'));

        // First quarterly step after 10 Jan that is on/after 1 Feb is 10 Apr 2025.
        $this->assertSame('2025-04-10', $due->format('Y-m-d'));
    }
}
