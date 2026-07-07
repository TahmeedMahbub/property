<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    private function createFullHierarchy($company): array
    {
        $project = Project::factory()->create(['company_id' => $company->id]);
        $building = Building::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
        $floor = Floor::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_number' => 1,
        ]);

        return [$project, $building, $floor];
    }

    public function test_owner_can_list_units_for_project(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        Unit::factory()->count(5)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units");

        $response->assertOk();
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_owner_can_create_unit(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/floors/{$floor->uuid}/units", [
            'unit_number' => 'A-101',
            'size' => 1200.50,
            'price' => 5500000,
            'facing' => 'South',
            'status' => 'available',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['unit_number' => 'A-101']);

        $this->assertDatabaseHas('units', [
            'floor_id' => $floor->id,
            'building_id' => $building->id,
            'project_id' => $project->id,
            'unit_number' => 'A-101',
            'status' => 'available',
        ]);
    }

    public function test_owner_can_create_unit_with_type(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        $unitType = UnitType::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/floors/{$floor->uuid}/units", [
            'unit_number' => 'B-201',
            'unit_type_id' => $unitType->uuid,
            'size' => 1500,
            'price' => 7000000,
            'facing' => 'North-East',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('units', [
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
            'unit_number' => 'B-201',
        ]);
    }

    public function test_owner_can_update_unit(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        $unit = Unit::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}", [
            'price' => 8000000,
            'status' => 'reserved',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'price' => 8000000,
            'status' => 'reserved',
        ]);
    }

    public function test_owner_can_delete_unit(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        $unit = Unit::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }

    public function test_unit_show_includes_relations(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        $unitType = UnitType::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_type_id' => $unitType->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['building', 'floor', 'unit_type'],
            ]);
    }

    public function test_units_can_be_filtered_by_status(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        Unit::factory()->count(3)->available()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        Unit::factory()->count(2)->sold()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units?status=available");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_viewer_can_view_units(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        [$project, $building, $floor] = $this->createFullHierarchy($company);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units");

        $response->assertOk();
    }

    public function test_viewer_cannot_create_unit(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        [$project, $building, $floor] = $this->createFullHierarchy($company);

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/floors/{$floor->uuid}/units", [
            'unit_number' => 'X-999',
            'price' => 999,
        ]);

        $response->assertStatus(403);
    }

    public function test_member_can_manage_units(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$member] = $this->createMemberWithRole($company, 'member');
        [$project, $building, $floor] = $this->createFullHierarchy($company);

        $response = $this->apiAs($member, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/floors/{$floor->uuid}/units", [
            'unit_number' => 'M-101',
            'size' => 900,
            'price' => 3000000,
        ]);

        $response->assertStatus(201);
    }

    public function test_non_member_cannot_access_units(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = \App\Models\User::factory()->create();
        [$project] = $this->createFullHierarchy($company);

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units");

        $response->assertStatus(403);
    }

    public function test_unit_status_transitions(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        $unit = Unit::factory()->available()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);

        // Available -> Reserved
        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}", [
            'status' => 'reserved',
        ]);
        $response->assertOk();

        // Reserved -> Booked
        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}", [
            'status' => 'booked',
        ]);
        $response->assertOk();

        // Booked -> Sold
        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}", [
            'status' => 'sold',
        ]);
        $response->assertOk();

        // Sold -> Handovered
        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/projects/{$project->uuid}/units/{$unit->uuid}", [
            'status' => 'handovered',
        ]);
        $response->assertOk();

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'status' => 'handovered']);
    }

    public function test_duplicate_unit_number_in_building_rejected(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$project, $building, $floor] = $this->createFullHierarchy($company);
        Unit::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => 'A-101',
        ]);

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/projects/{$project->uuid}/floors/{$floor->uuid}/units", [
            'unit_number' => 'A-101',
            'price' => 999,
        ]);

        // Unique constraint violation
        $response->assertStatus(500);
    }
}
