<?php

namespace App\Domains\Employee\Controllers;

use App\Domains\Employee\Requests\StoreEmployeeRequest;
use App\Domains\Employee\Requests\UpdateEmployeeRequest;
use App\Domains\Employee\Services\EmployeeService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $employees = $this->employeeService->listForCompany($company);

        return response()->json(['data' => $employees]);
    }

    public function store(StoreEmployeeRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $employee = $this->employeeService->create($company, $request->validated());

        return response()->json(['data' => $employee], 201);
    }

    public function show(Company $company, Employee $employee): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json(['data' => $employee->load('user')]);
    }

    public function update(UpdateEmployeeRequest $request, Company $company, Employee $employee): JsonResponse
    {
        $this->authorize('update', $company);

        $employee = $this->employeeService->update($employee, $request->validated());

        return response()->json(['data' => $employee]);
    }

    public function destroy(Company $company, Employee $employee): JsonResponse
    {
        $this->authorize('update', $company);

        $this->employeeService->delete($employee);

        return response()->json(['message' => 'Employee removed.']);
    }
}
