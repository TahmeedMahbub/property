<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_owner_can_list_their_companies(): void
    {
        [$user, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($user, 'GET', '/api/companies');

        $response->assertOk()
            ->assertJsonFragment(['uuid' => $company->uuid]);
    }

    public function test_super_admin_can_list_all_companies(): void
    {
        $this->seedPermissions();
        $admin = $this->createSuperAdmin();
        Company::factory()->count(3)->create();

        $response = $this->apiAs($admin, 'GET', '/api/companies');

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_user_can_create_company(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create();

        $response = $this->apiAs($user, 'POST', '/api/companies', [
            'name' => 'New Company',
            'type' => 'real_estate',
            'currency' => 'BDT',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Company']);

        // Creator should be owner
        $this->assertDatabaseHas('company_memberships', [
            'user_id' => $user->id,
            'is_owner' => true,
        ]);
    }

    public function test_owner_can_view_company(): void
    {
        [$user, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($user, 'GET', "/api/companies/{$company->uuid}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $company->name]);
    }

    public function test_owner_can_update_company(): void
    {
        [$user, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($user, 'PUT', "/api/companies/{$company->uuid}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_owner_can_delete_company(): void
    {
        [$user, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($user, 'DELETE', "/api/companies/{$company->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_viewer_cannot_update_company(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');

        $response = $this->apiAs($viewer, 'PUT', "/api/companies/{$company->uuid}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_view_company(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = User::factory()->create();

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}");

        $response->assertStatus(403);
    }

    public function test_company_context_resolves_from_header(): void
    {
        [$user, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs(
            $user,
            'GET',
            "/api/companies/{$company->uuid}/members",
            [],
            ['X-Company-Id' => $company->uuid],
        );

        $response->assertOk();
    }

    public function test_company_context_required_middleware_rejects_missing_context(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create();

        // Try accessing company-scoped route without membership
        $company = Company::factory()->create();

        $response = $this->apiAs($user, 'GET', "/api/companies/{$company->uuid}/members");

        $response->assertStatus(403);
    }
}
