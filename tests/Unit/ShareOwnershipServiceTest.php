<?php

namespace Tests\Unit;

use App\Domains\Shareholder\Services\ShareOwnershipService;
use App\Models\Company;
use App\Models\Shareholder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareOwnershipServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShareOwnershipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShareOwnershipService();
    }

    public function test_it_calculates_ownership_percentage_from_shares_owned(): void
    {
        $company = Company::factory()->create();

        // total = 283.333333 → matches the specification example.
        $a = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'ownership_percentage' => 0]);
        $b = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'ownership_percentage' => 0]);
        $c = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 83.333333, 'ownership_percentage' => 0]);

        $this->service->recalculateCompanyOwnerships($company->id);

        $this->assertEquals(35.294118, round((float) $a->fresh()->ownership_percentage, 6));
        $this->assertEquals(35.294118, round((float) $b->fresh()->ownership_percentage, 6));
        // 83.333333 / 283.333333 * 100 = 29.41176459… → rounds to 29.411765.
        $this->assertEquals(29.411765, round((float) $c->fresh()->ownership_percentage, 6));
    }

    public function test_percentages_are_rounded_to_six_decimals(): void
    {
        $company = Company::factory()->create();
        $s = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 1, 'ownership_percentage' => 0]);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 2, 'ownership_percentage' => 0]);

        $this->service->recalculateCompanyOwnerships($company->id);

        // 1 / 3 * 100 = 33.333333...
        $this->assertEquals(33.333333, (float) $s->fresh()->ownership_percentage);
    }

    public function test_it_prevents_division_by_zero_when_no_shares(): void
    {
        $company = Company::factory()->create();
        $s = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 0, 'ownership_percentage' => 50]);

        $this->service->recalculateCompanyOwnerships($company->id);

        $this->assertEquals(0.0, (float) $s->fresh()->ownership_percentage);
    }

    public function test_inactive_shareholders_are_zeroed_and_excluded_from_total(): void
    {
        $company = Company::factory()->create();
        $active = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'status' => 'active', 'ownership_percentage' => 0]);
        $inactive = Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'status' => 'inactive', 'ownership_percentage' => 99]);

        $this->service->recalculateCompanyOwnerships($company->id);

        // Only the active holder counts → 100%.
        $this->assertEquals(100.0, (float) $active->fresh()->ownership_percentage);
        $this->assertEquals(0.0, (float) $inactive->fresh()->ownership_percentage);
    }

    public function test_recalculation_is_scoped_to_a_single_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $a = Shareholder::factory()->create(['company_id' => $companyA->id, 'shares_owned' => 100, 'ownership_percentage' => 0]);
        $b = Shareholder::factory()->create(['company_id' => $companyB->id, 'shares_owned' => 100, 'ownership_percentage' => 0]);

        $this->service->recalculateCompanyOwnerships($companyA->id);

        $this->assertEquals(100.0, (float) $a->fresh()->ownership_percentage);
        // Company B untouched.
        $this->assertEquals(0.0, (float) $b->fresh()->ownership_percentage);
    }

    public function test_summary_returns_expected_shape(): void
    {
        $company = Company::factory()->create();
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'user_id' => null]);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'user_id' => null]);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 83.333333, 'user_id' => null]);

        $this->service->recalculateCompanyOwnerships($company->id);
        $summary = $this->service->getCompanyOwnershipSummary($company->id);

        $this->assertCount(3, $summary);
        $this->assertArrayHasKey('user_id', $summary[0]);
        $this->assertArrayHasKey('shares', $summary[0]);
        $this->assertArrayHasKey('percentage', $summary[0]);
        $this->assertEqualsWithDelta(100.0, array_sum(array_column($summary, 'percentage')), 0.0001);
    }
}
