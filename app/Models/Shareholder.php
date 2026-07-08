<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shareholder extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_shareholders';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'email',
        'phone',
        'share_percentage',
        'share_amount',
        'share_type',
        'acquired_at',
        'notes',
        'status',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'share_percentage' => 'decimal:4',
            'share_amount' => 'decimal:2',
            'acquired_at' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
