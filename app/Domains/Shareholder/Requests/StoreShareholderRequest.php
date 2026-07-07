<?php

namespace App\Domains\Shareholder\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'share_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'share_amount' => ['nullable', 'numeric', 'min:0'],
            'share_type' => ['nullable', 'in:common,preferred,convertible'],
            'acquired_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
