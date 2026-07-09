<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A legal land owner of a plot. Ownership percentage is the share of the LAND,
 * which is entirely separate from company shareholders / equity ownership.
 */
class PlotOwner extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_plot_owners';

    protected $fillable = [
        'plot_id',
        'name',
        'phone',
        'nid',
        'nid_front',
        'nid_back',
        'photo',
        'address',
        'ownership_percentage',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'ownership_percentage' => 'decimal:4',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
