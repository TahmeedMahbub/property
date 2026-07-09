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

class PlotBooking extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_plot_bookings';

    protected $fillable = [
        'company_id',
        'plot_id',
        'customer_id',
        'created_by',
        'booking_no',
        'shares_count',
        'share_price',
        'booking_money',
        'registration_fee',
        'other_fee',
        'discount',
        'other_info',
        'booking_date',
        'status',
        'notes',
    ];

    protected $hidden = ['id'];

    /** Booking lifecycle statuses. */
    public const STATUSES = ['booked', 'active', 'completed', 'cancelled'];

    protected function casts(): array
    {
        return [
            'shares_count' => 'integer',
            'share_price' => 'decimal:2',
            'booking_money' => 'decimal:2',
            'registration_fee' => 'decimal:2',
            'other_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'booking_date' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PlotBookingInstallment::class, 'booking_id')->orderBy('installment_no');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PlotBookingPayment::class, 'booking_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // ─── Computed attributes ─────────────────────────────────────

    /** Value of the purchased shares = shares_count × share_price. */
    public function getShareValueAttribute(): float
    {
        return round((float) $this->shares_count * (float) $this->share_price, 2);
    }

    /** Total amount the customer must pay (shares + fees − discount). */
    public function getTotalPayableAttribute(): float
    {
        return round(
            $this->share_value
            + (float) $this->registration_fee
            + (float) $this->other_fee
            - (float) $this->discount,
            2
        );
    }

    /** Total cash received across all payments. */
    public function getTotalPaidAttribute(): float
    {
        return round((float) $this->payments->sum('amount'), 2);
    }

    /** Outstanding balance still owed by the customer. */
    public function getTotalDueAttribute(): float
    {
        return round($this->total_payable - $this->total_paid, 2);
    }

    /** Whether the booking is fully paid. */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->total_due <= 0;
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNot('status', 'cancelled');
    }
}
