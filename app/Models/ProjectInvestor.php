<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectInvestor extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_project_investors';

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'email',
        'phone',
        'investment_amount',
        'investment_percentage',
        'investment_type',
        'invested_at',
        'expected_return',
        'notes',
        'status',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'investment_percentage' => 'decimal:4',
            'expected_return' => 'decimal:2',
            'invested_at' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
