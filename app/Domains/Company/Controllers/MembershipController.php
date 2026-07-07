<?php

namespace App\Domains\Company\Controllers;

use App\Domains\Company\Requests\StoreMembershipRequest;
use App\Domains\Company\Requests\UpdateMembershipRequest;
use App\Domains\Company\Services\MembershipService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMembership;
use Illuminate\Http\JsonResponse;

class MembershipController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('viewMembers', $company);

        $members = $this->membershipService->listForCompany($company);

        return response()->json(['data' => $members]);
    }

    public function store(StoreMembershipRequest $request, Company $company): JsonResponse
    {
        $this->authorize('manageMembers', $company);

        $membership = $this->membershipService->addMember($company, $request->validated());

        return response()->json(['data' => $membership], 201);
    }

    public function update(UpdateMembershipRequest $request, Company $company, CompanyMembership $membership): JsonResponse
    {
        $this->authorize('manageMembers', $company);

        $membership = $this->membershipService->updateMember($membership, $request->validated());

        return response()->json(['data' => $membership]);
    }

    public function destroy(Company $company, CompanyMembership $membership): JsonResponse
    {
        $this->authorize('manageMembers', $company);

        $this->membershipService->removeMember($membership);

        return response()->json(['message' => 'Member removed.']);
    }
}
