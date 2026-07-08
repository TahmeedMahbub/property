<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $company = app('currentCompany');

        return view('contents.property.dashboard', compact('company'));
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

        // Company asset = value of property the company still owns (unsold inventory).
        // Units that are sold/handovered have been transferred to buyers and are no
        // longer part of the company's holdings.
        $inventoryValue = $value('available') + $value('reserved') + $value('booked');
        $soldValue = $value('sold') + $value('handovered');

        $stats = [
            'projects' => $company->projects()->count(),
            'buildings' => $company->buildings()->count(),
            'total_units' => (int) $byStatus->sum('units'),
            'available_units' => $count('available'),
            'reserved_units' => $count('reserved'),
            'booked_units' => $count('booked'),
            'sold_units' => $count('sold') + $count('handovered'),
            'total_asset_value' => round($inventoryValue, 2),
            'sold_value' => round($soldValue, 2),
            'portfolio_value' => round($inventoryValue + $soldValue, 2),
        ];

        return response()->json($stats);
    }
}
