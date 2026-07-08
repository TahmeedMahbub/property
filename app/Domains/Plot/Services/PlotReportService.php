<?php

namespace App\Domains\Plot\Services;

use App\Models\Plot;
use App\Models\PlotPayment;
use Illuminate\Support\Collection;

/**
 * Read-optimised plot metrics and reports for the dashboard and report screens.
 *
 * All money figures are derived from the plots and their payments so the numbers
 * are always accurate and never drift out of a cache.
 */
class PlotReportService
{
    /**
     * Company-wide plot dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function companyMetrics(int $companyId): array
    {
        $plots = Plot::forCompany($companyId)
            ->withSum('payments as paid_total', 'amount')
            ->get();

        $totalCost = 0.0;
        $totalPaid = 0.0;
        $totalLandKatha = 0.0;
        $baynaPending = 0;
        $registrationPending = 0;

        foreach ($plots as $plot) {
            $totalCost += $plot->total_acquisition_cost;
            $totalPaid += (float) ($plot->paid_total ?? 0);
            $totalLandKatha += $plot->land_size_in_katha;

            if ($plot->is_bayna_pending) {
                $baynaPending++;
            }

            if ($plot->is_registration_pending) {
                $registrationPending++;
            }
        }

        return [
            'total_plots' => $plots->count(),
            'total_land_katha' => round($totalLandKatha, 4),
            'total_acquisition_cost' => round($totalCost, 2),
            'total_paid' => round($totalPaid, 2),
            'total_due' => round($totalCost - $totalPaid, 2),
            'bayna_pending' => $baynaPending,
            'registration_pending' => $registrationPending,
        ];
    }

    /**
     * Plot Register — every plot with its key location and status info.
     *
     * @return Collection<int, Plot>
     */
    public function registerReport(int $companyId): Collection
    {
        return Plot::forCompany($companyId)
            ->withCount(['sellers', 'owners'])
            ->orderBy('plot_code')
            ->get();
    }

    /**
     * Plot Acquisition Report — cost breakdown per plot.
     *
     * @return Collection<int, Plot>
     */
    public function acquisitionReport(int $companyId): Collection
    {
        return Plot::forCompany($companyId)
            ->with('payments')
            ->orderByDesc('purchase_price')
            ->get();
    }

    /**
     * Plot Payment Report — every payment transaction across all plots.
     *
     * @return Collection<int, PlotPayment>
     */
    public function paymentReport(int $companyId): Collection
    {
        return PlotPayment::whereHas('plot', fn ($q) => $q->where('company_id', $companyId))
            ->with('plot')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Plot Due Report — plots that still carry an outstanding acquisition due.
     *
     * @return Collection<int, Plot>
     */
    public function dueReport(int $companyId): Collection
    {
        return Plot::forCompany($companyId)
            ->with('payments')
            ->get()
            ->filter(fn (Plot $plot) => $plot->total_due > 0)
            ->sortByDesc(fn (Plot $plot) => $plot->total_due)
            ->values();
    }

    /**
     * Plot Cost Summary — aggregated cost components across all plots.
     *
     * @return array<string, mixed>
     */
    public function costSummary(int $companyId): array
    {
        $totals = Plot::forCompany($companyId)
            ->selectRaw('
                COUNT(*) as plot_count,
                COALESCE(SUM(purchase_price), 0) as purchase_price,
                COALESCE(SUM(registration_cost), 0) as registration_cost,
                COALESCE(SUM(mutation_cost), 0) as mutation_cost,
                COALESCE(SUM(legal_cost), 0) as legal_cost,
                COALESCE(SUM(broker_cost), 0) as broker_cost,
                COALESCE(SUM(other_cost), 0) as other_cost
            ')
            ->first();

        $purchase = (float) $totals->purchase_price;
        $registration = (float) $totals->registration_cost;
        $mutation = (float) $totals->mutation_cost;
        $legal = (float) $totals->legal_cost;
        $broker = (float) $totals->broker_cost;
        $other = (float) $totals->other_cost;

        $totalCost = $purchase + $registration + $mutation + $legal + $broker + $other;

        $paid = (float) PlotPayment::whereHas('plot', fn ($q) => $q->where('company_id', $companyId))
            ->sum('amount');

        return [
            'plot_count' => (int) $totals->plot_count,
            'purchase_price' => round($purchase, 2),
            'registration_cost' => round($registration, 2),
            'mutation_cost' => round($mutation, 2),
            'legal_cost' => round($legal, 2),
            'broker_cost' => round($broker, 2),
            'other_cost' => round($other, 2),
            'total_acquisition_cost' => round($totalCost, 2),
            'total_paid' => round($paid, 2),
            'total_due' => round($totalCost - $paid, 2),
        ];
    }
}
