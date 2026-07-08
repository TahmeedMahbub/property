<?php

namespace App\Console\Commands;

use App\Domains\Loan\Services\LoanReportService;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Notifications\LoanPaymentDueNotification;
use Illuminate\Console\Command;

class CheckLoanDueDates extends Command
{
    protected $signature = 'loans:check-due';

    protected $description = 'Notify company owners about loan installments/maturities due within 7 or 30 days.';

    public function handle(LoanReportService $reports): int
    {
        // Alert thresholds (days). Maturities and installments both use these.
        $thresholds = [7, 30];

        $companies = Company::query()->get();
        $sent = 0;

        foreach ($companies as $company) {
            $upcoming = $reports->upcomingPayments($company->id, 30);

            if ($upcoming->isEmpty()) {
                continue;
            }

            $owners = $this->companyRecipients($company->id);

            if ($owners->isEmpty()) {
                continue;
            }

            foreach ($upcoming as $row) {
                $daysLeft = (int) $row['days_left'];

                // Only fire on the alert windows (due in <=7 or <=30 days).
                if (! $this->matchesThreshold($daysLeft, $thresholds)) {
                    continue;
                }

                foreach ($owners as $owner) {
                    $owner->notify(new LoanPaymentDueNotification(
                        loan: $row['loan'],
                        dueDate: $row['due_date']->format('d M Y'),
                        daysLeft: $daysLeft,
                        kind: $row['kind'],
                    ));
                    $sent++;
                }
            }
        }

        $this->info("Loan due-date check complete. {$sent} notification(s) sent.");

        return self::SUCCESS;
    }

    /**
     * Fire when the days left crosses a 7-day or 30-day window boundary.
     */
    private function matchesThreshold(int $daysLeft, array $thresholds): bool
    {
        return in_array($daysLeft, $thresholds, true);
    }

    /**
     * Owners (and admins) of a company who should receive loan alerts.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function companyRecipients(int $companyId)
    {
        return CompanyMembership::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('is_owner', true)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();
    }
}
