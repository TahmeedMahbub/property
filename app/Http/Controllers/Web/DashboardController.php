<?php

namespace App\Http\Controllers\Web;

use App\Domains\Loan\Services\LoanReportService;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $company = app('currentCompany');

        $loanMetrics = $company
            ? (new LoanReportService())->companyMetrics($company->id)
            : null;

        return view('contents.property.dashboard', compact('company', 'loanMetrics'));
    }

    public function stats(): JsonResponse
    {
        $company = app('currentCompany');

        // Aggregate unit counts and value per status in a single query.
        $byStatus = Unit::where('company_id', $company->id)
            ->selectRaw('status, COUNT(*) as units, COALESCE(SUM(price), 0) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $count = fn (string $status) => (int) ($byStatus[$status]->units ?? 0);
        $value = fn (string $status) => (float) ($byStatus[$status]->value ?? 0);

        // Value of property the company still owns (unsold inventory).
        $inventoryValue = $value('available') + $value('reserved') + $value('booked');
        $soldValue = $value('sold') + $value('handovered');

        // Company asset comes from the accounting ledger (Journal): every
        // shareholder investment, project-investor investment and unit sale is
        // credited, expenses/refunds are debited. Net (credits - debits) per
        // category, computed in a single query.
        $ledger = Journal::where('company_id', $company->id)
            ->selectRaw("category, COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as net")
            ->groupBy('category')
            ->pluck('net', 'category');

        $net = fn (string $category) => (float) ($ledger[$category] ?? 0);

        $shareholderInvestment = $net('shareholder_investment');
        $investorInvestment = $net('investment');
        $unitSales = $net('unit_sale');

        // Total company asset = current cash/capital balance across the ledger.
        $companyAsset = (float) $ledger->sum();

        $stats = [
            'projects' => $company->projects()->count(),
            'buildings' => $company->buildings()->count(),
            'total_units' => (int) $byStatus->sum('units'),
            'available_units' => $count('available'),
            'reserved_units' => $count('reserved'),
            'booked_units' => $count('booked'),
            'sold_units' => $count('sold') + $count('handovered'),
            // Ledger-based company asset (money invested + realised sales).
            'total_asset_value' => round($companyAsset, 2),
            'shareholder_investment' => round($shareholderInvestment, 2),
            'investor_investment' => round($investorInvestment, 2),
            'unit_sales' => round($unitSales, 2),
            // Property inventory value (informational).
            'inventory_value' => round($inventoryValue, 2),
            'sold_value' => round($soldValue, 2),
            'portfolio_value' => round($inventoryValue + $soldValue, 2),
        ];

        return response()->json($stats);
    }
}
