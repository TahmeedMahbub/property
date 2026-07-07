<?php

namespace App\Domains\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'total_units' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
