<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\PlotBooking;
use App\Services\DocumentStorageService;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Attach / detach certificates and agreements to a booking using the shared
 * polymorphic document module (App\Models\Document, disk "documents").
 */
class PlotBookingDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storage = new DocumentStorageService(),
    ) {}

    public function store(Request $request, string $bookingUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();

        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('p_document_categories', 'id')->where(
                    fn ($q) => $q->where(fn ($w) => $w->where('company_id', $company->id)->orWhereNull('company_id')),
                ),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'],
        ]);

        $category = DocumentCategory::forCompany($company->id)->findOrFail($validated['category_id']);

        if ($category->slug === DocumentCategorySeeder::OTHER_SLUG) {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:2000'],
            ]);
        }

        $meta = $this->storage->upload(
            $request->file('file'),
            "companies/{$company->id}/bookings/{$booking->id}",
        );

        Document::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'documentable_type' => PlotBooking::class,
            'documentable_id' => $booking->id,
            'title' => $validated['title'] ?: $category->name,
            'description' => $validated['description'] ?? null,
            'file_name' => $meta['file_name'],
            'file_path' => $meta['path'],
            'file_size' => $meta['size'],
            'mime_type' => $meta['mime_type'],
            'disk' => $meta['disk'],
            'uploaded_by' => Auth::id(),
        ]);

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Document uploaded successfully.');
    }

    public function destroy(string $bookingUuid, string $documentUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();

        $document = Document::forCompany($company->id)
            ->where('documentable_type', PlotBooking::class)
            ->where('documentable_id', $booking->id)
            ->where('uuid', $documentUuid)
            ->firstOrFail();

        if ($this->storage->exists($document)) {
            $this->storage->delete($document);
        }

        $document->delete();

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Document deleted successfully.');
    }
}
