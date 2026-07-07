<?php

namespace App\Domains\Shareholder\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShareholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'share_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'share_amount' => ['nullable', 'numeric', 'min:0'],
            'share_type' => ['nullable', 'in:common,preferred,convertible'],
            'acquired_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive,transferred'],
        ];
    }
}
