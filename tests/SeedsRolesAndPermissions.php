<?php

namespace Tests;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

trait SeedsRolesAndPermissions
{
    protected function seedPermissions(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    protected function createOwnerWithCompany(): array
    {
        $this->seedPermissions();

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $adminRole = Role::where('slug', 'admin')->whereNull('company_id')->first();

        $membership = CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $adminRole->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return [$user, $company, $membership];
    }

    protected function createMemberWithRole(Company $company, string $roleSlug): array
    {
        $user = User::factory()->create();
        $role = Role::where('slug', $roleSlug)->whereNull('company_id')->first();

        $membership = CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role?->id,
            'is_owner' => false,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        return [$user, $membership];
    }

    protected function createSuperAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    protected function apiAs(User $user, string $method, string $uri, array $data = [], array $headers = [])
    {
        return $this->actingAs($user, 'sanctum')->json($method, $uri, $data, $headers);
    }
}
