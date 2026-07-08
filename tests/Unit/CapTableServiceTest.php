<?php

namespace Tests\Unit;

use App\Domains\Shareholder\Services\CapTableService;
use App\Models\Company;
use App\Models\CompanyMetrics;
use App\Models\ShareTransaction;
use App\Models\Shareholder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CapTableServiceTest extends TestCase
{
    use RefreshDatabase;

    private CapTableService $service;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CapTableService();
        $this->company = Company::factory()->create();
    }

    private function holder(string $name): Shareholder
    {
        return Shareholder::factory()->create([
            'company_id' => $this->company->id,
            'name' => $name,
            'shares_owned' => 0,
            'ownership_percentage' => 0,
            'status' => 'active',
        ]);
    }

    private function metrics(): CompanyMetrics
    {
        return CompanyMetrics::where('company_id', $this->company->id)->firstOrFail();
    }

    public function test_first_issue_uses_par_price_of_one(): void
    {
        $tahmeed = $this->holder('Tahmeed');

        $tx = $this->service->issueShares($tahmeed, 100);

        $this->assertEquals('issue', $tx->type);
        $this->assertEqualsWithDelta(1.0, (float) $tx->share_price, 0.000001);
        $this->assertEqualsWithDelta(100.0, (float) $tx->shares_issued, 0.000001);

        $this->assertEqualsWithDelta(100.0, (float) $tahmeed->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(100.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(100.0, (float) $this->metrics()->current_valuation, 0.01);
        $this->assertEqualsWithDelta(1.0, (float) $this->metrics()->current_share_price, 0.000001);
    }

    public function test_second_issue_at_same_price_dilutes_without_changing_price(): void
    {
        $tahmeed = $this->holder('Tahmeed');
        $sakib = $this->holder('Sakib');

        $this->service->issueShares($tahmeed, 100);
        $tx = $this->service->issueShares($sakib, 100);

        // Price is unchanged by investing at the current price.
        $this->assertEqualsWithDelta(1.0, (float) $tx->share_price, 0.000001);
        $this->assertEqualsWithDelta(100.0, (float) $tx->shares_issued, 0.000001);

        $this->assertEqualsWithDelta(200.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(200.0, (float) $this->metrics()->current_valuation, 0.01);
        $this->assertEqualsWithDelta(1.0, (float) $this->metrics()->current_share_price, 0.000001);

        // Each now owns 50%.
        $this->assertEqualsWithDelta(50.0, (float) $tahmeed->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(50.0, (float) $sakib->fresh()->ownership_percentage, 0.000001);
    }

    public function test_revaluation_changes_price_only_not_shares_or_percentages(): void
    {
        $tahmeed = $this->holder('Tahmeed');
        $sakib = $this->holder('Sakib');
        $this->service->issueShares($tahmeed, 100);
        $this->service->issueShares($sakib, 100);

        $this->service->revalueCompany($this->company->id, 240);

        $this->assertEqualsWithDelta(240.0, (float) $this->metrics()->current_valuation, 0.01);
        $this->assertEqualsWithDelta(200.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(1.2, (float) $this->metrics()->current_share_price, 0.000001);

        // Percentages depend on shares only → unchanged.
        $this->assertEqualsWithDelta(50.0, (float) $tahmeed->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(50.0, (float) $sakib->fresh()->ownership_percentage, 0.000001);
    }

    public function test_full_cap_table_example(): void
    {
        $tahmeed = $this->holder('Tahmeed');
        $sakib = $this->holder('Sakib');
        $rafi = $this->holder('Rafi');

        // 1) Tahmeed invests 100 at par (price 1) → 100 shares.
        $this->service->issueShares($tahmeed, 100);
        // 2) Sakib invests 100 at price 1 → 100 shares.
        $this->service->issueShares($sakib, 100);
        // 3) Company is revalued to 240 → price becomes 1.20.
        $this->service->revalueCompany($this->company->id, 240);
        // 4) Rafi invests 100 at price 1.20 → 83.333333 shares.
        $rafiTx = $this->service->issueShares($rafi, 100);

        $this->assertEqualsWithDelta(1.2, (float) $rafiTx->share_price, 0.000001);
        $this->assertEqualsWithDelta(83.333333, (float) $rafiTx->shares_issued, 0.000001);

        $this->assertEqualsWithDelta(283.333333, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(340.0, (float) $this->metrics()->current_valuation, 0.01);
        $this->assertEqualsWithDelta(1.2, (float) $this->metrics()->current_share_price, 0.000001);

        $this->assertEqualsWithDelta(35.294118, (float) $tahmeed->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(35.294118, (float) $sakib->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(29.411765, (float) $rafi->fresh()->ownership_percentage, 0.000001);

        // Percentages sum to 100.
        $sum = (float) $tahmeed->fresh()->ownership_percentage
            + (float) $sakib->fresh()->ownership_percentage
            + (float) $rafi->fresh()->ownership_percentage;
        $this->assertEqualsWithDelta(100.0, $sum, 0.0001);
    }

    public function test_transfer_moves_shares_without_changing_totals(): void
    {
        $from = $this->holder('From');
        $to = $this->holder('To');
        $this->service->issueShares($from, 100);
        $this->service->issueShares($to, 100);

        [$outgoing, $incoming] = $this->service->transferShares($from, $to, 40);

        $this->assertEqualsWithDelta(-40.0, (float) $outgoing->shares_issued, 0.000001);
        $this->assertEqualsWithDelta(40.0, (float) $incoming->shares_issued, 0.000001);
        $this->assertEquals($to->id, $outgoing->related_shareholder_id);
        $this->assertEquals($from->id, $incoming->related_shareholder_id);

        $this->assertEqualsWithDelta(60.0, (float) $from->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(140.0, (float) $to->fresh()->shares_owned, 0.000001);

        // Company totals & valuation are untouched.
        $this->assertEqualsWithDelta(200.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(200.0, (float) $this->metrics()->current_valuation, 0.01);

        $this->assertEqualsWithDelta(30.0, (float) $from->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(70.0, (float) $to->fresh()->ownership_percentage, 0.000001);
    }

    public function test_buyback_reduces_shares_and_valuation(): void
    {
        $holder = $this->holder('Holder');
        $this->service->issueShares($holder, 100);

        $this->service->buybackShares($holder, 40);

        $this->assertEqualsWithDelta(60.0, (float) $holder->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(60.0, (float) $this->metrics()->total_shares, 0.000001);
        // 40 shares at price 1 = 40 out.
        $this->assertEqualsWithDelta(60.0, (float) $this->metrics()->current_valuation, 0.01);
        // Price is preserved.
        $this->assertEqualsWithDelta(1.0, (float) $this->metrics()->current_share_price, 0.000001);
    }

    public function test_transfer_more_than_owned_is_rejected(): void
    {
        $from = $this->holder('From');
        $to = $this->holder('To');
        $this->service->issueShares($from, 100);

        $this->expectException(InvalidArgumentException::class);
        $this->service->transferShares($from, $to, 150);
    }

    public function test_deposit_more_issues_shares_and_dilutes_others(): void
    {
        $a = $this->holder('A');
        $b = $this->holder('B');
        $this->service->issueShares($a, 100); // 100 shares @ 1
        $this->service->issueShares($b, 100); // 100 shares @ 1 → each 50%

        // A deposits 100 more at the current price (1) → +100 shares.
        $this->service->issueShares($a, 100, 'Additional deposit');

        $this->assertEqualsWithDelta(200.0, (float) $a->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(300.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(300.0, (float) $this->metrics()->current_valuation, 0.01);
        // Price is preserved by depositing at the current price.
        $this->assertEqualsWithDelta(1.0, (float) $this->metrics()->current_share_price, 0.000001);

        // A now owns 200/300, B owns 100/300.
        $this->assertEqualsWithDelta(66.666667, (float) $a->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(33.333333, (float) $b->fresh()->ownership_percentage, 0.000001);
    }

    public function test_withdraw_amount_buys_back_equivalent_shares(): void
    {
        $a = $this->holder('A');
        $b = $this->holder('B');
        $this->service->issueShares($a, 200); // 200 shares
        $this->service->issueShares($b, 200); // 200 shares → each 50%

        // A withdraws 50 at price 1 → buys back 50 shares.
        $tx = $this->service->withdrawAmount($a, 50);

        $this->assertEquals('buyback', $tx->type);
        $this->assertEqualsWithDelta(-50.0, (float) $tx->shares_issued, 0.000001);
        $this->assertEqualsWithDelta(150.0, (float) $a->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(350.0, (float) $this->metrics()->total_shares, 0.000001);
        $this->assertEqualsWithDelta(350.0, (float) $this->metrics()->current_valuation, 0.01);

        // A: 150/350, B: 200/350.
        $this->assertEqualsWithDelta(42.857143, (float) $a->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(57.142857, (float) $b->fresh()->ownership_percentage, 0.000001);
    }

    public function test_withdraw_all_caps_at_full_balance(): void
    {
        $a = $this->holder('A');
        $b = $this->holder('B');
        $this->service->issueShares($a, 100);
        $this->service->issueShares($b, 100);

        // Withdraw exactly the full value; rounding must not exceed the balance.
        $this->service->withdrawAmount($a, 100);

        $this->assertEqualsWithDelta(0.0, (float) $a->fresh()->shares_owned, 0.000001);
        $this->assertEqualsWithDelta(0.0, (float) $a->fresh()->ownership_percentage, 0.000001);
        $this->assertEqualsWithDelta(100.0, (float) $b->fresh()->ownership_percentage, 0.000001);
    }

    public function test_withdraw_more_than_owned_is_rejected(): void
    {
        $a = $this->holder('A');
        $b = $this->holder('B');
        $this->service->issueShares($a, 100);
        $this->service->issueShares($b, 100);

        // A only owns 100 worth; withdrawing 150 (after cap) still buys back 100 max,
        // so exceeding the balance is only possible when the cap is bypassed. Here we
        // assert the direct buyback primitive rejects over-withdrawal.
        $this->expectException(InvalidArgumentException::class);
        $this->service->buybackShares($a, 150);
    }

    public function test_issue_records_a_journal_credit(): void
    {
        $holder = $this->holder('Holder');
        $tx = $this->service->issueShares($holder, 100);

        $this->assertDatabaseHas('p_journals', [
            'company_id' => $this->company->id,
            'type' => 'credit',
            'category' => 'shareholder_investment',
            'reference_type' => ShareTransaction::class,
            'reference_id' => $tx->id,
        ]);
    }
}
