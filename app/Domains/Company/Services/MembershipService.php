<?php

namespace App\Domains\Company\Services;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;

class MembershipService
{
    public function listForCompany(Company $company): mixed
    {
        return $company->memberships()
            ->with(['user:id,uuid,name,email,avatar', 'role:id,name,slug'])
            ->latest()
            ->paginate(20);
    }

    public function addMember(Company $company, array $data): CompanyMembership
    {
        $user = User::where('uuid', $data['user_id'])->firstOrFail();

        return CompanyMembership::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $data['role_id'] ?? null,
            'title' => $data['title'] ?? null,
            'department' => $data['department'] ?? null,
            'is_owner' => $data['is_owner'] ?? false,
            'joined_at' => $data['joined_at'] ?? now(),
            'status' => 'active',
        ]);
    }

    public function updateMember(CompanyMembership $membership, array $data): CompanyMembership
    {
        $membership->update($data);

        return $membership->fresh(['user', 'role']);
    }

    public function removeMember(CompanyMembership $membership): void
    {
        $membership->update([
            'status' => 'inactive',
            'left_at' => now(),
        ]);
    }
}
