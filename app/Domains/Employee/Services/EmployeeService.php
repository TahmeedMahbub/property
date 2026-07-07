<?php

namespace App\Domains\Employee\Services;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;

class EmployeeService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->employees()
            ->with('user:id,uuid,name,email')
            ->latest()
            ->paginate(20);
    }

    public function create(Company $company, array $data): Employee
    {
        if (! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        return $company->employees()->create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee->fresh('user');
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}
