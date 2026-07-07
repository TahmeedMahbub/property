<?php

namespace App\Domains\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'folder_id' => ['nullable', 'exists:document_folders,id'],
            'is_public' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
