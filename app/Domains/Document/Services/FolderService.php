<?php

namespace App\Domains\Document\Services;

use App\Models\Company;
use App\Models\DocumentFolder;
use App\Models\User;

class FolderService
{
    public function listForCompany(Company $company, ?string $parentId = null): mixed
    {
        $query = $company->documentFolders();

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } else {
            $query->roots();
        }

        return $query->orderBy('name')->get();
    }

    public function create(Company $company, User $user, array $data): DocumentFolder
    {
        $parent = null;
        $path = $data['name'];

        if (! empty($data['parent_id'])) {
            $parent = DocumentFolder::findOrFail($data['parent_id']);
            $path = $parent->path . '/' . $data['name'];
        }

        return DocumentFolder::create([
            'company_id' => $company->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'path' => $path,
            'created_by' => $user->id,
        ]);
    }

    public function delete(DocumentFolder $folder): void
    {
        $folder->delete();
    }
}
