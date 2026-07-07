<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectInvestor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_owner_can_list_projects(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        Project::factory()->count(3)->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_owner_can_create_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects", [
            'name' => 'Skyline Tower',
            'description' => 'A 20-story residential building',
            'type' => 'residential',
            'budget' => 50000000,
            'start_date' => '2026-01-01',
            'end_date' => '2028-12-31',
            'location' => 'Gulshan',
            'city' => 'Dhaka',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Skyline Tower']);

        $this->assertDatabaseHas('projects', [
            'company_id' => $company->id,
            'name' => 'Skyline Tower',
            'slug' => 'skyline-tower',
        ]);
    }

    public function test_owner_can_update_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}", [
            'status' => 'active',
            'budget' => 75000000,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'active',
        ]);
    }

    public function test_owner_can_delete_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/projects/{$project->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_member_can_create_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member] = $this->createMemberWithRole($company, 'member');

        $response = $this->apiAs($member, 'POST', "/api/companies/{$company->uuid}/projects", [
            'name' => 'Member Project',
            'type' => 'commercial',
        ]);

        $response->assertStatus(201);
    }

    public function test_viewer_cannot_create_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/projects", [
            'name' => 'Viewer Project',
        ]);

        $response->assertStatus(403);
    }

    public function test_viewer_can_view_projects(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        Project::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/projects");

        $response->assertOk();
    }

    public function test_project_show_includes_investors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);
        ProjectInvestor::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}");

        $response->assertOk()
            ->assertJsonCount(2, 'data.investors');
    }

    // ─── Investor Tests ──────────────────────────────────────────

    public function test_owner_can_list_investors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);
        ProjectInvestor::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_owner_can_add_investor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors", [
            'name' => 'Investor One',
            'email' => 'investor@example.com',
            'investment_amount' => 10000000,
            'investment_percentage' => 20,
            'investment_type' => 'equity',
            'invested_at' => '2026-01-15',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Investor One']);

        $this->assertDatabaseHas('project_investors', [
            'project_id' => $project->id,
            'name' => 'Investor One',
        ]);
    }

    public function test_owner_can_add_investor_linked_to_user(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $investorUser = User::factory()->create();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors", [
            'user_id' => $investorUser->uuid,
            'name' => $investorUser->name,
            'investment_amount' => 5000000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('project_investors', [
            'project_id' => $project->id,
            'user_id' => $investorUser->id,
        ]);
    }

    public function test_owner_can_update_investor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $investor = ProjectInvestor::factory()->create(['project_id' => $project->id]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors/{$investor->uuid}", [
            'investment_amount' => 20000000,
            'status' => 'active',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('project_investors', [
            'id' => $investor->id,
            'investment_amount' => 20000000,
        ]);
    }

    public function test_owner_can_delete_investor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $investor = ProjectInvestor::factory()->create(['project_id' => $project->id]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors/{$investor->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('project_investors', ['id' => $investor->id]);
    }

    public function test_viewer_cannot_manage_investors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        $project = Project::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/investors", [
            'name' => 'Unauthorized Investor',
            'investment_amount' => 999,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_access_projects(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = User::factory()->create();

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}/projects");

        $response->assertStatus(403);
    }
}
