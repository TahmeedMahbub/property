<?php

namespace App\Domains\Plot\Services;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\PlotBooking;
use App\Models\PlotBookingPayment;
use App\Services\DocumentStorageService;
use App\Services\JournalService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Plot share sale / booking lifecycle: a customer buys one or more shares
 * (per share = per flat) of a plot, gives booking money and then pays the
 * balance in installments or in full, plus registration and other fees.
 *
 * Accounting: the company ledger (Journal) is a single cash/bank account where
 * a credit is cash-in and a debit is cash-out. Every payment received from a
 * customer is cash entering the company, so each booking payment posts a
 * CREDIT under the `plot_share_sale` category. This is the OPPOSITE direction
 * of {@see PlotService}, whose acquisition payments are cash leaving the
 * company (debits).
 *
 * Outstanding due (total_payable − total_paid) is derived and NOT posted to the
 * cash ledger, which tracks realised cash only.
 */
class PlotBookingService
{
    public const CATEGORY = 'plot_share_sale';

    /**
     * Create a booking with its manual installment schedule and certificates.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): PlotBooking
    {
        $installments = $data['installments'] ?? [];
        $documents = $data['documents'] ?? [];
        unset($data['installments'], $data['documents']);

        $data['company_id'] = $companyId;
        $data['created_by'] = $data['created_by'] ?? Auth::id();
        $data['status'] = $data['status'] ?? 'booked';

        if (empty($data['booking_no'])) {
            $data['booking_no'] = $this->generateBookingNo($companyId);
        }

        return DB::transaction(function () use ($data, $installments, $documents) {
            $this->guardShareAvailability($data['plot_id'], (int) ($data['shares_count'] ?? 0), $data['company_id']);

            $booking = PlotBooking::create($data);
            $this->syncInstallments($booking, $installments);
            $this->syncDocuments($booking, $documents);

            return $booking;
        });
    }

    /**
     * Update a booking and re-sync its installment schedule and certificates.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(PlotBooking $booking, array $data): PlotBooking
    {
        $installments = $data['installments'] ?? null;
        $documents = $data['documents'] ?? [];
        unset($data['installments'], $data['documents']);

        return DB::transaction(function () use ($booking, $data, $installments, $documents) {
            $newShares = (int) ($data['shares_count'] ?? $booking->shares_count);
            $newPlotId = (int) ($data['plot_id'] ?? $booking->plot_id);

            // Only the newly requested shares beyond what this booking already
            // holds on the same plot need to fit within availability.
            $alreadyHeld = $newPlotId === (int) $booking->plot_id ? (int) $booking->shares_count : 0;
            $this->guardShareAvailability($newPlotId, $newShares, $booking->company_id, $alreadyHeld);

            $booking->update($data);

            if ($installments !== null) {
                $booking->installments()->delete();
                $this->syncInstallments($booking, $installments);
            }

            $this->syncDocuments($booking, $documents);

            return $booking->refresh();
        });
    }

    /**
     * Record a payment received from the customer and post the cash-in entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(PlotBooking $booking, array $data): PlotBookingPayment
    {
        return DB::transaction(function () use ($booking, $data) {
            $data['booking_id'] = $booking->id;
            $data['created_by'] = $data['created_by'] ?? Auth::id();

            $payment = PlotBookingPayment::create($data);

            $this->syncPaymentJournal($booking, $payment);

            return $payment;
        });
    }

    /**
     * Delete a payment and reverse its cash-in ledger entry.
     */
    public function deletePayment(PlotBookingPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $booking = $payment->booking;

            if ($booking) {
                JournalService::reverseReference(
                    companyId: $booking->company_id,
                    reference: $payment,
                    category: self::CATEGORY,
                    remarks: 'Reversed booking payment for ' . $booking->booking_no,
                );
            }

            $payment->delete();
        });
    }

    /**
     * Delete a booking: reverse every payment posted to the ledger, then
     * soft-delete the booking (installments/payments cascade at DB level).
     */
    public function delete(PlotBooking $booking): void
    {
        DB::transaction(function () use ($booking) {
            foreach ($booking->payments as $payment) {
                JournalService::reverseReference(
                    companyId: $booking->company_id,
                    reference: $payment,
                    category: self::CATEGORY,
                    remarks: 'Reversed booking payment for ' . $booking->booking_no,
                );
            }

            $booking->delete();
        });
    }

    /**
     * Post the ledger credit (cash in) for a booking payment.
     */
    private function syncPaymentJournal(PlotBooking $booking, PlotBookingPayment $payment): void
    {
        JournalService::syncReference(
            companyId: $booking->company_id,
            reference: $payment,
            // Positive target credit ⇒ a credit (cash in) of the payment amount.
            targetCredit: (float) $payment->amount,
            category: self::CATEGORY,
            remarks: ucfirst($payment->payment_type) . ' payment for booking ' . $booking->booking_no,
            userId: $payment->created_by,
        );
    }

    /**
     * Ensure a plot has enough unbooked shares for the requested quantity.
     */
    private function guardShareAvailability(int $plotId, int $requested, int $companyId, int $alreadyHeld = 0): void
    {
        if ($requested < 1) {
            throw new RuntimeException('At least one share must be booked.');
        }

        $plot = \App\Models\Plot::forCompany($companyId)->with('bookings')->findOrFail($plotId);

        $available = $plot->shares_available + $alreadyHeld;

        if ($requested > $available) {
            throw new RuntimeException(
                "Only {$available} share(s) are available for {$plot->plot_name}."
            );
        }
    }

    /**
     * Recreate the manual installment rows for a booking. Rows without an
     * amount are skipped. Installment numbers are re-sequenced.
     *
     * @param  array<int, array<string, mixed>>  $installments
     */
    private function syncInstallments(PlotBooking $booking, array $installments): void
    {
        $no = 0;

        foreach ($installments as $installment) {
            $amount = round((float) ($installment['amount'] ?? 0), 2);
            $dueDate = $installment['due_date'] ?? null;

            if ($amount <= 0 && empty($dueDate)) {
                continue;
            }

            $no++;

            $booking->installments()->create([
                'created_by' => Auth::id(),
                'installment_no' => $no,
                'title' => $installment['title'] ?? ('Installment ' . $no),
                'due_date' => $dueDate ?: null,
                'amount' => $amount,
                'notes' => $installment['notes'] ?? null,
            ]);
        }
    }

    /**
     * Upload certificate/agreement files submitted from the booking form and
     * attach them via the shared polymorphic document module. Rows without a
     * file are skipped. New uploads are appended (existing docs untouched).
     *
     * @param  array<int, array<string, mixed>>  $documents
     */
    private function syncDocuments(PlotBooking $booking, array $documents): void
    {
        foreach ($documents as $document) {
            $file = $document['file'] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $categoryId = $document['category_id'] ?? null;
            $category = $categoryId ? DocumentCategory::forCompany($booking->company_id)->find($categoryId) : null;

            $meta = (new DocumentStorageService())->upload(
                $file,
                "companies/{$booking->company_id}/bookings/{$booking->id}",
            );

            Document::create([
                'company_id' => $booking->company_id,
                'category_id' => $category?->id,
                'documentable_type' => PlotBooking::class,
                'documentable_id' => $booking->id,
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
     * Generate a unique sequential booking number for a company (e.g. BKG-0001).
     */
    public function generateBookingNo(int $companyId): string
    {
        $count = PlotBooking::withTrashed()->where('company_id', $companyId)->count();

        do {
            $count++;
            $code = 'BKG-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } while (
            PlotBooking::withTrashed()
                ->where('company_id', $companyId)
                ->where('booking_no', $code)
                ->exists()
        );

        return $code;
    }
}
