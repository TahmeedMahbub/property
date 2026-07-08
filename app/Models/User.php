<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $table = 'p_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_super_admin',
        'status',
    ];

    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'p_company_memberships')
            ->withPivot(['role_id', 'title', 'department', 'is_owner', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function shareholders(): HasMany
    {
        return $this->hasMany(Shareholder::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(ProjectInvestor::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function customerProfiles(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    public function isOwnerOf(Company $company): bool
    {
        return $this->memberships()
            ->where('company_id', $company->id)
            ->where('is_owner', true)
            ->exists();
    }

    public function activeMemberships()
    {
        return $this->memberships()->where('status', 'active');
    }

    public function membershipFor(Company $company): ?CompanyMembership
    {
        return $this->memberships()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->first();
    }

    public function hasPermissionIn(Company $company, string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isOwnerOf($company)) {
            return true;
        }

        $membership = app()->bound('currentMembership')
            ? app('currentMembership')
            : $this->membershipFor($company);

        if (! $membership || ! $membership->role) {
            return false;
        }

        return $membership->role->permissions()
            ->where('slug', $permissionSlug)
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
