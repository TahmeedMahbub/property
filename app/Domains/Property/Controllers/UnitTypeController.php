<?php

namespace App\Domains\Property\Controllers;

use App\Domains\Property\Requests\StoreUnitTypeRequest;
use App\Domains\Property\Requests\UpdateUnitTypeRequest;
use App\Domains\Property\Services\UnitTypeService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Http\JsonResponse;

class UnitTypeController extends Controller
{
    public function __construct(
        private readonly UnitTypeService $service,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        $unitTypes = $this->service->listForCompany($company);

        return response()->json(['data' => $unitTypes]);
    }

    public function store(StoreUnitTypeRequest $request, Company $company): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $unitType = $this->service->create($company, $request->validated());

        return response()->json(['data' => $unitType], 201);
    }

    public function show(Company $company, UnitType $unitType): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        return response()->json(['data' => $unitType->load('units')]);
    }

    public function update(UpdateUnitTypeRequest $request, Company $company, UnitType $unitType): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $unitType = $this->service->update($unitType, $request->validated());

        return response()->json(['data' => $unitType]);
    }

    public function destroy(Company $company, UnitType $unitType): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $this->service->delete($unitType);

        return response()->json(['message' => 'Unit type deleted.']);
    }
}
