<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class ContextSwitchingTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_user_can_switch_between_companies(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create();

        // Create two companies with same user as member
        $company1 = Company::factory()->create(['name' => 'Company Alpha']);
        $company2 = Company::factory()->create(['name' => 'Company Beta']);

        CompanyMembership::create([
            'company_id' => $company1->id,
            'user_id' => $user->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        CompanyMembership::create([
            'company_id' => $company2->id,
            'user_id' => $user->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        // Access company 1
        $response1 = $this->apiAs($user, 'GET', "/api/companies/{$company1->uuid}/members");
        $response1->assertOk();

        // Switch to company 2
        $response2 = $this->apiAs($user, 'GET', "/api/companies/{$company2->uuid}/members");
        $response2->assertOk();
    }

    public function test_user_sees_all_companies_they_belong_to(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        CompanyMembership::create([
            'company_id' => $company1->id,
            'user_id' => $user->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        CompanyMembership::create([
            'company_id' => $company2->id,
            'user_id' => $user->id,
            'is_owner' => false,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        $response = $this->apiAs($user, 'GET', '/api/companies');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_inactive_membership_denies_access(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create();
        $company = Company::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'is_owner' => false,
            'joined_at' => now(),
            'status' => 'inactive',
        ]);

        $response = $this->apiAs($user, 'GET', "/api/companies/{$company->uuid}/members");

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_any_company_without_membership(): void
    {
        $this->seedPermissions();
        $admin = $this->createSuperAdmin();
        $company = Company::factory()->create();

        $response = $this->apiAs($admin, 'GET', "/api/companies/{$company->uuid}/members");

        $response->assertOk();
    }

    public function test_x_company_id_header_works_for_context(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $response = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Company-Id', $company->uuid)
            ->getJson("/api/companies/{$company->uuid}/members");

        $response->assertOk();
    }

    public function test_login_returns_available_companies(): void
    {
        $this->seedPermissions();
        $user = User::factory()->create(['password' => 'password123']);
        $company = Company::factory()->create();

        CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'is_owner' => true,
            'joined_at' => now(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data.companies');
    }
}
