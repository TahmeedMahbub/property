<?php

namespace App\Domains\Property\Services;

use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Support\Str;

class UnitTypeService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->unitTypes()->latest()->paginate(20);
    }

    public function create(Company $company, array $data): UnitType
    {
        $data['slug'] = Str::slug($data['name']);

        return $company->unitTypes()->create($data);
    }

    public function update(UnitType $unitType, array $data): UnitType
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $unitType->update($data);

        return $unitType->fresh();
    }

    public function delete(UnitType $unitType): void
    {
        $unitType->delete();
    }
}
