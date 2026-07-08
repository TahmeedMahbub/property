<?php

namespace App\Domains\Loan\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'principal_paid' => ['required', 'numeric', 'min:0'],
            'interest_paid' => ['required', 'numeric', 'min:0'],
            'penalty' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,cheque,bank_transfer,mobile_banking,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((float) $this->input('principal_paid', 0) + (float) $this->input('interest_paid', 0) + (float) $this->input('penalty', 0) <= 0) {
                $validator->errors()->add('principal_paid', 'Enter a principal, interest or penalty amount greater than zero.');
            }
        });
    }
}
