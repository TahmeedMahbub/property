<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuildingController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');
        $query = Building::where('company_id', $company->id)->with('project');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($projectId = $request->get('project')) {
            $project = Project::where('uuid', $projectId)->first();
            if ($project) {
                $query->where('project_id', $project->id);
            }
        }

        $buildings = $query->latest()->paginate(20)->withQueryString();
        $projects = $company->projects()->orderBy('name')->get();

        return view('contents.property.buildings.index', compact('buildings', 'projects', 'company'));
    }

    public function create(Request $request)
    {
        $company = app('currentCompany');
        $projects = $company->projects()->orderBy('name')->get();
        return view('contents.property.buildings.create', compact('projects', 'company'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_floors' => 'nullable|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:planning,under_construction,completed,handed_over',
        ]);

        $project = Project::where('uuid', $validated['project_id'])->firstOrFail();
        unset($validated['project_id']);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['company_id'] = $company->id;

        $project->buildings()->create($validated);

        return redirect('/buildings')->with('success', 'Building created successfully.');
    }

    public function edit(Building $building)
    {
        $company = app('currentCompany');
        $projects = $company->projects()->orderBy('name')->get();
        return view('contents.property.buildings.edit', compact('building', 'projects', 'company'));
    }

    public function update(Request $request, Building $building)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_floors' => 'nullable|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:planning,under_construction,completed,handed_over',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $building->update($validated);

        return redirect('/buildings')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect('/buildings')->with('success', 'Building deleted successfully.');
    }
}
