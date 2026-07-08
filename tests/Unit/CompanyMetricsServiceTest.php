<?php

namespace Tests\Unit;

use App\Domains\Company\Services\CompanyMetricsService;
use App\Domains\Shareholder\Services\ShareOwnershipService;
use App\Models\Company;
use App\Models\Shareholder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_shares_sums_active_shareholders_only(): void
    {
        $company = Company::factory()->create();
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100, 'status' => 'active']);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 50, 'status' => 'active']);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 999, 'status' => 'inactive']);

        $metrics = new CompanyMetricsService($company->id);

        $this->assertEquals(150.0, $metrics->totalShares());
    }

    public function test_total_shares_is_zero_without_shareholders(): void
    {
        $company = Company::factory()->create();

        $metrics = new CompanyMetricsService($company->id);

        $this->assertEquals(0.0, $metrics->totalShares());
    }

    public function test_ownership_summary_reflects_cached_percentages(): void
    {
        $company = Company::factory()->create();
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 100]);
        Shareholder::factory()->create(['company_id' => $company->id, 'shares_owned' => 300]);

        (new ShareOwnershipService())->recalculateCompanyOwnerships($company->id);

        $metrics = new CompanyMetricsService($company->id);
        $summary = $metrics->ownershipSummary();

        $this->assertCount(2, $summary);
        $this->assertEqualsWithDelta(100.0, array_sum(array_column($summary, 'percentage')), 0.0001);
    }
}
