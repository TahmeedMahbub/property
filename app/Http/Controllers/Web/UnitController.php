<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Unit;
use App\Models\UnitType;
use App\Services\JournalService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');
        $query = Unit::where('company_id', $company->id)->with(['building', 'floor', 'unitType', 'project']);

        if ($search = $request->get('search')) {
            $query->where('unit_number', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($buildingUuid = $request->get('building')) {
            $building = Building::where('uuid', $buildingUuid)->first();
            if ($building) {
                $query->where('building_id', $building->id);
            }
        }

        if ($floorUuid = $request->get('floor')) {
            $floor = Floor::where('uuid', $floorUuid)->first();
            if ($floor) {
                $query->where('floor_id', $floor->id);
            }
        }

        if ($typeUuid = $request->get('unit_type')) {
            $type = UnitType::where('uuid', $typeUuid)->first();
            if ($type) {
                $query->where('unit_type_id', $type->id);
            }
        }

        $units = $query->latest()->paginate(30)->withQueryString();
        $buildings = Building::where('company_id', $company->id)->orderBy('name')->get();
        $unitTypes = UnitType::where('company_id', $company->id)->active()->orderBy('name')->get();

        return view('contents.property.units.index', compact('units', 'buildings', 'unitTypes', 'company'));
    }

    public function create(Request $request)
    {
        $company = app('currentCompany');
        $buildings = Building::where('company_id', $company->id)->with('project')->orderBy('name')->get();
        $floors = Floor::where('company_id', $company->id)->orderBy('building_id')->orderBy('floor_number')->get();
        $unitTypes = UnitType::where('company_id', $company->id)->active()->orderBy('name')->get();
        return view('contents.property.units.create', compact('buildings', 'floors', 'unitTypes', 'company'));
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'floor_id' => 'required|exists:floors,uuid',
            'unit_type_id' => 'nullable|exists:unit_types,uuid',
            'unit_number' => 'required|string|max:50',
            'size' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'facing' => 'nullable|string|max:50',
            'status' => 'required|in:available,reserved,booked,sold,handovered',
            'description' => 'nullable|string',
        ]);

        $floor = Floor::where('uuid', $validated['floor_id'])->firstOrFail();
        unset($validated['floor_id']);

        if (!empty($validated['unit_type_id'])) {
            $unitType = UnitType::where('uuid', $validated['unit_type_id'])->first();
            $validated['unit_type_id'] = $unitType?->id;
        } else {
            $validated['unit_type_id'] = null;
        }

        $validated['company_id'] = $company->id;
        $validated['project_id'] = $floor->project_id;
        $validated['building_id'] = $floor->building_id;

        $unit = $floor->units()->create($validated);

        // A sold/handovered unit brings in its sale price (credit)
        JournalService::syncReference(
            companyId: $company->id,
            reference: $unit,
            targetCredit: in_array($unit->status, ['sold', 'handovered']) ? (float) ($unit->price ?? 0) : 0.0,
            category: 'unit_sale',
            remarks: 'Unit ' . $unit->unit_number . ' sale',
        );

        return redirect('/units')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        $company = app('currentCompany');
        $unitTypes = UnitType::where('company_id', $company->id)->active()->orderBy('name')->get();
        $unit->load(['building', 'floor']);
        return view('contents.property.units.edit', compact('unit', 'unitTypes', 'company'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'unit_type_id' => 'nullable|exists:unit_types,uuid',
            'unit_number' => 'required|string|max:50',
            'size' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'facing' => 'nullable|string|max:50',
            'status' => 'required|in:available,reserved,booked,sold,handovered',
            'description' => 'nullable|string',
        ]);

        if (array_key_exists('unit_type_id', $validated) && $validated['unit_type_id']) {
            $unitType = UnitType::where('uuid', $validated['unit_type_id'])->first();
            $validated['unit_type_id'] = $unitType?->id;
        } else {
            $validated['unit_type_id'] = null;
        }

        $unit->update($validated);

        // Sync ledger: credit sale price when sold/handovered, otherwise reverse
        JournalService::syncReference(
            companyId: $unit->company_id,
            reference: $unit,
            targetCredit: in_array($unit->status, ['sold', 'handovered']) ? (float) ($unit->price ?? 0) : 0.0,
            category: 'unit_sale',
            remarks: 'Unit ' . $unit->unit_number . ' status: ' . $unit->status,
        );

        return redirect('/units')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        // Reverse any sale contribution before deleting
        JournalService::reverseReference(
            companyId: $unit->company_id,
            reference: $unit,
            category: 'unit_sale',
            remarks: 'Reversal: unit ' . $unit->unit_number . ' removed',
        );

        $unit->delete();
        return redirect('/units')->with('success', 'Unit deleted successfully.');
    }
}
