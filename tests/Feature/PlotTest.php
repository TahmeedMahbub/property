<?php

namespace Tests\Feature;

use App\Domains\Plot\Services\PlotReportService;
use App\Domains\Plot\Services\PlotService;
use App\Models\Plot;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SeedsRolesAndPermissions;
use Tests\TestCase;

class PlotTest extends TestCase
{
    use RefreshDatabase, SeedsRolesAndPermissions;

    private function makePlot(int $companyId, array $overrides = []): Plot
    {
        return (new PlotService())->create($companyId, array_merge([
            'plot_code' => 'PLT-' . fake()->unique()->numberBetween(1000, 9999),
            'plot_name' => 'Test Plot',
            'status' => 'negotiation',
            'land_size' => 10,
            'land_unit' => 'katha',
            'purchase_price' => 10000000,
            'registration_cost' => 200000,
            'mutation_cost' => 50000,
            'legal_cost' => 40000,
            'broker_cost' => 60000,
            'other_cost' => 10000,
        ], $overrides));
    }

    public function test_total_acquisition_cost_is_auto_calculated(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $plot = $this->makePlot($company->id);

        // 10,000,000 + 200,000 + 50,000 + 40,000 + 60,000 + 10,000
        $this->assertEqualsWithDelta(10360000.0, $plot->total_acquisition_cost, 0.01);
    }

    public function test_sellers_and_owners_are_created_with_the_plot(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $plot = $this->makePlot($company->id, [
            'sellers' => [
                ['name' => 'Abdul Karim', 'phone' => '0171', 'nid' => '123'],
                ['name' => 'Sultana Begum'],
            ],
            'owners' => [
                ['name' => 'Owner A', 'ownership_percentage' => 60],
                ['name' => 'Owner B', 'ownership_percentage' => 40],
            ],
        ]);

        $this->assertCount(2, $plot->sellers);
        $this->assertCount(2, $plot->owners);
        $this->assertEqualsWithDelta(100.0, $plot->owners->sum('ownership_percentage'), 0.01);
    }

    public function test_paid_and_due_are_computed_from_payments(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new PlotService();
        $plot = $this->makePlot($company->id);

        $service->recordPayment($plot, [
            'payment_type' => 'bayna',
            'amount' => 1000000,
            'payment_date' => '2025-01-01',
            'payment_method' => 'bank_transfer',
        ]);
        $service->recordPayment($plot, [
            'payment_type' => 'land',
            'amount' => 5000000,
            'payment_date' => '2025-02-01',
            'payment_method' => 'cash',
        ]);

        $plot->refresh()->load('payments');

        $this->assertEqualsWithDelta(6000000.0, $plot->total_paid, 0.01);
        $this->assertEqualsWithDelta(4360000.0, $plot->total_due, 0.01);
    }

    public function test_payments_post_cash_out_to_the_journal(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new PlotService();
        $plot = $this->makePlot($company->id);

        // A plot payment is cash leaving the company (debit).
        $payment = $service->recordPayment($plot, [
            'payment_type' => 'bayna',
            'amount' => 1000000,
            'payment_date' => '2025-01-01',
            'payment_method' => 'bank_transfer',
        ]);
        $this->assertEqualsWithDelta(-1000000.0, JournalService::balance($company->id), 0.01);

        // Deleting the payment reverses its cash-out entry.
        $service->deletePayment($payment);
        $this->assertEqualsWithDelta(0.0, JournalService::balance($company->id), 0.01);
    }

    public function test_deleting_a_plot_reverses_all_payment_entries(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new PlotService();
        $plot = $this->makePlot($company->id);

        $service->recordPayment($plot, [
            'payment_type' => 'bayna',
            'amount' => 2000000,
            'payment_date' => '2025-01-01',
            'payment_method' => 'bank_transfer',
        ]);
        $this->assertEqualsWithDelta(-2000000.0, JournalService::balance($company->id), 0.01);

        $service->delete($plot->load('payments'));
        $this->assertEqualsWithDelta(0.0, JournalService::balance($company->id), 0.01);
        $this->assertSoftDeleted('p_plots', ['id' => $plot->id]);
    }

    public function test_company_metrics_aggregate_across_plots(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new PlotService();
        $reports = new PlotReportService();

        $a = $this->makePlot($company->id, ['status' => 'prospect', 'land_size' => 10, 'land_unit' => 'katha']);
        $b = $this->makePlot($company->id, ['status' => 'registration_complete', 'land_size' => 1, 'land_unit' => 'acre']);

        $service->recordPayment($a, [
            'payment_type' => 'bayna',
            'amount' => 1000000,
            'payment_date' => '2025-01-01',
            'payment_method' => 'bank_transfer',
        ]);

        $metrics = $reports->companyMetrics($company->id);

        $this->assertSame(2, $metrics['total_plots']);
        // 10 katha + 1 acre (60.5 katha) = 70.5 katha
        $this->assertEqualsWithDelta(70.5, $metrics['total_land_katha'], 0.01);
        $this->assertEqualsWithDelta(20720000.0, $metrics['total_acquisition_cost'], 0.01);
        $this->assertEqualsWithDelta(1000000.0, $metrics['total_paid'], 0.01);
        $this->assertEqualsWithDelta(19720000.0, $metrics['total_due'], 0.01);
        // Plot A is 'prospect' → bayna & registration pending. Plot B is complete.
        $this->assertSame(1, $metrics['bayna_pending']);
        $this->assertSame(1, $metrics['registration_pending']);
    }

    public function test_due_report_excludes_fully_paid_plots(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();
        $service = new PlotService();
        $reports = new PlotReportService();

        $open = $this->makePlot($company->id);
        $paid = $this->makePlot($company->id);

        // Pay the full acquisition cost of $paid.
        $service->recordPayment($paid, [
            'payment_type' => 'land',
            'amount' => $paid->total_acquisition_cost,
            'payment_date' => '2025-01-01',
            'payment_method' => 'cash',
        ]);

        $rows = $reports->dueReport($company->id);

        $this->assertCount(1, $rows);
        $this->assertSame($open->id, $rows->first()->id);
    }

    public function test_plot_web_crud_flow(): void
    {
        [$owner, $company] = $this->createOwnerWithCompany();

        $response = $this->actingAs($owner)
            ->withHeader('X-Company-Id', $company->uuid)
            ->post('/plots', [
                'plot_code' => 'PLT-5555',
                'plot_name' => 'Web Plot',
                'status' => 'prospect',
                'land_unit' => 'katha',
                'land_size' => 5,
                'purchase_price' => 5000000,
                'sellers' => [['name' => 'Seller One']],
                'owners' => [['name' => 'Owner One', 'ownership_percentage' => 100]],
            ]);

        $response->assertRedirect('/plots');
        $this->assertDatabaseHas('p_plots', [
            'company_id' => $company->id,
            'plot_code' => 'PLT-5555',
        ]);
    }
}
