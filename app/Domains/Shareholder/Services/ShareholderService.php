<?php

namespace App\Domains\Shareholder\Services;

use App\Models\Company;
use App\Models\Shareholder;
use App\Models\User;

class ShareholderService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->shareholders()
            ->with('user:id,uuid,name,email')
            ->latest()
            ->paginate(20);
    }

    public function create(Company $company, array $data): Shareholder
    {
        if (! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        return $company->shareholders()->create($data);
    }

    public function update(Shareholder $shareholder, array $data): Shareholder
    {
        if (array_key_exists('user_id', $data) && ! empty($data['user_id'])) {
            $user = User::where('uuid', $data['user_id'])->first();
            $data['user_id'] = $user?->id;
        }

        $shareholder->update($data);

        return $shareholder->fresh('user');
    }

    public function delete(Shareholder $shareholder): void
    {
        $shareholder->delete();
    }
}
