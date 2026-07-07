<?php

namespace App\Domains\Project\Controllers;

use App\Domains\Project\Requests\StoreInvestorRequest;
use App\Domains\Project\Requests\UpdateInvestorRequest;
use App\Domains\Project\Services\InvestorService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectInvestor;
use Illuminate\Http\JsonResponse;

class InvestorController extends Controller
{
    public function __construct(
        private readonly InvestorService $investorService,
    ) {}

    public function index(Company $company, Project $project): JsonResponse
    {
        $this->authorize('viewInvestors', $company);

        $investors = $this->investorService->listForProject($project);

        return response()->json(['data' => $investors]);
    }

    public function store(StoreInvestorRequest $request, Company $company, Project $project): JsonResponse
    {
        $this->authorize('manageInvestors', $company);

        $investor = $this->investorService->create($project, $request->validated());

        return response()->json(['data' => $investor], 201);
    }

    public function show(Company $company, Project $project, ProjectInvestor $investor): JsonResponse
    {
        $this->authorize('viewInvestors', $company);

        return response()->json(['data' => $investor->load('user')]);
    }

    public function update(UpdateInvestorRequest $request, Company $company, Project $project, ProjectInvestor $investor): JsonResponse
    {
        $this->authorize('manageInvestors', $company);

        $investor = $this->investorService->update($investor, $request->validated());

        return response()->json(['data' => $investor]);
    }

    public function destroy(Company $company, Project $project, ProjectInvestor $investor): JsonResponse
    {
        $this->authorize('manageInvestors', $company);

        $this->investorService->delete($investor);

        return response()->json(['message' => 'Investor removed.']);
    }
}
