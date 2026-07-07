<?php

namespace App\Domains\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id_number' => ['nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'date_of_joining' => ['sometimes', 'date'],
            'date_of_leaving' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'salary_type' => ['nullable', 'in:monthly,hourly,daily,weekly'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:active,on_leave,resigned,terminated'],
        ];
    }
}
