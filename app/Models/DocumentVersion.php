<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    protected $table = 'p_document_versions';

    protected $fillable = [
        'document_id',
        'version_number',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'changes_summary',
        'uploaded_by',
    ];

    protected $hidden = ['id'];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version_number' => 'integer',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
