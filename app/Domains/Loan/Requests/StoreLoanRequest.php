<?php

namespace App\Domains\Loan\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'exists:p_projects,uuid'],
            'lender_type' => ['required', 'in:bank,shareholder,director,third_party'],
            'lender_name' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'principal_amount' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'interest_type' => ['required', 'in:flat,reducing'],
            'emi_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'repayment_frequency' => ['required', 'in:monthly,quarterly,yearly'],
            'collateral' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,closed,defaulted'],
        ];
    }
}
