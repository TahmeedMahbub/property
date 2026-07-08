<?php

namespace App\Http\Controllers\Web;

use App\Domains\Loan\Requests\StoreLoanRepaymentRequest;
use App\Domains\Loan\Services\LoanService;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRepayment;

class LoanRepaymentController extends Controller
{
    public function __construct(
        private readonly LoanService $loans = new LoanService(),
    ) {}

    public function create(string $loanUuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $loanUuid)->firstOrFail();

        abort_if($loan->status === 'closed', 404);

        return view('contents.property.loans.repay', compact('loan'));
    }

    public function store(StoreLoanRepaymentRequest $request, string $loanUuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $loanUuid)->firstOrFail();

        $this->loans->recordRepayment($loan, $request->validated());

        return redirect("/loans/{$loan->uuid}")->with('success', 'Repayment recorded successfully.');
    }

    public function destroy(string $loanUuid, string $repaymentUuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $loanUuid)->firstOrFail();
        $repayment = LoanRepayment::where('loan_id', $loan->id)->where('uuid', $repaymentUuid)->firstOrFail();

        $this->loans->deleteRepayment($repayment);

        return redirect("/loans/{$loan->uuid}")->with('success', 'Repayment deleted successfully.');
    }
}
