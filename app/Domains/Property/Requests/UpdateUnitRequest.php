<?php

namespace App\Domains\Property\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_type_id' => ['nullable', 'exists:unit_types,uuid'],
            'unit_number' => ['sometimes', 'string', 'max:50'],
            'size' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'facing' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'in:available,reserved,booked,sold,handovered'],
            'description' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
