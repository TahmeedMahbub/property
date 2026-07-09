<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'p_customers';

    /**
     * Customer document types uploaded through the existing polymorphic
     * document system. key => human label. Stored in Document.metadata
     * under "customer_document_type".
     */
    public const DOCUMENT_TYPES = [
        'photo' => 'Photo',
        'nid_front' => 'NID Front',
        'nid_back' => 'NID Back',
        'tin' => 'TIN Copy',
        'passport' => 'Passport Copy',
        'nominee_nid' => 'Nominee NID',
        'joint_owner_photo' => 'Joint Owner Photo',
    ];

    /**
     * Fields counted towards the profile completion percentage.
     */
    public const COMPLETION_FIELDS = [
        'phone', 'email', 'present_address', 'permanent_address', 'alternative_mobile',
        'full_name_en', 'full_name_bn',
        'father_name', 'father_name_bn', 'mother_name', 'mother_name_bn',
        'date_of_birth', 'gender', 'marital_status', 'profession', 'nationality',
        'religion', 'spouse_name',
        'nid_number', 'tin_number', 'passport_number', 'driving_license_number',
        'nominee_name', 'nominee_relationship', 'nominee_mobile', 'nominee_address', 'nominee_nid_number',
        'bank_name', 'bank_account_name', 'bank_account_number',
        'emergency_contact_name', 'emergency_contact_mobile',
    ];

    /** Default lifetime (days) of a generated profile-completion link. */
    public const LINK_LIFETIME_DAYS = 30;

    /**
     * Fields a customer is allowed to fill/overwrite through the public
     * profile-completion form. Never includes name, status or any
     * verification/link column.
     */
    public const PUBLIC_FILLABLE = [
        'full_name_en', 'full_name_bn',
        'father_name', 'father_name_bn', 'mother_name', 'mother_name_bn',
        'date_of_birth', 'gender', 'marital_status', 'profession', 'nationality',
        'religion', 'spouse_name',
        'phone', 'alternative_mobile', 'email', 'present_address', 'permanent_address',
        'nid_number', 'tin_number', 'passport_number', 'driving_license_number',
        'nominee_name', 'nominee_relationship', 'nominee_mobile', 'nominee_address', 'nominee_nid_number',
        'bank_name', 'bank_account_name', 'bank_account_number',
        'emergency_contact_name', 'emergency_contact_mobile',
        'has_joint_owner', 'joint_owner_name', 'joint_owner_mobile', 'joint_owner_nid', 'joint_owner_address',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'project_id',
        'name',
        'full_name_en',
        'full_name_bn',
        'father_name',
        'father_name_bn',
        'mother_name',
        'mother_name_bn',
        'date_of_birth',
        'gender',
        'marital_status',
        'religion',
        'spouse_name',
        'profession',
        'nationality',
        'email',
        'phone',
        'alternative_mobile',
        'company_name',
        'tax_id',
        'address',
        'present_address',
        'permanent_address',
        'city',
        'state',
        'country',
        'postal_code',
        'nid_number',
        'tin_number',
        'passport_number',
        'driving_license_number',
        'nominee_name',
        'nominee_relationship',
        'nominee_mobile',
        'nominee_address',
        'nominee_nid_number',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'emergency_contact_name',
        'emergency_contact_mobile',
        'has_joint_owner',
        'joint_owner_name',
        'joint_owner_mobile',
        'joint_owner_nid',
        'joint_owner_address',
        'type',
        'credit_limit',
        'notes',
        'status',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'credit_limit' => 'decimal:2',
            'profile_link_generated_at' => 'datetime',
            'profile_link_expires_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'profile_verified_at' => 'datetime',
            'profile_locked' => 'boolean',
            'profile_completion_percentage' => 'integer',
            'has_joint_owner' => 'boolean',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(PlotBooking::class);
    }

    // ─── Attributes ──────────────────────────────────────────────

    /**
     * Live profile completion percentage (0–100) based on filled profile
     * fields plus uploaded identity documents.
     */
    public function getProfileCompletionAttribute(): int
    {
        return $this->calculateProfileCompletion();
    }

    /**
     * Compute the profile completion percentage from the current attributes
     * and uploaded documents.
     */
    public function calculateProfileCompletion(): int
    {
        $fields = self::COMPLETION_FIELDS;
        $docTypes = array_keys(self::DOCUMENT_TYPES);

        $total = count($fields) + count($docTypes);
        if ($total === 0) {
            return 0;
        }

        $filled = 0;

        foreach ($fields as $field) {
            if (filled($this->{$field})) {
                $filled++;
            }
        }

        $uploadedTypes = $this->documents
            ->map(fn (Document $doc) => $doc->metadata['customer_document_type'] ?? null)
            ->filter()
            ->unique();

        foreach ($docTypes as $type) {
            if ($uploadedTypes->contains($type)) {
                $filled++;
            }
        }

        return (int) round(($filled / $total) * 100);
    }

    /**
     * Persist the recalculated completion percentage and locking state.
     */
    public function refreshProfileState(): void
    {
        $this->profile_completion_percentage = $this->calculateProfileCompletion();
        $this->profile_locked = (bool) ($this->profile_completed_at && $this->profile_verified_at);
        $this->save();
    }

    /**
     * Generate (or regenerate) a secure profile-completion token. Any previous
     * token is overwritten and therefore immediately invalidated.
     */
    public function generateProfileLink(int $days = self::LINK_LIFETIME_DAYS): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (static::where('profile_token', $token)->exists());

        $this->forceFill([
            'profile_token' => $token,
            'profile_link_generated_at' => now(),
            'profile_link_expires_at' => now()->addDays($days),
        ])->save();

        return $token;
    }

    public function getProfileLinkAttribute(): ?string
    {
        return $this->profile_token
            ? url('/customer-profile/' . $this->profile_token)
            : null;
    }

    public function hasProfileLink(): bool
    {
        return ! empty($this->profile_token);
    }

    public function isProfileLinkExpired(): bool
    {
        return $this->profile_link_expires_at !== null
            && $this->profile_link_expires_at->isPast();
    }

    public function isProfileCompleted(): bool
    {
        return $this->profile_completed_at !== null;
    }

    /**
     * Return the uploaded document for a given customer document type, if any.
     */
    public function documentOfType(string $type): ?Document
    {
        return $this->documents
            ->first(fn (Document $doc) => ($doc->metadata['customer_document_type'] ?? null) === $type);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeLead($query)
    {
        return $query->where('status', 'lead');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeBusiness($query)
    {
        return $query->where('type', 'business');
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }
}
