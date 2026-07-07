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

class UnitTypeTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_owner_can_list_unit_types(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        UnitType::factory()->count(3)->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/unit-types");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_owner_can_create_unit_type(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/unit-types", [
            'name' => '3 BHK',
            'description' => 'Three bedroom apartment',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => '3 BHK']);

        $this->assertDatabaseHas('unit_types', [
            'company_id' => $company->id,
            'name' => '3 BHK',
            'slug' => '3-bhk',
        ]);
    }

    public function test_owner_can_update_unit_type(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $unitType = UnitType::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/unit-types/{$unitType->uuid}", [
            'name' => 'Updated Type',
            'is_active' => false,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('unit_types', [
            'id' => $unitType->id,
            'name' => 'Updated Type',
            'slug' => 'updated-type',
            'is_active' => false,
        ]);
    }

    public function test_owner_can_delete_unit_type(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $unitType = UnitType::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/unit-types/{$unitType->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('unit_types', ['id' => $unitType->id]);
    }

    public function test_viewer_can_view_unit_types(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        UnitType::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/unit-types");

        $response->assertOk();
    }

    public function test_viewer_cannot_create_unit_type(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/unit-types", [
            'name' => 'Unauthorized',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_access_unit_types(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = \App\Models\User::factory()->create();

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}/unit-types");

        $response->assertStatus(403);
    }
}
