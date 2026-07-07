<?php

namespace App\Domains\Property\Services;

use App\Models\Building;
use App\Models\Project;
use Illuminate\Support\Str;

class BuildingService
{
    public function listForProject(Project $project): mixed
    {
        return $project->buildings()->latest()->paginate(20);
    }

    public function create(Project $project, array $data): Building
    {
        $data['slug'] = Str::slug($data['name']);
        $data['company_id'] = $project->company_id;

        return $project->buildings()->create($data);
    }

    public function update(Building $building, array $data): Building
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $building->update($data);

        return $building->fresh();
    }

    public function delete(Building $building): void
    {
        $building->delete();
    }
}
