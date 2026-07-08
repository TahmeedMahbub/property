<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanPaymentDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Loan $loan,
        public readonly string $dueDate,
        public readonly int $daysLeft,
        public readonly string $kind, // installment | maturity
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $label = $this->kind === 'maturity' ? 'matures' : 'installment is due';

        return [
            'type' => 'loan_due',
            'loan_uuid' => $this->loan->uuid,
            'lender_name' => $this->loan->lender_name,
            'outstanding' => $this->loan->outstanding_balance,
            'due_date' => $this->dueDate,
            'days_left' => $this->daysLeft,
            'kind' => $this->kind,
            'message' => "Loan from {$this->loan->lender_name} {$label} on {$this->dueDate} ({$this->daysLeft} days left).",
            'url' => "/loans/{$this->loan->uuid}",
        ];
    }
}
