<?php

namespace App\Domains\Document\Controllers;

use App\Domains\Document\Requests\StoreFolderRequest;
use App\Domains\Document\Services\FolderService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DocumentFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(
        private readonly FolderService $folderService,
    ) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $folders = $this->folderService->listForCompany($company, $request->query('parent_id'));

        return response()->json(['data' => $folders]);
    }

    public function store(StoreFolderRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $folder = $this->folderService->create($company, $request->user(), $request->validated());

        return response()->json(['data' => $folder], 201);
    }

    public function show(Company $company, DocumentFolder $folder): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json([
            'data' => $folder->load(['children', 'documents']),
        ]);
    }

    public function destroy(Company $company, DocumentFolder $folder): JsonResponse
    {
        $this->authorize('update', $company);

        $this->folderService->delete($folder);

        return response()->json(['message' => 'Folder deleted.']);
    }
}
