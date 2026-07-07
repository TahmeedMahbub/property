<?php

namespace App\Domains\Company\Services;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;

class CompanyService
{
    public function listForUser(User $user): mixed
    {
        if ($user->isSuperAdmin()) {
            return Company::active()->latest()->paginate(20);
        }

        return $user->companies()->wherePivot('status', 'active')->paginate(20);
    }

    public function create(User $user, array $data): Company
    {
        $company = Company::create($data);

        // Creator becomes owner automatically
        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return $company->load('owners');
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function delete(Company $company): void
    {
        $company->delete();
    }
}
