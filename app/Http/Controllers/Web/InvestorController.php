<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectInvestor;
use App\Services\JournalService;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');
        $projectIds = $company->projects()->pluck('id');

        $query = ProjectInvestor::whereIn('project_id', $projectIds)->with('project');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($projectUuid = $request->input('project')) {
            $project = $company->projects()->where('uuid', $projectUuid)->first();
            if ($project) {
                $query->where('project_id', $project->id);
            }
        }

        $investors = $query->latest()->paginate(15)->withQueryString();
        $projects = $company->projects()->orderBy('name')->get();

        // Calculate each investor's share of their project's total active investment.
        $projectIdsOnPage = $investors->pluck('project_id')->unique()->all();
        $projectTotals = ProjectInvestor::whereIn('project_id', $projectIdsOnPage)
            ->where('status', 'active')
            ->selectRaw('project_id, COALESCE(SUM(investment_amount), 0) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $investors->getCollection()->transform(function ($investor) use ($projectTotals) {
            $total = (float) ($projectTotals[$investor->project_id] ?? 0);
            $investor->calculated_percentage = ($total > 0 && $investor->status === 'active')
                ? round(((float) $investor->investment_amount / $total) * 100, 6)
                : 0.0;

            return $investor;
        });

        return view('contents.property.investors.index', compact('investors', 'projects'));
    }

    public function create()
    {
        $company = app('currentCompany');
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.investors.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'project_id' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'investment_amount' => 'nullable|numeric|min:0',
            'investment_type' => 'nullable|string|in:equity,debt,mezzanine',
            'invested_at' => 'nullable|date',
            'expected_return' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,exited',
        ]);

        $project = $company->projects()->where('uuid', $validated['project_id'])->firstOrFail();
        $validated['project_id'] = $project->id;

        $investor = ProjectInvestor::create($validated);

        // Record investment as money-in (credit)
        JournalService::syncReference(
            companyId: $company->id,
            reference: $investor,
            targetCredit: (float) ($investor->investment_amount ?? 0),
            category: 'investment',
            remarks: 'Investment from ' . $investor->name . ' (' . $project->name . ')',
        );

        return redirect('/investors')->with('success', 'Investor added successfully.');
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $projectIds = $company->projects()->pluck('id');
        $investor = ProjectInvestor::whereIn('project_id', $projectIds)
            ->where('uuid', $uuid)->with('project')->firstOrFail();

        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.investors.edit', compact('investor', 'projects'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $projectIds = $company->projects()->pluck('id');
        $investor = ProjectInvestor::whereIn('project_id', $projectIds)
            ->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'investment_amount' => 'nullable|numeric|min:0',
            'investment_type' => 'nullable|string|in:equity,debt,mezzanine',
            'invested_at' => 'nullable|date',
            'expected_return' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,exited',
        ]);

        $investor->update($validated);

        // Keep the ledger in sync with the (possibly changed) investment amount
        JournalService::syncReference(
            companyId: $company->id,
            reference: $investor,
            targetCredit: (float) ($investor->investment_amount ?? 0),
            category: 'investment',
            remarks: 'Investment adjustment for ' . $investor->name,
        );

        return redirect('/investors')->with('success', 'Investor updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $projectIds = $company->projects()->pluck('id');
        $investor = ProjectInvestor::whereIn('project_id', $projectIds)
            ->where('uuid', $uuid)->firstOrFail();

        // Reverse the investment contribution before deleting
        JournalService::reverseReference(
            companyId: $company->id,
            reference: $investor,
            category: 'investment',
            remarks: 'Reversal: investor ' . $investor->name . ' removed',
        );

        $investor->delete();

        return redirect('/investors')->with('success', 'Investor deleted successfully.');
    }
}
