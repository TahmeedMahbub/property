<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorageService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = 'documents';
    }

    /**
     * Upload a file and return metadata for database storage.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory  Relative path within the disk (e.g. "companies/5/projects/12")
     * @param  string|null  $filename  Custom filename (auto-generated if null)
     * @return array{disk: string, path: string, file_name: string, mime_type: string, size: int}
     */
    public function upload(UploadedFile $file, string $directory, ?string $filename = null): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = $filename ?? Str::ulid() . '.' . $extension;

        $path = $this->storage()->putFileAs($directory, $file, $filename);

        return [
            'disk' => $this->disk,
            'path' => $path,
            'file_name' => $originalName,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Download a document as a streamed response.
     */
    public function download(Document $document): StreamedResponse
    {
        $disk = $document->disk ?? $this->disk;

        return Storage::disk($disk)->download(
            $document->file_path,
            $document->file_name
        );
    }

    /**
     * Stream a document inline (for preview in browser).
     */
    public function stream(Document $document): StreamedResponse
    {
        $disk = $document->disk ?? $this->disk;

        return response()->streamDownload(function () use ($disk, $document) {
            echo Storage::disk($disk)->get($document->file_path);
        }, $document->file_name, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ]);
    }

    /**
     * Delete a file from storage.
     */
    public function delete(Document $document): bool
    {
        $disk = $document->disk ?? $this->disk;

        return Storage::disk($disk)->delete($document->file_path);
    }

    /**
     * Check if a document's file exists on disk.
     */
    public function exists(Document $document): bool
    {
        $disk = $document->disk ?? $this->disk;

        return Storage::disk($disk)->exists($document->file_path);
    }

    /**
     * Generate a temporary URL (works with S3/R2 drivers).
     * Falls back to a signed route for local disk.
     */
    public function temporaryUrl(Document $document, int $minutes = 30): string
    {
        $disk = $document->disk ?? $this->disk;
        $diskConfig = config("filesystems.disks.{$disk}");

        // S3-compatible drivers support native temporary URLs
        if (($diskConfig['driver'] ?? 'local') === 's3') {
            return Storage::disk($disk)->temporaryUrl(
                $document->file_path,
                now()->addMinutes($minutes)
            );
        }

        // For local disk, use a signed route
        return url()->signedRoute('documents.download', [
            'document' => $document->uuid,
        ], now()->addMinutes($minutes));
    }

    /**
     * Get the underlying Storage disk instance.
     */
    protected function storage()
    {
        return Storage::disk($this->disk);
    }
}
