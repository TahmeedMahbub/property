<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /** Supported expense categories. */
    public const CATEGORIES = [
        'registration' => 'Registration Fee',
        'other' => 'Other Fee',
        'commission' => 'Commission',
        'salary' => 'Salary',
        'utility' => 'Utility',
        'maintenance' => 'Maintenance',
        'general' => 'General',
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

    public function expensable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
