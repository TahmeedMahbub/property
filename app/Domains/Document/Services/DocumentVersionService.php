<?php

namespace App\Domains\Document\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class DocumentVersionService
{
    public function listForDocument(Document $document): mixed
    {
        return $document->versions()->with('uploader:id,uuid,name')->get();
    }

    public function createVersion(Document $document, User $user, array $data): DocumentVersion
    {
        /** @var UploadedFile $file */
        $file = $data['file'];

        $nextVersion = ($document->versions()->max('version_number') ?? 0) + 1;

        $path = $file->store(
            "companies/{$document->company->uuid}/documents/versions",
            'local'
        );

        return DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $nextVersion,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'changes_summary' => $data['changes_summary'] ?? null,
            'uploaded_by' => $user->id,
        ]);
    }
}
