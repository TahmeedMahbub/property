<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A generic, polymorphic expense. Any module (company, project, plot, booking,
 * …) links its expenses here through the `expensable` morph, so a single
 * Expense module can list every expense from wherever it was created.
 */
class Expense extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_expenses';

    protected $fillable = [
        'company_id',
        'created_by',
        'category_id',
        'expensable_type',
        'expensable_id',
        'category',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $hidden = ['id'];

    /**
     * Source module labels + badge colours keyed by the morph class. A null
     * expensable_type (standalone) is treated as a company-level expense.
     */
    public const SOURCE_TYPES = [
        Company::class => ['label' => 'Company', 'color' => 'primary'],
        Plot::class => ['label' => 'Plot', 'color' => 'warning'],
        Project::class => ['label' => 'Project', 'color' => 'info'],
        PlotBooking::class => ['label' => 'Booking', 'color' => 'success'],
    ];

    /** Supported payment methods. */
    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'cheque' => 'Cheque',
        'bank_transfer' => 'Bank Transfer',
        'mobile_banking' => 'Mobile Banking',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function expensable(): MorphTo
    {
        return $this->morphTo();
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(Journal::class, 'reference');
    }

    // ─── Computed attributes ─────────────────────────────────────

    /** Human category name: the managed category, else the stored slug. */
    public function getCategoryNameAttribute(): string
    {
        if ($this->category_id && $this->expenseCategory) {
            return $this->expenseCategory->name;
        }

        return $this->category ? Str::title(str_replace('_', ' ', $this->category)) : '—';
    }

    /** Source module label: Company / Plot / Project / Booking / Other. */
    public function getSourceTypeLabelAttribute(): string
    {
        if (! $this->expensable_type) {
            return 'Company';
        }

        return self::SOURCE_TYPES[$this->expensable_type]['label'] ?? 'Other';
    }

    /** Bootstrap badge colour for the source type. */
    public function getSourceColorAttribute(): string
    {
        if (! $this->expensable_type) {
            return self::SOURCE_TYPES[Company::class]['color'];
        }

        return self::SOURCE_TYPES[$this->expensable_type]['color'] ?? 'secondary';
    }

    /** Display name of the linked source record (booking no, plot name, …). */
    public function getSourceNameAttribute(): ?string
    {
        $source = $this->expensable;

        if (! $source) {
            return null;
        }

        return match ($this->expensable_type) {
            Plot::class => $source->plot_name,
            PlotBooking::class => $source->booking_no,
            default => (string) ($source->name ?? $source->title ?? $source->uuid ?? ''),
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
