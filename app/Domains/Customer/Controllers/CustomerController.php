<?php

namespace App\Domains\Customer\Controllers;

use App\Domains\Customer\Requests\StoreCustomerRequest;
use App\Domains\Customer\Requests\UpdateCustomerRequest;
use App\Domains\Customer\Services\CustomerService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $customers = $this->customerService->listForCompany($company);

        return response()->json(['data' => $customers]);
    }

    public function store(StoreCustomerRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $customer = $this->customerService->create($company, $request->validated());

        return response()->json(['data' => $customer], 201);
    }

    public function show(Company $company, Customer $customer): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json(['data' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Company $company, Customer $customer): JsonResponse
    {
        $this->authorize('update', $company);

        $customer = $this->customerService->update($customer, $request->validated());

        return response()->json(['data' => $customer]);
    }

    public function destroy(Company $company, Customer $customer): JsonResponse
    {
        $this->authorize('update', $company);

        $this->customerService->delete($customer);

        return response()->json(['message' => 'Customer removed.']);
    }
}
