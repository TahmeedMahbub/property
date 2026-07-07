<?php

namespace App\Domains\Property\Services;

use App\Models\Building;
use App\Models\Floor;

class FloorService
{
    public function listForBuilding(Building $building): mixed
    {
        return $building->floors()->orderBy('floor_number')->paginate(50);
    }

    public function create(Building $building, array $data): Floor
    {
        $data['company_id'] = $building->company_id;
        $data['project_id'] = $building->project_id;

        return $building->floors()->create($data);
    }

    public function update(Floor $floor, array $data): Floor
    {
        $floor->update($data);

        return $floor->fresh();
    }

    public function delete(Floor $floor): void
    {
        $floor->delete();
    }
}
