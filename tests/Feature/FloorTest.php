<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class FloorTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    private function createHierarchy($company): array
    {
        $project = Project::factory()->create(['company_id' => $company->id]);
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        return [$project, $building];
    }

    public function test_owner_can_list_floors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);
        Floor::factory()->count(5)->sequence(
            ['floor_number' => 1],
            ['floor_number' => 2],
            ['floor_number' => 3],
            ['floor_number' => 4],
            ['floor_number' => 5],
        )->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors");

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_owner_can_create_floor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors", [
            'name' => 'Ground Floor',
            'floor_number' => 0,
            'description' => 'Lobby and reception',
            'total_units' => 4,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Ground Floor']);

        $this->assertDatabaseHas('floors', [
            'building_id' => $building->id,
            'name' => 'Ground Floor',
            'floor_number' => 0,
        ]);
    }

    public function test_owner_can_update_floor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);
        $floor = Floor::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_number' => 1,
        ]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors/{$floor->uuid}", [
            'name' => 'Updated Floor',
            'total_units' => 8,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('floors', [
            'id' => $floor->id,
            'name' => 'Updated Floor',
            'total_units' => 8,
        ]);
    }

    public function test_owner_can_delete_floor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);
        $floor = Floor::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_number' => 1,
        ]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors/{$floor->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('floors', ['id' => $floor->id]);
    }

    public function test_floor_show_includes_units(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);
        $floor = Floor::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_number' => 1,
        ]);
        Unit::factory()->count(4)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors/{$floor->uuid}");

        $response->assertOk()
            ->assertJsonCount(4, 'data.units');
    }

    public function test_viewer_can_view_floors(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        [$project, $building] = $this->createHierarchy($company);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors");

        $response->assertOk();
    }

    public function test_viewer_cannot_create_floor(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        [$project, $building] = $this->createHierarchy($company);

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors", [
            'name' => 'Unauthorized',
            'floor_number' => 99,
        ]);

        $response->assertStatus(403);
    }

    public function test_duplicate_floor_number_rejected(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building] = $this->createHierarchy($company);
        Floor::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_number' => 5,
        ]);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/buildings/{$building->uuid}/floors", [
            'name' => 'Floor 5 Again',
            'floor_number' => 5,
        ]);

        // Unique constraint violation
        $response->assertStatus(500);
    }
}
