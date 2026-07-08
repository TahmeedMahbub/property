<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;

class DocumentDownloadController extends Controller
{
    public function __construct(protected DocumentStorageService $storage)
    {
    }

    /**
     * Download a document (requires auth + company ownership).
     */
    public function download(Request $request, string $uuid)
    {
        $document = Document::where('uuid', $uuid)
            ->forCompany(app('currentCompany')->id)
            ->firstOrFail();

        if (!$this->storage->exists($document)) {
            abort(404, 'File not found.');
        }

        return $this->storage->download($document);
    }

    /**
     * Signed URL download (for temporary/shared links — validates signature).
     */
    public function signedDownload(Request $request, string $uuid)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $document = Document::where('uuid', $uuid)->firstOrFail();

        if (!$this->storage->exists($document)) {
            abort(404, 'File not found.');
        }

        return $this->storage->download($document);
    }

    /**
     * Stream/preview a document inline.
     */
    public function preview(Request $request, string $uuid)
    {
        $document = Document::where('uuid', $uuid)
            ->forCompany(app('currentCompany')->id)
            ->firstOrFail();

        if (!$this->storage->exists($document)) {
            abort(404, 'File not found.');
        }

        return $this->storage->stream($document);
    }
}
