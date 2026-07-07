<?php

namespace App\Domains\Company\Controllers;

use App\Domains\Company\Requests\StoreCompanyRequest;
use App\Domains\Company\Requests\UpdateCompanyRequest;
use App\Domains\Company\Services\CompanyService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $companies = $this->companyService->listForUser($request->user());

        return response()->json(['data' => $companies]);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->create($request->user(), $request->validated());

        return response()->json(['data' => $company], 201);
    }

    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json(['data' => $company->load('owners')]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $company = $this->companyService->update($company, $request->validated());

        return response()->json(['data' => $company]);
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->companyService->delete($company);

        return response()->json(['message' => 'Company deleted.']);
    }
}
