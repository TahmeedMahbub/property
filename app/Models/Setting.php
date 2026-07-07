<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'company_id',
        'group',
        'key',
        'value',
    ];

    protected $hidden = ['id'];

    // ─── Relationships ───────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
