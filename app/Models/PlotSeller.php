<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotSeller extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_plot_sellers';

    protected $fillable = [
        'plot_id',
        'name',
        'phone',
        'nid',
        'nid_front',
        'nid_back',
        'photo',
        'address',
    ];

    protected $hidden = ['id'];

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
