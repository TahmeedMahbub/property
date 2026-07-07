<?php

namespace App\Domains\Document\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function listForCompany(Company $company, ?string $folderId = null, ?string $categoryId = null): mixed
    {
        $query = $company->documents()->with(['category', 'folder', 'uploader:id,uuid,name']);

        if ($folderId !== null) {
            $query->where('folder_id', $folderId);
        }

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query->latest()->paginate(20);
    }

    public function upload(Company $company, User $user, array $data): Document
    {
        /** @var UploadedFile $file */
        $file = $data['file'];

        $path = $file->store("companies/{$company->uuid}/documents", 'local');

        return Document::create([
            'company_id' => $company->id,
            'category_id' => $data['category_id'] ?? null,
            'folder_id' => $data['folder_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'local',
            'uploaded_by' => $user->id,
            'is_public' => $data['is_public'] ?? false,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function update(Document $document, array $data): Document
    {
        $document->update($data);

        return $document->fresh(['category', 'folder']);
    }

    public function delete(Document $document): void
    {
        Storage::disk($document->disk)->delete($document->file_path);
        $document->delete();
    }

    public function attachToEntity(Document $document, string $type, int $id): void
    {
        $allowedTypes = [
            'shareholder' => \App\Models\Shareholder::class,
            'project' => \App\Models\Project::class,
            'investor' => \App\Models\ProjectInvestor::class,
            'employee' => \App\Models\Employee::class,
            'customer' => \App\Models\Customer::class,
        ];

        $modelClass = $allowedTypes[$type] ?? null;

        if (! $modelClass) {
            abort(422, 'Invalid entity type.');
        }

        $entity = $modelClass::findOrFail($id);

        $document->update([
            'documentable_type' => $modelClass,
            'documentable_id' => $entity->id,
        ]);
    }
}
