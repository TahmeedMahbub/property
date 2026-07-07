<?php

namespace App\Domains\Company\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,uuid'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_owner' => ['nullable', 'boolean'],
            'joined_at' => ['nullable', 'date'],
        ];
    }
}
