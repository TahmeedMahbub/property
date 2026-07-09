<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotPayment extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_plot_payments';

    protected $fillable = [
        'plot_id',
        'created_by',
        'auto_generated',
        'payment_type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $hidden = ['id'];

    /** Supported plot payment types. */
    public const TYPES = [
        'bayna' => 'Bayna Payment',
        'land' => 'Land Payment',
        'registration' => 'Registration Payment',
        'legal' => 'Legal Payment',
        'mutation' => 'Mutation Payment',
        'broker' => 'Broker Payment',
        'other' => 'Other Payment',
        'extra' => "Extra Payment (Don't Affect on Due)",
    ];

    /** Payment types that are extra cash-outs and do NOT reduce the acquisition due. */
    public const NON_DUE_TYPES = ['extra'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'auto_generated' => 'boolean',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
