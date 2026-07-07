<?php

namespace App\Domains\Project\Services;

use App\Models\Project;
use App\Models\ProjectInvestor;
use App\Models\User;

class InvestorService
{
    public function listForProject(Project $project): mixed
    {
        return $project->investors()
            ->with('user:id,uuid,name,email')
            ->latest()
            ->paginate(20);
    }

    public function create(Project $project, array $data): ProjectInvestor
    {
        if (! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        return $project->investors()->create($data);
    }

    public function update(ProjectInvestor $investor, array $data): ProjectInvestor
    {
        if (array_key_exists('user_id', $data) && ! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        $investor->update($data);

        return $investor->fresh('user');
    }

    public function delete(ProjectInvestor $investor): void
    {
        $investor->delete();
    }
}
