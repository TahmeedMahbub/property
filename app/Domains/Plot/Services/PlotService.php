<?php

namespace App\Domains\Plot\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Plot;
use App\Models\PlotPayment;
use App\Services\DocumentStorageService;
use App\Services\JournalService;
use Illuminate\Http\UploadedFile;
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
     * Maps a plot cost field to the payment type recorded when its "Paid"
     * checkbox is ticked on the plot form. These payments are flagged
     * auto_generated so they can be re-synced without touching payments the
     * user records manually.
     */
    public const FIELD_PAYMENT_TYPES = [
        'bayna_amount' => 'bayna',
        'purchase_price' => 'land',
        'registration_cost' => 'registration',
        'mutation_cost' => 'mutation',
        'legal_cost' => 'legal',
        'broker_cost' => 'broker',
        'other_cost' => 'other',
    ];

    /**
     * Create a plot with its sellers and legal owners.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): Plot
    {
        $sellers = $data['sellers'] ?? [];
        $owners = $data['owners'] ?? [];
        $paid = $data['paid'] ?? [];
        $documents = $data['documents'] ?? [];
        unset($data['sellers'], $data['owners'], $data['paid'], $data['documents']);

        $data['company_id'] = $companyId;
        $data['created_by'] = $data['created_by'] ?? Auth::id();
        $data['status'] = $data['status'] ?? 'prospect';

        if (empty($data['plot_code'])) {
            $data['plot_code'] = $this->generatePlotCode($companyId);
        }

        return DB::transaction(function () use ($data, $sellers, $owners, $paid, $documents) {
            $plot = Plot::create($data);
            $this->syncSellers($plot, $sellers);
            $this->syncOwners($plot, $owners);
            $this->syncFormPayments($plot, $paid);
            $this->syncFormDocuments($plot, $documents);

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
        $paid = $data['paid'] ?? [];
        $documents = $data['documents'] ?? [];
        unset($data['sellers'], $data['owners'], $data['paid'], $data['documents']);

        return DB::transaction(function () use ($plot, $data, $sellers, $owners, $paid, $documents) {
            $plot->update($data);

            if ($sellers !== null) {
                $plot->sellers()->delete();
                $this->syncSellers($plot, $sellers);
            }

            if ($owners !== null) {
                $plot->owners()->delete();
                $this->syncOwners($plot, $owners);
            }

            $this->syncFormPayments($plot, $paid);
            $this->syncFormDocuments($plot, $documents);

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
     * Reconcile the auto-generated "Paid" payments driven by the plot form's
     * per-cost checkboxes. For each cost field: create/update its cash-out
     * payment when ticked (and amount > 0), or reverse and remove it otherwise.
     *
     * @param  array<string, mixed>  $paidFlags  field => "1" when marked paid
     */
    private function syncFormPayments(Plot $plot, array $paidFlags): void
    {
        foreach (self::FIELD_PAYMENT_TYPES as $field => $type) {
            $amount = round((float) $plot->$field, 2);
            $isPaid = ! empty($paidFlags[$field]) && $amount > 0;

            $payment = $plot->payments()
                ->where('payment_type', $type)
                ->where('auto_generated', true)
                ->first();

            if ($isPaid) {
                if ($payment) {
                    $payment->update(['amount' => $amount]);
                } else {
                    $payment = $plot->payments()->create([
                        'created_by' => Auth::id(),
                        'auto_generated' => true,
                        'payment_type' => $type,
                        'amount' => $amount,
                        'payment_date' => now()->toDateString(),
                        'notes' => 'Marked paid from plot form.',
                    ]);
                }

                $this->syncPaymentJournal($plot, $payment);
            } elseif ($payment) {
                JournalService::reverseReference(
                    companyId: $plot->company_id,
                    reference: $payment,
                    category: self::CATEGORY_ACQUISITION,
                    remarks: 'Reversed plot payment for ' . $plot->plot_code,
                );

                $payment->delete();
            }
        }
    }

    /**
     * Upload documents submitted from the plot form and attach them to the plot
     * via the existing polymorphic document module. Rows without a file are
     * skipped. New uploads are appended (existing documents are untouched).
     *
     * @param  array<int, array<string, mixed>>  $documents
     */
    private function syncFormDocuments(Plot $plot, array $documents): void
    {
        foreach ($documents as $document) {
            $file = $document['file'] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $categoryId = $document['category_id'] ?? null;
            $category = $categoryId ? DocumentCategory::forCompany($plot->company_id)->find($categoryId) : null;

            $meta = (new DocumentStorageService())->upload(
                $file,
                "companies/{$plot->company_id}/plots/{$plot->id}",
            );

            Document::create([
                'company_id' => $plot->company_id,
                'category_id' => $category?->id,
                'documentable_type' => Plot::class,
                'documentable_id' => $plot->id,
                'title' => ($document['title'] ?? null) ?: ($category->name ?? $meta['file_name']),
                'description' => $document['description'] ?? null,
                'file_name' => $meta['file_name'],
                'file_path' => $meta['path'],
                'file_size' => $meta['size'],
                'mime_type' => $meta['mime_type'],
                'disk' => $meta['disk'],
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Generate a unique sequential plot code for a company (e.g. PLOT-0001).
     */
    private function generatePlotCode(int $companyId): string
    {
        $count = Plot::withTrashed()->where('company_id', $companyId)->count();

        do {
            $count++;
            $code = 'PLOT-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } while (
            Plot::withTrashed()
                ->where('company_id', $companyId)
                ->where('plot_code', $code)
                ->exists()
        );

        return $code;
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
                'nid_front' => $this->resolvePersonFile($plot, $seller, 'nid_front'),
                'nid_back' => $this->resolvePersonFile($plot, $seller, 'nid_back'),
                'photo' => $this->resolvePersonFile($plot, $seller, 'photo'),
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
                'nid_front' => $this->resolvePersonFile($plot, $owner, 'nid_front'),
                'nid_back' => $this->resolvePersonFile($plot, $owner, 'nid_back'),
                'photo' => $this->resolvePersonFile($plot, $owner, 'photo'),
            ]);
        }
    }

    /**
     * Resolve a seller/owner image field: upload a newly submitted file via the
     * shared document storage, or keep the existing stored path on edit.
     *
     * @param  array<string, mixed>  $person
     */
    private function resolvePersonFile(Plot $plot, array $person, string $field): ?string
    {
        $file = $person[$field] ?? null;

        if ($file instanceof UploadedFile) {
            return (new DocumentStorageService())->upload(
                $file,
                "companies/{$plot->company_id}/plots/{$plot->id}/people",
            )['path'];
        }

        $existing = $person[$field . '_existing'] ?? null;

        return is_string($existing) && $existing !== '' ? $existing : null;
    }
}
