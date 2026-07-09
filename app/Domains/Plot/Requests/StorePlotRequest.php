<?php

namespace App\Domains\Plot\Requests;

use App\Models\DocumentCategory;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_code' => ['nullable', 'string', 'max:100'],
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

            // Share division (per share = per flat) for customer bookings
            'total_shares' => ['nullable', 'integer', 'min:0'],

            // "Paid" flags per cost field (records a cash-out payment when checked)
            'paid' => ['nullable', 'array'],
            'paid.*' => ['nullable', 'in:0,1'],

            'notes' => ['nullable', 'string', 'max:2000'],

            // Sellers
            'sellers' => ['nullable', 'array'],
            'sellers.*.name' => ['nullable', 'string', 'max:255'],
            'sellers.*.phone' => ['nullable', 'string', 'max:50'],
            'sellers.*.nid' => ['nullable', 'string', 'max:50'],
            'sellers.*.address' => ['nullable', 'string', 'max:500'],
            'sellers.*.nid_front' => ['nullable', 'image', 'max:3072'],
            'sellers.*.nid_back' => ['nullable', 'image', 'max:3072'],
            'sellers.*.photo' => ['nullable', 'image', 'max:3072'],
            'sellers.*.nid_front_existing' => ['nullable', 'string', 'max:255'],
            'sellers.*.nid_back_existing' => ['nullable', 'string', 'max:255'],
            'sellers.*.photo_existing' => ['nullable', 'string', 'max:255'],

            // Legal land owners (separate from company shareholders)
            'owners' => ['nullable', 'array'],
            'owners.*.name' => ['nullable', 'string', 'max:255'],
            'owners.*.phone' => ['nullable', 'string', 'max:50'],
            'owners.*.nid' => ['nullable', 'string', 'max:50'],
            'owners.*.address' => ['nullable', 'string', 'max:500'],
            'owners.*.ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'owners.*.nid_front' => ['nullable', 'image', 'max:3072'],
            'owners.*.nid_back' => ['nullable', 'image', 'max:3072'],
            'owners.*.photo' => ['nullable', 'image', 'max:3072'],
            'owners.*.nid_front_existing' => ['nullable', 'string', 'max:255'],
            'owners.*.nid_back_existing' => ['nullable', 'string', 'max:255'],
            'owners.*.photo_existing' => ['nullable', 'string', 'max:255'],

            // Documents (image/PDF, max 3 MB each). Category comes from the DB.
            'documents' => ['nullable', 'array'],
            'documents.*.category_id' => [
                'nullable',
                'required_with:documents.*.file',
                Rule::exists('p_document_categories', 'id')->where(
                    fn ($q) => $q->where(fn ($w) => $w->where('company_id', $this->companyId())->orWhereNull('company_id')),
                ),
            ],
            'documents.*.title' => ['nullable', 'string', 'max:255'],
            'documents.*.description' => ['nullable', 'string', 'max:2000'],
            'documents.*.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'],
        ];
    }

    /**
     * Selecting the "Other Document" category requires a custom title and
     * description for that row.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $otherId = DocumentCategory::forCompany($this->companyId())
                    ->where('slug', DocumentCategorySeeder::OTHER_SLUG)
                    ->value('id');

                foreach ((array) $this->input('documents', []) as $i => $doc) {
                    if (empty($doc['file']) && ! $this->hasFile("documents.{$i}.file")) {
                        continue;
                    }

                    if ($otherId && (int) ($doc['category_id'] ?? 0) === (int) $otherId) {
                        if (empty($doc['title'])) {
                            $validator->errors()->add("documents.{$i}.title", 'A title is required for an "Other Document".');
                        }
                        if (empty($doc['description'])) {
                            $validator->errors()->add("documents.{$i}.description", 'A description is required for an "Other Document".');
                        }
                    }
                }
            },
        ];
    }

    private function companyId(): ?int
    {
        return app()->bound('currentCompany') ? app('currentCompany')->id : null;
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
