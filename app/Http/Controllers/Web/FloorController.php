<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');
        $query = Floor::where('company_id', $company->id)->with(['building', 'project']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($buildingUuid = $request->get('building')) {
            $building = Building::where('uuid', $buildingUuid)->first();
            if ($building) {
                $query->where('building_id', $building->id);
            }
        }

        $floors = $query->orderBy('building_id')->orderBy('floor_number')->paginate(30)->withQueryString();
        $buildings = Building::where('company_id', $company->id)->orderBy('name')->get();

        return view('contents.property.floors.index', compact('floors', 'buildings', 'company'));
    }

    public function create(Request $request)
    {
        $company = app('currentCompany');
        $buildings = Building::where('company_id', $company->id)->with('project')->orderBy('name')->get();
        return view('contents.property.floors.create', compact('buildings', 'company'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,uuid',
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'total_units' => 'nullable|integer|min:0',
        ]);

        $building = Building::where('uuid', $validated['building_id'])->firstOrFail();
        unset($validated['building_id']);

        $validated['company_id'] = $company->id;
        $validated['project_id'] = $building->project_id;

        $building->floors()->create($validated);

        return redirect('/floors')->with('success', 'Floor created successfully.');
    }

    public function edit(Floor $floor)
    {
        $company = app('currentCompany');
        $buildings = Building::where('company_id', $company->id)->with('project')->orderBy('name')->get();
        return view('contents.property.floors.edit', compact('floor', 'buildings', 'company'));
    }

    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'total_units' => 'nullable|integer|min:0',
        ]);

        $floor->update($validated);

        return redirect('/floors')->with('success', 'Floor updated successfully.');
    }

    public function destroy(Floor $floor)
    {
        $floor->delete();
        return redirect('/floors')->with('success', 'Floor deleted successfully.');
    }
}
