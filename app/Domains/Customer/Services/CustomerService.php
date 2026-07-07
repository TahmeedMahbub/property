<?php

namespace App\Domains\Customer\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;

class CustomerService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->customers()
            ->latest()
            ->paginate(20);
    }

    public function create(Company $company, array $data): Customer
    {
        if (! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        return $company->customers()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
