<?php

namespace App\Domains\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_floors' => ['sometimes', 'integer', 'min:0'],
            'total_units' => ['sometimes', 'integer', 'min:0'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'in:planning,under_construction,completed,handed_over'],
        ];
    }
}
