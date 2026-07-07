<?php

namespace App\Domains\Document\Controllers;

use App\Domains\Document\Requests\StoreDocumentRequest;
use App\Domains\Document\Requests\UpdateDocumentRequest;
use App\Domains\Document\Services\DocumentService;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $documents = $this->documentService->listForCompany(
            $company,
            $request->query('folder_id'),
            $request->query('category_id'),
        );

        return response()->json(['data' => $documents]);
    }

    public function store(StoreDocumentRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $document = $this->documentService->upload($company, $request->user(), $request->validated());

        return response()->json(['data' => $document], 201);
    }

    public function show(Company $company, Document $document): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json([
            'data' => $document->load(['category', 'folder', 'uploader', 'versions']),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Company $company, Document $document): JsonResponse
    {
        $this->authorize('update', $company);

        $document = $this->documentService->update($document, $request->validated());

        return response()->json(['data' => $document]);
    }

    public function destroy(Company $company, Document $document): JsonResponse
    {
        $this->authorize('update', $company);

        $this->documentService->delete($document);

        return response()->json(['message' => 'Document deleted.']);
    }

    public function attachToEntity(Request $request, Company $company, Document $document): JsonResponse
    {
        $this->authorize('update', $company);

        $request->validate([
            'documentable_type' => ['required', 'string'],
            'documentable_id' => ['required', 'integer'],
        ]);

        $this->documentService->attachToEntity(
            $document,
            $request->input('documentable_type'),
            $request->input('documentable_id'),
        );

        return response()->json(['data' => $document->fresh()]);
    }
}
