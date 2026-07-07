<?php

namespace App\Domains\Company\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'company.view');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'company.update');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'company.delete');
    }

    public function viewMembers(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'members.view');
    }

    public function manageMembers(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'members.manage');
    }

    public function viewShareholders(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'shareholders.view');
    }

    public function manageShareholders(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'shareholders.manage');
    }

    public function viewProjects(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'projects.view');
    }

    public function createProjects(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'projects.create');
    }

    public function updateProjects(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'projects.update');
    }

    public function deleteProjects(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'projects.delete');
    }

    public function viewInvestors(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'investors.view');
    }

    public function manageInvestors(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'investors.manage');
    }

    // ─── Property Permissions ────────────────────────────────────

    public function viewProperties(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'properties.view');
    }

    public function manageProperties(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'properties.manage');
    }

    public function deleteProperties(User $user, Company $company): bool
    {
        return $user->hasPermissionIn($company, 'properties.delete');
    }
}
