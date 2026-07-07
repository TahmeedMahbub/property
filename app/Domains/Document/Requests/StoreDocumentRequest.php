<?php

namespace App\Domains\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'], // 50MB max
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'folder_id' => ['nullable', 'exists:document_folders,id'],
            'is_public' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
