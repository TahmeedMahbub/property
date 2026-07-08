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
            'user_id' => ['nullable', 'exists:p_users,uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'investment_amount' => ['required', 'numeric', 'min:0'],
            'share_type' => ['nullable', 'in:common,preferred,convertible'],
            'acquired_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
