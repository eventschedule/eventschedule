<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Image::class);

        $query = Image::query()
            ->where(function ($builder) use ($request) {
                $builder->whereNull('user_id')
                    ->orWhere('user_id', $request->user()->id);
            })
            ->orderByDesc('created_at');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('original_name', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            });
        }

        $images = $query->limit(50)->get()->map(function (Image $image) {
            return [
                'id' => $image->id,
                'url' => $image->url(),
                'name' => $image->original_name ?? basename($image->path),
                'created_at' => optional($image->created_at)->toDateTimeString(),
            ];
        });

        return response()->json(['data' => $images]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Image::class);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $file = $validated['image'];
        $disk = storage_public_disk();
        $filename = Str::lower(Str::uuid()->toString() . '.' . $file->getClientOriginalExtension());

        $path = storage_put_file_as_public($disk, $file, $filename);

        $dimensions = @getimagesize($file->getRealPath());

        $image = Image::create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => [
                'id' => $image->id,
                'url' => $image->url(),
                'name' => $image->original_name ?? basename($image->path),
            ],
        ], 201);
    }

    public function destroy(Image $image): JsonResponse
    {
        $this->authorize('delete', $image);

        $disk = $image->disk;
        $path = $image->path;

        if ($image->delete()) {
            try {
                Storage::disk($disk)->delete($path);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
