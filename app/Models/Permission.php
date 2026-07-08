<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'p_permissions';

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    protected $hidden = ['id'];

    // ─── Relationships ───────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'p_role_permissions');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
