<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_projects';

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'type',
        'budget',
        'start_date',
        'end_date',
        'location',
        'address',
        'city',
        'status',
        'settings',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'settings' => 'array',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function investors(): HasMany
    {
        return $this->hasMany(ProjectInvestor::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
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

    // ─── Helpers ─────────────────────────────────────────────────

    public function totalInvestment(): float
    {
        return (float) $this->investors()->active()->sum('investment_amount');
    }
}
