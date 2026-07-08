<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Plot;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Attach / detach documents to a plot using the existing polymorphic document
 * module (App\Models\Document, disk "documents"). The plot document type
 * (Bayna Agreement, Sale Deed, Khatian, ...) is stored in the document metadata.
 */
class PlotDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storage = new DocumentStorageService(),
    ) {}

    public function store(Request $request, string $plotUuid)
    {
        $company = app('currentCompany');
        $plot = Plot::forCompany($company->id)->where('uuid', $plotUuid)->firstOrFail();

        $validated = $request->validate([
            'document_type' => ['required', 'in:' . implode(',', array_keys(Plot::DOCUMENT_TYPES))],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $meta = $this->storage->upload(
            $request->file('file'),
            "companies/{$company->id}/plots/{$plot->id}",
        );

        Document::create([
            'company_id' => $company->id,
            'documentable_type' => Plot::class,
            'documentable_id' => $plot->id,
            'title' => $validated['title'] ?: Plot::DOCUMENT_TYPES[$validated['document_type']],
            'file_name' => $meta['file_name'],
            'file_path' => $meta['path'],
            'file_size' => $meta['size'],
            'mime_type' => $meta['mime_type'],
            'disk' => $meta['disk'],
            'uploaded_by' => Auth::id(),
            'metadata' => ['plot_document_type' => $validated['document_type']],
        ]);

        return redirect("/plots/{$plot->uuid}")->with('success', 'Document uploaded successfully.');
    }

    public function destroy(string $plotUuid, string $documentUuid)
    {
        $company = app('currentCompany');
        $plot = Plot::forCompany($company->id)->where('uuid', $plotUuid)->firstOrFail();

        $document = Document::forCompany($company->id)
            ->where('documentable_type', Plot::class)
            ->where('documentable_id', $plot->id)
            ->where('uuid', $documentUuid)
            ->firstOrFail();

        if ($this->storage->exists($document)) {
            $this->storage->delete($document);
        }

        $document->delete();

        return redirect("/plots/{$plot->uuid}")->with('success', 'Document deleted successfully.');
    }
}
