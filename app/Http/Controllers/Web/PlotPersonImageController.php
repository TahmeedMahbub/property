<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlotOwner;
use App\Models\PlotSeller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Streams a plot seller/owner NID or photo image inline, scoped to the current
 * company. Files are stored on the private "documents" disk via
 * DocumentStorageService, so they must be served through this authorised route.
 */
class PlotPersonImageController extends Controller
{
    private const FIELDS = ['nid_front', 'nid_back', 'photo'];

    public function show(string $type, string $uuid, string $field): Response
    {
        abort_unless(in_array($field, self::FIELDS, true), 404);

        $company = app('currentCompany');

        $model = match ($type) {
            'seller' => PlotSeller::class,
            'owner' => PlotOwner::class,
            default => abort(404),
        };

        $person = $model::where('uuid', $uuid)
            ->whereHas('plot', fn ($q) => $q->where('company_id', $company->id))
            ->firstOrFail();

        $path = $person->{$field};

        abort_if(empty($path) || ! Storage::disk('documents')->exists($path), 404);

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response(Storage::disk('documents')->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
        ]);
    }
}
