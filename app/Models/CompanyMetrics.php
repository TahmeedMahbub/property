<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyMetrics extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_company_metrics';

    protected $fillable = [
        'company_id',
        'total_shares',
        'current_share_price',
        'current_valuation',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'total_shares' => 'decimal:6',
            'current_share_price' => 'decimal:6',
            'current_valuation' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
