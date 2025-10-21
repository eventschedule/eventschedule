<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicStorageController extends Controller
{
    /**
     * Stream a publicly accessible file stored on the configured filesystem disk.
     */
    public function __invoke(string $path): StreamedResponse
    {
        $normalized = storage_normalize_path($path);

        if ($normalized === '' || str_contains($normalized, '..')) {
            abort(404);
        }

        $diskName = storage_public_disk();
        $disk = Storage::disk($diskName);

        try {
            if (! $disk->exists($normalized)) {
                abort(404);
            }

            $stream = $disk->readStream($normalized);
        } catch (\Throwable $exception) {
            abort(404);
        }

        if (! is_resource($stream)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($normalized) ?: 'application/octet-stream';
        $size = $disk->size($normalized);

        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
            'Content-Disposition' => 'inline; filename="' . basename($normalized) . '"',
        ];

        if (is_numeric($size)) {
            $headers['Content-Length'] = (string) $size;
        }

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, $headers);
    }
}

