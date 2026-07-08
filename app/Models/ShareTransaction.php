<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransaction extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'p_share_transactions';

    protected $fillable = [
        'company_id',
        'shareholder_id',
        'related_shareholder_id',
        'user_id',
        'type',
        'investment_amount',
        'share_price',
        'shares_issued',
        'notes',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'share_price' => 'decimal:6',
            'shares_issued' => 'decimal:6',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class);
    }

    public function relatedShareholder(): BelongsTo
    {
        return $this->belongsTo(Shareholder::class, 'related_shareholder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
