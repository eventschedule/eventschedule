<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Models\MediaAssetVariant;
use App\Models\MediaAssetUsage;
use App\Models\MediaTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    public function index(): View
    {
        return view('media.index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = MediaAsset::with([
            'tags',
            'variants' => fn ($q) => $q->latest(),
            'usages.usable',
        ])
            ->withCount('usages')
            ->latest();

        if ($request->filled('tag')) {
            $tag = MediaTag::where('slug', $request->string('tag'))->first();
            if ($tag) {
                $query->whereHas('tags', fn ($builder) => $builder->whereKey($tag));
            }
        }

        $search = $request->string('search')->trim()->value();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('folder', 'like', "%{$search}%");
            });
        }

        $assets = $query->paginate(24);

        return response()->json([
            'data' => $assets->getCollection()->transform(function (MediaAsset $asset) {
                return [
                    'id' => $asset->id,
                    'uuid' => $asset->uuid,
                    'url' => $asset->url,
                    'original_filename' => $asset->original_filename,
                    'width' => $asset->width,
                    'height' => $asset->height,
                    'folder' => $asset->folder,
                    'usage_count' => $asset->usages_count ?? 0,
                    'usages' => $asset->usages
                        ->map(fn (MediaAssetUsage $usage) => $this->transformUsage($usage))
                        ->all(),
                    'tags' => $asset->tags->map(fn (MediaTag $tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ])->all(),
                    'variants' => $asset->variants->map(fn (MediaAssetVariant $variant) => [
                        'id' => $variant->id,
                        'label' => $variant->label,
                        'url' => $variant->url,
                        'width' => $variant->width,
                        'height' => $variant->height,
                    ])->all(),
                ];
            }),
            'pagination' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:5120'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:media_tags,id'],
            'folder' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $disk = storage_public_disk();
        $dimensions = @getimagesize($file->getPathname());
        $filename = strtolower('media_' . Str::random(40) . '.' . $file->getClientOriginalExtension());
        $path = storage_put_file_as_public($disk, $file, $filename, 'media');

        $asset = MediaAsset::create([
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'uploaded_by' => Auth::id(),
            'folder' => $validated['folder'] ?? null,
        ]);

        if (! empty($validated['tags'])) {
            $asset->tags()->sync($validated['tags']);
        }

        return response()->json([
            'success' => true,
            'asset' => [
                'id' => $asset->id,
                'uuid' => $asset->uuid,
                'url' => $asset->url,
            ],
        ], 201);
    }

    public function destroy(Request $request, MediaAsset $asset): JsonResponse
    {
        $asset->loadMissing('usages.usable');

        if ($asset->usages->isNotEmpty() && ! $request->boolean('force')) {
            return response()->json([
                'message' => 'This asset is currently in use and cannot be deleted.',
                'usages' => $asset->usages
                    ->map(fn (MediaAssetUsage $usage) => $this->transformUsage($usage))
                    ->all(),
            ], 422);
        }

        if ($asset->usages->isNotEmpty()) {
            MediaAssetUsage::clearForAsset($asset);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function storeVariant(Request $request, MediaAsset $asset): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:5120'],
            'label' => ['nullable', 'string', 'max:120'],
            'context' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $disk = storage_public_disk();
        $dimensions = @getimagesize($file->getPathname());
        $filename = strtolower('media_variant_' . Str::random(40) . '.' . $file->getClientOriginalExtension());
        $path = storage_put_file_as_public($disk, $file, $filename, 'media/variants');

        $cropMeta = null;

        if ($request->filled('crop_meta')) {
            $decoded = json_decode($request->input('crop_meta'), true);
            $cropMeta = is_array($decoded) ? $decoded : null;
        }

        $variant = $asset->variants()->create([
            'disk' => $disk,
            'path' => $path,
            'label' => $validated['label'] ?? $validated['context'] ?? null,
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'size' => $file->getSize(),
            'crop_meta' => $cropMeta,
        ]);

        return response()->json([
            'success' => true,
            'variant' => [
                'id' => $variant->id,
                'label' => $variant->label,
                'url' => $variant->url,
                'width' => $variant->width,
                'height' => $variant->height,
            ],
        ], 201);
    }

    public function tags(): JsonResponse
    {
        $tags = MediaTag::orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function storeTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $tag = MediaTag::firstOrCreate(
            ['slug' => Str::slug($validated['name'])],
            ['name' => $validated['name']]
        );

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ],
        ], 201);
    }

    public function destroyTag(MediaTag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function syncTags(Request $request, MediaAsset $asset): JsonResponse
    {
        $validated = $request->validate([
            'tags' => ['array'],
            'tags.*' => ['integer', 'exists:media_tags,id'],
        ]);

        $asset->tags()->sync($validated['tags'] ?? []);

        return response()->json(['success' => true]);
    }

    protected function transformUsage(MediaAssetUsage $usage): array
    {
        $usable = $usage->usable;
        $name = null;

        if ($usable) {
            $name = $usable->name
                ?? $usable->title
                ?? ($usable->display_name ?? null);
        }

        return [
            'id' => $usage->id,
            'context' => $usage->context,
            'context_label' => Str::of($usage->context ?? 'general')->headline()->toString(),
            'type' => $usage->usable_type ? class_basename($usage->usable_type) : null,
            'display_name' => $name,
            'usable_id' => $usage->usable_id,
            'variant_id' => $usage->media_asset_variant_id,
        ];
    }
}
