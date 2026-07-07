<?php

namespace App\Domains\Project\Controllers;

use App\Domains\Project\Requests\StoreProjectRequest;
use App\Domains\Project\Requests\UpdateProjectRequest;
use App\Domains\Project\Services\ProjectService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('viewProjects', $company);

        $projects = $this->projectService->listForCompany($company);

        return response()->json(['data' => $projects]);
    }

    public function store(StoreProjectRequest $request, Company $company): JsonResponse
    {
        $this->authorize('createProjects', $company);

        $project = $this->projectService->create($company, $request->validated());

        return response()->json(['data' => $project], 201);
    }

    public function show(Company $company, Project $project): JsonResponse
    {
        $this->authorize('viewProjects', $company);

        return response()->json(['data' => $project->load('investors')]);
    }

    public function update(UpdateProjectRequest $request, Company $company, Project $project): JsonResponse
    {
        $this->authorize('updateProjects', $company);

        $project = $this->projectService->update($project, $request->validated());

        return response()->json(['data' => $project]);
    }

    public function destroy(Company $company, Project $project): JsonResponse
    {
        $this->authorize('deleteProjects', $company);

        $this->projectService->delete($project);

        return response()->json(['message' => 'Project deleted.']);
    }
}
