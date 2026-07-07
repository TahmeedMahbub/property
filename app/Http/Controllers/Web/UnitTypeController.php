<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitTypeController extends Controller
{
    public function index(Request $request)
    {
        $company = $this->getCompany($request);
        $query = $company->unitTypes();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $unitTypes = $query->latest()->paginate(20)->withQueryString();

        return view('contents.property.unit-types.index', compact('unitTypes', 'company'));
    }

    public function create(Request $request)
    {
        $company = $this->getCompany($request);
        return view('contents.property.unit-types.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = $this->getCompany($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $company->unitTypes()->create($validated);

        return redirect('/unit-types')->with('success', 'Unit type created successfully.');
    }

    public function edit(UnitType $unitType)
    {
        return view('contents.property.unit-types.edit', compact('unitType'));
    }

    public function update(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $unitType->update($validated);

        return redirect('/unit-types')->with('success', 'Unit type updated successfully.');
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->delete();
        return redirect('/unit-types')->with('success', 'Unit type deleted successfully.');
    }

    private function getCompany(Request $request): Company
    {
        return app('currentCompany');
    }
}
