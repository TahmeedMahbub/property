<?php

namespace App\Domains\Plot\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlotPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => ['required', 'in:bayna,land,registration,legal,mutation,broker,other,extra'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,cheque,bank_transfer,mobile_banking,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
