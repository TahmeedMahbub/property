<?php

namespace App\Domains\Document\Controllers;

use App\Domains\Document\Services\DocumentVersionService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentVersionController extends Controller
{
    public function __construct(
        private readonly DocumentVersionService $versionService,
    ) {}

    public function index(Company $company, Document $document): JsonResponse
    {
        $this->authorize('view', $company);

        $versions = $this->versionService->listForDocument($document);

        return response()->json(['data' => $versions]);
    }

    public function store(Request $request, Company $company, Document $document): JsonResponse
    {
        $this->authorize('update', $company);

        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'changes_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $version = $this->versionService->createVersion($document, $request->user(), $request->validated());

        return response()->json(['data' => $version], 201);
    }
}
