<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyMembership extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
        'title',
        'department',
        'is_owner',
        'joined_at',
        'left_at',
        'status',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'joined_at' => 'date',
            'left_at' => 'date',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'membership_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOwners($query)
    {
        return $query->where('is_owner', true);
    }
}
