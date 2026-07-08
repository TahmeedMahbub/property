<?php

namespace App\Http\Controllers\Web;

use App\Domains\Loan\Requests\StoreLoanRequest;
use App\Domains\Loan\Requests\UpdateLoanRequest;
use App\Domains\Loan\Services\LoanReportService;
use App\Domains\Loan\Services\LoanService;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Project;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        private readonly LoanService $loans = new LoanService(),
        private readonly LoanReportService $reports = new LoanReportService(),
    ) {}

    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Loan::forCompany($company->id)->with('project')->withSum('repayments as principal_repaid', 'principal_paid');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('lender_name', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('lender_type')) {
            $query->where('lender_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $loans = $query->latest()->paginate(15)->withQueryString();
        $metrics = $this->reports->companyMetrics($company->id);

        return view('contents.property.loans.index', compact('loans', 'metrics'));
    }

    public function create()
    {
        $company = app('currentCompany');
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.loans.create', compact('projects'));
    }

    public function store(StoreLoanRequest $request)
    {
        $company = app('currentCompany');
        $data = $request->validated();
        $data['project_id'] = $this->resolveProjectId($company->id, $data['project_id'] ?? null);

        $this->loans->create($company->id, $data);

        return redirect('/loans')->with('success', 'Loan recorded successfully.');
    }

    public function show(string $uuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)
            ->with(['project', 'creator', 'repayments' => fn ($q) => $q->latest('payment_date')->latest('id')])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $nextDue = $this->reports->nextDueDate($loan);

        return view('contents.property.loans.show', compact('loan', 'nextDue'));
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.loans.edit', compact('loan', 'projects'));
    }

    public function update(UpdateLoanRequest $request, string $uuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $data = $request->validated();
        $data['project_id'] = $this->resolveProjectId($company->id, $data['project_id'] ?? null);

        $this->loans->update($loan, $data);

        return redirect('/loans')->with('success', 'Loan updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $loan = Loan::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $this->loans->delete($loan);

        return redirect('/loans')->with('success', 'Loan deleted successfully.');
    }

    /**
     * Translate a project UUID from the form into the internal id, scoped to the company.
     */
    private function resolveProjectId(int $companyId, ?string $projectUuid): ?int
    {
        if (! $projectUuid) {
            return null;
        }

        return Project::where('company_id', $companyId)->where('uuid', $projectUuid)->value('id');
    }
}
