<?php

namespace App\Domains\Property\Controllers;

use App\Domains\Property\Requests\StoreUnitRequest;
use App\Domains\Property\Requests\UpdateUnitRequest;
use App\Domains\Property\Services\UnitService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $service,
    ) {}

    public function index(Request $request, Company $company, Project $project): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        $units = $this->service->listForProject($project, $request->only([
            'building_id', 'floor_id', 'status', 'unit_type_id',
        ]));

        return response()->json(['data' => $units]);
    }

    public function store(StoreUnitRequest $request, Company $company, Project $project, Floor $floor): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $data = $request->validated();

        // Resolve unit_type_id from UUID
        if (! empty($data['unit_type_id'])) {
            $unitType = UnitType::where('uuid', $data['unit_type_id'])->first();
            $data['unit_type_id'] = $unitType?->id;
        }

        $unit = $this->service->create($floor, $data);

        return response()->json(['data' => $unit->load(['building', 'floor', 'unitType'])], 201);
    }

    public function show(Company $company, Project $project, Unit $unit): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        return response()->json(['data' => $unit->load(['building', 'floor', 'unitType'])]);
    }

    public function update(UpdateUnitRequest $request, Company $company, Project $project, Unit $unit): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $data = $request->validated();

        // Resolve unit_type_id from UUID
        if (array_key_exists('unit_type_id', $data) && $data['unit_type_id']) {
            $unitType = UnitType::where('uuid', $data['unit_type_id'])->first();
            $data['unit_type_id'] = $unitType?->id;
        }

        $unit = $this->service->update($unit, $data);

        return response()->json(['data' => $unit->load(['building', 'floor', 'unitType'])]);
    }

    public function destroy(Company $company, Project $project, Unit $unit): JsonResponse
    {
        $this->authorize('deleteProperties', $company);

        $this->service->delete($unit);

        return response()->json(['message' => 'Unit deleted.']);
    }
}
