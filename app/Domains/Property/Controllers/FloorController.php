<?php

namespace App\Domains\Property\Controllers;

use App\Domains\Property\Requests\StoreFloorRequest;
use App\Domains\Property\Requests\UpdateFloorRequest;
use App\Domains\Property\Services\FloorService;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class FloorController extends Controller
{
    public function __construct(
        private readonly FloorService $service,
    ) {}

    public function index(Company $company, Project $project, Building $building): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        $floors = $this->service->listForBuilding($building);

        return response()->json(['data' => $floors]);
    }

    public function store(StoreFloorRequest $request, Company $company, Project $project, Building $building): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $floor = $this->service->create($building, $request->validated());

        return response()->json(['data' => $floor], 201);
    }

    public function show(Company $company, Project $project, Building $building, Floor $floor): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        return response()->json(['data' => $floor->load('units')]);
    }

    public function update(UpdateFloorRequest $request, Company $company, Project $project, Building $building, Floor $floor): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $floor = $this->service->update($floor, $request->validated());

        return response()->json(['data' => $floor]);
    }

    public function destroy(Company $company, Project $project, Building $building, Floor $floor): JsonResponse
    {
        $this->authorize('deleteProperties', $company);

        $this->service->delete($floor);

        return response()->json(['message' => 'Floor deleted.']);
    }
}
