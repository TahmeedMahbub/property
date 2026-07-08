<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_loan_repayments';

    protected $fillable = [
        'loan_id',
        'created_by',
        'payment_date',
        'principal_paid',
        'interest_paid',
        'penalty',
        'payment_method',
        'reference_no',
        'remarks',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'principal_paid' => 'decimal:2',
            'interest_paid' => 'decimal:2',
            'penalty' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Computed attributes ─────────────────────────────────────

    /** Total cash of this repayment (principal + interest + penalty). */
    public function getTotalPaidAttribute(): float
    {
        return round((float) $this->principal_paid + (float) $this->interest_paid + (float) $this->penalty, 2);
    }
}
