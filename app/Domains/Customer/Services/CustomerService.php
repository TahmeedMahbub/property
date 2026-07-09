<?php

namespace App\Domains\Customer\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
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
        $data = $this->resolveRelations($data, $company);

        return $company->customers()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $data = $this->resolveRelations($data, $customer->company);

        $customer->update($data);

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    /**
     * Translate UUID inputs (user_id, project_id) into internal primary keys.
     */
    private function resolveRelations(array $data, Company $company): array
    {
        if (array_key_exists('user_id', $data)) {
            $data['user_id'] = ! empty($data['user_id'])
                ? User::where('uuid', $data['user_id'])->value('id')
                : null;
        }

        if (array_key_exists('project_id', $data)) {
            $data['project_id'] = ! empty($data['project_id'])
                ? Project::where('company_id', $company->id)->where('uuid', $data['project_id'])->value('id')
                : null;
        }

        return $data;
    }
}
