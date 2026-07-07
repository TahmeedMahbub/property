<?php

namespace App\Domains\Property\Services;

use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;

class UnitService
{
    public function listForProject(Project $project, array $filters = []): mixed
    {
        $query = $project->units()->with(['building', 'floor', 'unitType']);

        if (! empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        if (! empty($filters['floor_id'])) {
            $query->where('floor_id', $filters['floor_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['unit_type_id'])) {
            $query->where('unit_type_id', $filters['unit_type_id']);
        }

        return $query->latest()->paginate(50);
    }

    public function create(Floor $floor, array $data): Unit
    {
        $data['company_id'] = $floor->company_id;
        $data['project_id'] = $floor->project_id;
        $data['building_id'] = $floor->building_id;

        return $floor->units()->create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }
}
