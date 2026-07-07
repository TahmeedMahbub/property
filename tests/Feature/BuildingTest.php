<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    private function createProjectForCompany($company): Project
    {
        return Project::factory()->create(['company_id' => $company->id]);
    }

    public function test_owner_can_list_buildings(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = $this->createProjectForCompany($company);
        Building::factory()->count(3)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_owner_can_create_building(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = $this->createProjectForCompany($company);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings", [
            'name' => 'Tower Alpha',
            'total_floors' => 15,
            'total_units' => 60,
            'address' => '123 Main Street',
            'status' => 'planning',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Tower Alpha']);

        $this->assertDatabaseHas('buildings', [
            'project_id' => $project->id,
            'name' => 'Tower Alpha',
            'slug' => 'tower-alpha',
            'total_floors' => 15,
        ]);
    }

    public function test_owner_can_update_building(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = $this->createProjectForCompany($company);
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}", [
            'status' => 'under_construction',
            'total_floors' => 20,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'status' => 'under_construction',
            'total_floors' => 20,
        ]);
    }

    public function test_owner_can_delete_building(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = $this->createProjectForCompany($company);
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('buildings', ['id' => $building->id]);
    }

    public function test_building_show_includes_floors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $project = $this->createProjectForCompany($company);
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
        \App\Models\Floor::factory()->count(3)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}");

        $response->assertOk()
            ->assertJsonCount(3, 'data.floors');
    }

    public function test_viewer_can_view_buildings(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        $project = $this->createProjectForCompany($company);
        Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings");

        $response->assertOk();
    }

    public function test_viewer_cannot_create_building(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        $project = $this->createProjectForCompany($company);

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings", [
            'name' => 'Unauthorized Building',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_access_buildings(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = \App\Models\User::factory()->create();
        $project = $this->createProjectForCompany($company);

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings");

        $response->assertStatus(403);
    }
}
