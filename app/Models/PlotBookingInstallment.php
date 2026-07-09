<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlotBookingInstallment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_plot_booking_installments';

    protected $fillable = [
        'booking_id',
        'created_by',
        'installment_no',
        'title',
        'due_date',
        'amount',
        'notes',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'installment_no' => 'integer',
            'due_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(PlotBooking::class, 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PlotBookingPayment::class, 'installment_id');
    }

    // ─── Computed attributes ─────────────────────────────────────

    /** Cash received against this specific installment. */
    public function getPaidAmountAttribute(): float
    {
        return round((float) $this->payments->sum('amount'), 2);
    }

    /** Remaining amount owed for this installment. */
    public function getDueAmountAttribute(): float
    {
        return round((float) $this->amount - $this->paid_amount, 2);
    }

    /** Derived payment status for this installment. */
    public function getStatusAttribute(): string
    {
        if ($this->due_amount <= 0) {
            return 'paid';
        }

        if ($this->paid_amount > 0) {
            return 'partial';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'overdue';
        }

        return 'pending';
    }
}
