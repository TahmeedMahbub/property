<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses = new ExpenseService(),
    ) {}

    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Expense::forCompany($company->id)->with(['expenseCategory', 'expensable']);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($source = $request->input('source')) {
            $source === 'company'
                ? $query->where(fn ($q) => $q->whereNull('expensable_type')->orWhere('expensable_type', Company::class))
                : $query->where('expensable_type', $this->sourceClass($source));
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->input('date_to'));
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(15)->withQueryString();
        $metrics = $this->metrics($company->id);
        $categories = ExpenseCategory::forCompany($company->id)->active()->orderBy('name')->get();

        return view('contents.property.expenses.index', compact('expenses', 'metrics', 'categories'));
    }

    public function create()
    {
        $company = app('currentCompany');
        $categories = ExpenseCategory::forCompany($company->id)->active()->orderBy('name')->get();
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.expenses.create', compact('categories', 'projects'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');
        $validated = $this->validateExpense($request, $company->id);

        [$data, $expensable] = $this->resolveSource($validated, $company);
        $this->expenses->record($company->id, $data, $expensable);

        return redirect('/expenses')->with('success', 'Expense recorded successfully.');
    }

    public function show(string $uuid)
    {
        $company = app('currentCompany');
        $expense = Expense::forCompany($company->id)
            ->with(['expenseCategory', 'creator', 'expensable', 'journalEntries'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('contents.property.expenses.show', compact('expense'));
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $expense = Expense::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();
        $categories = ExpenseCategory::forCompany($company->id)->active()->orderBy('name')->get();
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.expenses.edit', compact('expense', 'categories', 'projects'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $expense = Expense::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $validated = $this->validateExpense($request, $company->id);
        $validated['category'] = ExpenseCategory::findOrFail($validated['category_id'])->slug;

        // Only company-level expenses may change their source here; expenses that
        // were created from another module (plot, booking, …) keep their link.
        if (! $expense->expensable_type || $expense->expensable_type === Company::class) {
            [$validated, $expensable] = $this->resolveSource($validated, $company);
            $validated['expensable_type'] = $expensable?->getMorphClass();
            $validated['expensable_id'] = $expensable?->getKey();
        } else {
            unset($validated['source'], $validated['project_id']);
        }

        $this->expenses->update($expense, $validated);

        return redirect('/expenses')->with('success', 'Expense updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $expense = Expense::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $this->expenses->delete($expense);

        return redirect('/expenses')->with('success', 'Expense deleted successfully.');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Today / this month / total expense figures for the summary cards.
     *
     * @return array<string, float>
     */
    private function metrics(int $companyId): array
    {
        $today = Carbon::today();

        $row = Expense::forCompany($companyId)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN expense_date = ? THEN amount ELSE 0 END), 0) as today_amount,
                COALESCE(SUM(CASE WHEN expense_date >= ? THEN amount ELSE 0 END), 0) as month_amount,
                COALESCE(SUM(amount), 0) as total_amount
            ', [$today->toDateString(), $today->copy()->startOfMonth()->toDateString()])
            ->first();

        return [
            'today' => round((float) $row->today_amount, 2),
            'month' => round((float) $row->month_amount, 2),
            'total' => round((float) $row->total_amount, 2),
        ];
    }

    private function sourceClass(string $source): string
    {
        return [
            'plot' => \App\Models\Plot::class,
            'project' => Project::class,
            'booking' => \App\Models\PlotBooking::class,
        ][$source] ?? Company::class;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateExpense(Request $request, int $companyId): array
    {
        return $request->validate([
            'category_id' => ['required', Rule::exists('p_expense_categories', 'id')->where(fn ($q) => $q->where(fn ($w) => $w->where('company_id', $companyId)->orWhereNull('company_id')))],
            'title' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', Rule::in(['company', 'project'])],
            'project_id' => ['nullable', 'string'],
        ]);
    }

    /**
     * Resolve the chosen source into a model and set the category slug.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: \Illuminate\Database\Eloquent\Model}
     */
    private function resolveSource(array $data, Company $company): array
    {
        $data['category'] = ExpenseCategory::findOrFail($data['category_id'])->slug;

        $source = $data['source'] ?? 'company';
        $projectUuid = $data['project_id'] ?? null;
        unset($data['source'], $data['project_id']);

        $expensable = $source === 'project' && $projectUuid
            ? $company->projects()->where('uuid', $projectUuid)->first()
            : null;

        return [$data, $expensable ?? $company];
    }
}
