<?php

namespace App\Domains\Plot\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_code' => ['required', 'string', 'max:100'],
            'plot_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:prospect,negotiation,bayna_done,registration_pending,registration_complete,development_ready'],

            // Location
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],

            // Land records
            'mouza' => ['nullable', 'string', 'max:255'],
            'jl_no' => ['nullable', 'string', 'max:100'],
            'khatian_no' => ['nullable', 'string', 'max:100'],
            'dag_no' => ['nullable', 'string', 'max:100'],

            // Land details
            'land_size' => ['nullable', 'numeric', 'min:0'],
            'land_unit' => ['required', 'in:katha,decimal,acre'],

            // Purchase information
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'price_per_katha' => ['nullable', 'numeric', 'min:0'],
            'bayna_amount' => ['nullable', 'numeric', 'min:0'],
            'registration_cost' => ['nullable', 'numeric', 'min:0'],
            'mutation_cost' => ['nullable', 'numeric', 'min:0'],
            'legal_cost' => ['nullable', 'numeric', 'min:0'],
            'broker_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:2000'],

            // Sellers
            'sellers' => ['nullable', 'array'],
            'sellers.*.name' => ['nullable', 'string', 'max:255'],
            'sellers.*.phone' => ['nullable', 'string', 'max:50'],
            'sellers.*.nid' => ['nullable', 'string', 'max:50'],
            'sellers.*.address' => ['nullable', 'string', 'max:500'],

            // Legal land owners (separate from company shareholders)
            'owners' => ['nullable', 'array'],
            'owners.*.name' => ['nullable', 'string', 'max:255'],
            'owners.*.phone' => ['nullable', 'string', 'max:50'],
            'owners.*.nid' => ['nullable', 'string', 'max:50'],
            'owners.*.address' => ['nullable', 'string', 'max:500'],
            'owners.*.ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'purchase_price', 'bayna_amount', 'registration_cost', 'mutation_cost',
            'legal_cost', 'broker_cost', 'other_cost', 'land_size',
        ] as $field) {
            if ($this->input($field) === null || $this->input($field) === '') {
                $this->merge([$field => 0]);
            }
        }
    }
}
