<?php

namespace App\Http\Controllers\Web;

use App\Domains\Loan\Services\LoanReportService;
use App\Http\Controllers\Controller;

class LoanReportController extends Controller
{
    public function __construct(
        private readonly LoanReportService $reports = new LoanReportService(),
    ) {}

    public function index()
    {
        return view('contents.property.loans.reports.index');
    }

    public function show(string $type)
    {
        $company = app('currentCompany');

        $reports = [
            'summary' => 'Loan Summary Report',
            'outstanding' => 'Outstanding Loan Report',
            'repayment' => 'Loan Repayment Report',
            'interest' => 'Interest Expense Report',
            'project' => 'Project Wise Loan Report',
        ];

        abort_unless(array_key_exists($type, $reports), 404);

        $title = $reports[$type];

        $data = match ($type) {
            'summary' => $this->reports->summaryReport($company->id),
            'outstanding' => $this->reports->outstandingReport($company->id),
            'repayment' => $this->reports->repaymentReport($company->id),
            'interest' => $this->reports->interestExpenseReport($company->id),
            'project' => $this->reports->projectWiseReport($company->id),
        };

        return view("contents.property.loans.reports.{$type}", compact('data', 'title', 'type'));
    }
}
