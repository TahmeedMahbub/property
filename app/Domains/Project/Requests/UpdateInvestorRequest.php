<?php

namespace App\Domains\Project\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvestorRequest extends FormRequest
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
            'investment_amount' => ['sometimes', 'numeric', 'min:0'],
            'investment_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'investment_type' => ['nullable', 'in:equity,debt,convertible'],
            'invested_at' => ['nullable', 'date'],
            'expected_return' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive,withdrawn'],
        ];
    }
}
