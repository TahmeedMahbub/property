<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = $company->projects()->latest();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projects = $query->paginate(15)->withQueryString();

        return view('contents.property.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('contents.property.projects.create');
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $company->projects()->create($validated);

        return redirect('/projects')->with('success', 'Project created successfully.');
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $project = $company->projects()->where('uuid', $uuid)->firstOrFail();

        return view('contents.property.projects.edit', compact('project'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $project = $company->projects()->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $project->update($validated);

        return redirect('/projects')->with('success', 'Project updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $project = $company->projects()->where('uuid', $uuid)->firstOrFail();
        $project->delete();

        return redirect('/projects')->with('success', 'Project deleted successfully.');
    }
}
