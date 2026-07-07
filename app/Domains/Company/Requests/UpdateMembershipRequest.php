<?php

namespace App\Domains\Company\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['nullable', 'exists:roles,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_owner' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
        ];
    }
}
