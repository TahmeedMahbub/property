<?php

namespace App\Domains\Project\Services;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Support\Str;

class ProjectService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->projects()->latest()->paginate(20);
    }

    public function create(Company $company, array $data): Project
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $company->projects()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
