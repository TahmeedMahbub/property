<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_companies';

    protected $fillable = [
        'name',
        'legal_name',
        'registration_number',
        'tax_id',
        'type',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'logo',
        'currency',
        'fiscal_year_start_month',
        'status',
        'settings',
        'founded_at',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'founded_at' => 'date',
            'fiscal_year_start_month' => 'integer',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'p_company_memberships')
            ->withPivot(['role_id', 'title', 'department', 'is_owner', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function shareholders(): HasMany
    {
        return $this->hasMany(Shareholder::class);
    }

    public function metrics(): HasOne
    {
        return $this->hasOne(CompanyMetrics::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function unitTypes(): HasMany
    {
        return $this->hasMany(UnitType::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function documentCategories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    public function documentFolders(): HasMany
    {
        return $this->hasMany(DocumentFolder::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function owners()
    {
        return $this->members()->wherePivot('is_owner', true);
    }

    public function activeMembers()
    {
        return $this->members()->wherePivot('status', 'active');
    }
}
