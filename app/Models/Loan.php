<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_loans';

    protected $fillable = [
        'company_id',
        'project_id',
        'created_by',
        'lender_type',
        'lender_name',
        'reference_no',
        'principal_amount',
        'interest_rate',
        'interest_type',
        'emi_amount',
        'start_date',
        'end_date',
        'repayment_frequency',
        'collateral',
        'notes',
        'status',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:4',
            'emi_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    // ─── Computed attributes ─────────────────────────────────────

    /** Total principal repaid so far. */
    public function getTotalPrincipalPaidAttribute(): float
    {
        return (float) $this->repayments->sum('principal_paid');
    }

    /** Total interest paid so far (interest expense). */
    public function getTotalInterestPaidAttribute(): float
    {
        return (float) $this->repayments->sum('interest_paid');
    }

    /** Total penalty/late fees paid so far. */
    public function getTotalPenaltyPaidAttribute(): float
    {
        return (float) $this->repayments->sum('penalty');
    }

    /** Outstanding principal balance = principal - principal repaid. */
    public function getOutstandingBalanceAttribute(): float
    {
        return round((float) $this->principal_amount - $this->total_principal_paid, 2);
    }

    /** Grand total cash paid against this loan (principal + interest + penalty). */
    public function getTotalPaidAttribute(): float
    {
        return round($this->total_principal_paid + $this->total_interest_paid + $this->total_penalty_paid, 2);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
