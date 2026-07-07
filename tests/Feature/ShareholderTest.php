<?php

namespace Tests\Feature;

use App\Models\Shareholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class ShareholderTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    public function test_owner_can_list_shareholders(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        Shareholder::factory()->count(3)->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/shareholders");

        $response->assertOk();
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_owner_can_create_shareholder(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/shareholders", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+8801712345678',
            'share_percentage' => 25.5,
            'share_amount' => 5000000,
            'share_type' => 'common',
            'acquired_at' => '2025-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'John Doe']);

        $this->assertDatabaseHas('shareholders', [
            'company_id' => $company->id,
            'name' => 'John Doe',
            'share_percentage' => 25.5,
        ]);
    }

    public function test_owner_can_create_shareholder_linked_to_user(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $linkedUser = User::factory()->create();

        $response = $this->apiAs($owner, 'POST', "/api/companies/{$company->uuid}/shareholders", [
            'user_id' => $linkedUser->uuid,
            'name' => $linkedUser->name,
            'share_percentage' => 10,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('shareholders', [
            'company_id' => $company->id,
            'user_id' => $linkedUser->id,
        ]);
    }

    public function test_owner_can_update_shareholder(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $shareholder = Shareholder::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'PUT', "/api/companies/{$company->uuid}/shareholders/{$shareholder->uuid}", [
            'share_percentage' => 30,
            'share_amount' => 7000000,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('shareholders', [
            'id' => $shareholder->id,
            'share_percentage' => 30,
        ]);
    }

    public function test_owner_can_delete_shareholder(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $shareholder = Shareholder::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'DELETE', "/api/companies/{$company->uuid}/shareholders/{$shareholder->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('shareholders', ['id' => $shareholder->id]);
    }

    public function test_viewer_can_view_shareholders(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');
        Shareholder::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($viewer, 'GET', "/api/companies/{$company->uuid}/shareholders");

        $response->assertOk();
    }

    public function test_viewer_cannot_create_shareholder(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        [$viewer] = $this->createMemberWithRole($company, 'viewer');

        $response = $this->apiAs($viewer, 'POST', "/api/companies/{$company->uuid}/shareholders", [
            'name' => 'Hacker',
            'share_percentage' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_access_shareholders(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $outsider = User::factory()->create();

        $response = $this->apiAs($outsider, 'GET', "/api/companies/{$company->uuid}/shareholders");

        $response->assertStatus(403);
    }

    public function test_shareholder_show_returns_details(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $shareholder = Shareholder::factory()->create(['company_id' => $company->id]);

        $response = $this->apiAs($owner, 'GET', "/api/companies/{$company->uuid}/shareholders/{$shareholder->uuid}");

        $response->assertOk()
            ->assertJsonFragment(['uuid' => $shareholder->uuid]);
    }
}
