<?php

namespace App\Domains\Plot\Services;

use App\Models\Plot;
use App\Models\PlotPayment;
use App\Services\JournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Plot (land acquisition) lifecycle: create/update plots with their sellers and
 * legal owners, and record acquisition payments.
 *
 * A plot is a land asset. This module is entirely separate from Loans,
 * Shareholder equity and Construction cost — it never touches the cap table.
 *
 * Accounting: the company ledger (Journal) is a single cash/bank account where
 * a credit is cash-in and a debit is cash-out. Every plot payment (bayna, land,
 * registration, legal, mutation, broker) is cash leaving the company to acquire
 * the land asset, so each payment posts a DEBIT under the `plot_acquisition`
 * category — this is the "Cr Cash/Bank" side of the acquisition entries. The
 * matching "Dr Plot Acquisition Asset" is represented by the plot record itself.
 *
 * Vendor due (unpaid acquisition cost) is derived as
 * total_acquisition_cost − total_paid and is NOT posted to the cash ledger,
 * because that ledger tracks realised cash only.
 */
class PlotService
{
    public const CATEGORY_ACQUISITION = 'plot_acquisition';

    /**
     * Create a plot with its sellers and legal owners.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): Plot
    {
        $sellers = $data['sellers'] ?? [];
        $owners = $data['owners'] ?? [];
        unset($data['sellers'], $data['owners']);

        $data['company_id'] = $companyId;
        $data['created_by'] = $data['created_by'] ?? Auth::id();
        $data['status'] = $data['status'] ?? 'prospect';

        return DB::transaction(function () use ($data, $sellers, $owners) {
            $plot = Plot::create($data);
            $this->syncSellers($plot, $sellers);
            $this->syncOwners($plot, $owners);

            return $plot;
        });
    }

    /**
     * Update a plot and re-sync its sellers and owners.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Plot $plot, array $data): Plot
    {
        $sellers = $data['sellers'] ?? null;
        $owners = $data['owners'] ?? null;
        unset($data['sellers'], $data['owners']);

        return DB::transaction(function () use ($plot, $data, $sellers, $owners) {
            $plot->update($data);

            if ($sellers !== null) {
                $plot->sellers()->delete();
                $this->syncSellers($plot, $sellers);
            }

            if ($owners !== null) {
                $plot->owners()->delete();
                $this->syncOwners($plot, $owners);
            }

            return $plot->refresh();
        });
    }

    /**
     * Record an acquisition payment against a plot and post the cash-out entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Plot $plot, array $data): PlotPayment
    {
        return DB::transaction(function () use ($plot, $data) {
            $data['plot_id'] = $plot->id;
            $data['created_by'] = $data['created_by'] ?? Auth::id();

            $payment = PlotPayment::create($data);

            $this->syncPaymentJournal($plot, $payment);

            return $payment;
        });
    }

    /**
     * Delete a payment and reverse its cash-out ledger entry.
     */
    public function deletePayment(PlotPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $plot = $payment->plot;

            if ($plot) {
                JournalService::reverseReference(
                    companyId: $plot->company_id,
                    reference: $payment,
                    category: self::CATEGORY_ACQUISITION,
                    remarks: 'Reversed plot payment for ' . $plot->plot_code,
                );
            }

            $payment->delete();
        });
    }

    /**
     * Delete a plot: reverse every acquisition payment posted to the ledger,
     * then soft-delete the plot (sellers/owners/payments cascade at DB level on
     * force delete; kept for the soft-deleted record here).
     */
    public function delete(Plot $plot): void
    {
        DB::transaction(function () use ($plot) {
            foreach ($plot->payments as $payment) {
                JournalService::reverseReference(
                    companyId: $plot->company_id,
                    reference: $payment,
                    category: self::CATEGORY_ACQUISITION,
                    remarks: 'Reversed plot payment for ' . $plot->plot_code,
                );
            }

            $plot->delete();
        });
    }

    /**
     * Post the ledger debit (cash out) for a plot payment.
     */
    private function syncPaymentJournal(Plot $plot, PlotPayment $payment): void
    {
        JournalService::syncReference(
            companyId: $plot->company_id,
            reference: $payment,
            // Negative target credit ⇒ a debit (cash out) of the payment amount.
            targetCredit: -(float) $payment->amount,
            category: self::CATEGORY_ACQUISITION,
            remarks: ucfirst($payment->payment_type) . ' payment for plot ' . $plot->plot_code,
            userId: $payment->created_by,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $sellers
     */
    private function syncSellers(Plot $plot, array $sellers): void
    {
        foreach ($sellers as $seller) {
            if (empty($seller['name'])) {
                continue;
            }

            $plot->sellers()->create([
                'name' => $seller['name'],
                'phone' => $seller['phone'] ?? null,
                'nid' => $seller['nid'] ?? null,
                'address' => $seller['address'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $owners
     */
    private function syncOwners(Plot $plot, array $owners): void
    {
        foreach ($owners as $owner) {
            if (empty($owner['name'])) {
                continue;
            }

            $plot->owners()->create([
                'name' => $owner['name'],
                'phone' => $owner['phone'] ?? null,
                'nid' => $owner['nid'] ?? null,
                'address' => $owner['address'] ?? null,
                'ownership_percentage' => $owner['ownership_percentage'] ?? 0,
            ]);
        }
    }
}
