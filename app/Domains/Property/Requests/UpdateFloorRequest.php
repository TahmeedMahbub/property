<?php

namespace App\Domains\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'floor_number' => ['sometimes', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'total_units' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
