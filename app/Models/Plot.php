<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plot extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_plots';

    protected $fillable = [
        'company_id',
        'created_by',
        'plot_code',
        'plot_name',
        'status',
        'division',
        'district',
        'upazila',
        'area',
        'address',
        'mouza',
        'jl_no',
        'khatian_no',
        'dag_no',
        'land_size',
        'land_unit',
        'purchase_price',
        'price_per_katha',
        'bayna_amount',
        'registration_cost',
        'mutation_cost',
        'legal_cost',
        'broker_cost',
        'other_cost',
        'notes',
    ];

    protected $hidden = ['id'];

    /** Land unit → katha conversion factors (standard Bangladesh measures). */
    public const LAND_UNIT_TO_KATHA = [
        'katha' => 1.0,
        'decimal' => 0.605,
        'acre' => 60.5,
    ];

    /** Available plot statuses in lifecycle order. */
    public const STATUSES = [
        'prospect',
        'negotiation',
        'bayna_done',
        'registration_pending',
        'registration_complete',
        'development_ready',
    ];

    /** Supported document types (uses the existing polymorphic document module). */
    public const DOCUMENT_TYPES = [
        'bayna_agreement' => 'Bayna Agreement',
        'sale_deed' => 'Sale Deed',
        'previous_deed' => 'Previous Deed',
        'khatian' => 'Khatian',
        'porcha' => 'Porcha',
        'mutation' => 'Mutation',
        'mouza_map' => 'Mouza Map',
        'tax_receipt' => 'Tax Receipt',
        'nid_copy' => 'NID Copy',
        'power_of_attorney' => 'Power Of Attorney',
        'legal_opinion' => 'Legal Opinion',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'land_size' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'price_per_katha' => 'decimal:2',
            'bayna_amount' => 'decimal:2',
            'registration_cost' => 'decimal:2',
            'mutation_cost' => 'decimal:2',
            'legal_cost' => 'decimal:2',
            'broker_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
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

    public function sellers(): HasMany
    {
        return $this->hasMany(PlotSeller::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(PlotOwner::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PlotPayment::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // ─── Computed attributes ─────────────────────────────────────

    /**
     * Auto-calculated total acquisition cost.
     *
     * = purchase_price + registration_cost + mutation_cost
     *   + legal_cost + broker_cost + other_cost
     *
     * (bayna_amount is an advance against the purchase price, not an extra cost.)
     */
    public function getTotalAcquisitionCostAttribute(): float
    {
        return round(
            (float) $this->purchase_price
            + (float) $this->registration_cost
            + (float) $this->mutation_cost
            + (float) $this->legal_cost
            + (float) $this->broker_cost
            + (float) $this->other_cost,
            2
        );
    }

    /** Total cash paid against this plot across all payment types. */
    public function getTotalPaidAttribute(): float
    {
        return round((float) $this->payments->sum('amount'), 2);
    }

    /** Outstanding acquisition due = total cost − total paid. */
    public function getTotalDueAttribute(): float
    {
        return round($this->total_acquisition_cost - $this->total_paid, 2);
    }

    /** Land size normalised to katha for aggregation/reporting. */
    public function getLandSizeInKathaAttribute(): float
    {
        $factor = self::LAND_UNIT_TO_KATHA[$this->land_unit] ?? 1.0;

        return round((float) $this->land_size * $factor, 4);
    }

    /** Whether the bayna step is still pending. */
    public function getIsBaynaPendingAttribute(): bool
    {
        return in_array($this->status, ['prospect', 'negotiation'], true);
    }

    /** Whether registration is still pending. */
    public function getIsRegistrationPendingAttribute(): bool
    {
        return in_array($this->status, ['prospect', 'negotiation', 'bayna_done', 'registration_pending'], true);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
