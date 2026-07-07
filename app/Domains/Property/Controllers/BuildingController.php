<?php

namespace App\Domains\Property\Controllers;

use App\Domains\Property\Requests\StoreBuildingRequest;
use App\Domains\Property\Requests\UpdateBuildingRequest;
use App\Domains\Property\Services\BuildingService;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class BuildingController extends Controller
{
    public function __construct(
        private readonly BuildingService $service,
    ) {}

    public function index(Company $company, Project $project): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        $buildings = $this->service->listForProject($project);

        return response()->json(['data' => $buildings]);
    }

    public function store(StoreBuildingRequest $request, Company $company, Project $project): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $building = $this->service->create($project, $request->validated());

        return response()->json(['data' => $building], 201);
    }

    public function show(Company $company, Project $project, Building $building): JsonResponse
    {
        $this->authorize('viewProperties', $company);

        return response()->json(['data' => $building->load(['floors', 'units'])]);
    }

    public function update(UpdateBuildingRequest $request, Company $company, Project $project, Building $building): JsonResponse
    {
        $this->authorize('manageProperties', $company);

        $building = $this->service->update($building, $request->validated());

        return response()->json(['data' => $building]);
    }

    public function destroy(Company $company, Project $project, Building $building): JsonResponse
    {
        $this->authorize('deleteProperties', $company);

        $this->service->delete($building);

        return response()->json(['message' => 'Building deleted.']);
    }
}
