<?php

namespace Tests\Feature;

use App\Models\CompanyMembership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_owner_can_list_members(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member] = $this->createMemberWithRole($company, 'member');

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/members");

        $response->assertOk();
        // Owner + member = 2
        $this->assertGreaterThanOrEqual(2, count($response->json('data.data')));
    }

    public function test_owner_can_add_member(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $newUser = User::factory()->create();
        $role = Role::where('slug', 'member')->whereNull('company_id')->first();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/members", [
            'user_id' => $newUser->uuid,
            'role_id' => $role->id,
            'title' => 'Developer',
            'department' => 'Engineering',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('company_memberships', [
            'company_id' => $company->id,
            'user_id' => $newUser->id,
            'status' => 'active',
        ]);
    }

    public function test_owner_can_update_member_role(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member, $membership] = $this->createMemberWithRole($company, 'member');
        $managerRole = Role::where('slug', 'manager')->whereNull('company_id')->first();

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/members/{$membership->id}", [
            'role_id' => $managerRole->id,
            'title' => 'Senior Developer',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('company_memberships', [
            'id' => $membership->id,
            'role_id' => $managerRole->id,
            'title' => 'Senior Developer',
        ]);
    }

    public function test_owner_can_remove_member(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member, $membership] = $this->createMemberWithRole($company, 'member');

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/members/{$membership->id}");

        $response->assertOk();
        $this->assertDatabaseHas('company_memberships', [
            'id' => $membership->id,
            'status' => 'inactive',
        ]);
    }

    public function test_viewer_cannot_manage_members(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        $newUser = User::factory()->create();

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/members", [
            'user_id' => $newUser->uuid,
        ]);

        $response->assertStatus(403);
    }

    public function test_member_can_view_members(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member] = $this->createMemberWithRole($company, 'member');

        $response = $this->apiAs($member, 'GET', "/api/companies/{$company->uuid}/members");

        $response->assertOk();
    }

    public function test_duplicate_membership_is_rejected(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member] = $this->createMemberWithRole($company, 'member');

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/members", [
            'user_id' => $member->uuid,
        ]);

        // Should fail due to unique constraint
        $response->assertStatus(500);
    }
}
