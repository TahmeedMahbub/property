<?php

namespace App\Domains\Shareholder\Controllers;

use App\Domains\Shareholder\Requests\StoreShareholderRequest;
use App\Domains\Shareholder\Requests\UpdateShareholderRequest;
use App\Domains\Shareholder\Services\ShareholderService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Shareholder;
use Illuminate\Http\JsonResponse;

class ShareholderController extends Controller
{
    public function __construct(
        private readonly ShareholderService $shareholderService,
    ) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('viewShareholders', $company);

        $shareholders = $this->shareholderService->listForCompany($company);

        return response()->json(['data' => $shareholders]);
    }

    public function store(StoreShareholderRequest $request, Company $company): JsonResponse
    {
        $this->authorize('manageShareholders', $company);

        $shareholder = $this->shareholderService->create($company, $request->validated());

        return response()->json(['data' => $shareholder], 201);
    }

    public function show(Company $company, Shareholder $shareholder): JsonResponse
    {
        $this->authorize('viewShareholders', $company);

        return response()->json(['data' => $shareholder->load('user')]);
    }

    public function update(UpdateShareholderRequest $request, Company $company, Shareholder $shareholder): JsonResponse
    {
        $this->authorize('manageShareholders', $company);

        $shareholder = $this->shareholderService->update($shareholder, $request->validated());

        return response()->json(['data' => $shareholder]);
    }

    public function destroy(Company $company, Shareholder $shareholder): JsonResponse
    {
        $this->authorize('manageShareholders', $company);

        $this->shareholderService->delete($shareholder);

        return response()->json(['message' => 'Shareholder removed.']);
    }
}
