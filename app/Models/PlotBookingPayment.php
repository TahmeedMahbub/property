<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotBookingPayment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_plot_booking_payments';

    protected $fillable = [
        'booking_id',
        'installment_id',
        'created_by',
        'payment_type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $hidden = ['id'];

    /** Supported booking payment types. */
    public const TYPES = [
        'booking' => 'Booking Money',
        'installment' => 'Installment',
        'registration' => 'Registration Fee',
        'other' => 'Other',
        'full' => 'Full Payment',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(PlotBooking::class, 'booking_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PlotBookingInstallment::class, 'installment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
