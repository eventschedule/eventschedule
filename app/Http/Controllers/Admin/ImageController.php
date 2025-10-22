<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(private readonly ImageLibraryService $images)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:20'],
        ]);

        $search = $validated['search'] ?? null;
        $type = $validated['type'] ?? null;

        $result = $this->images->list($search, $type);

        return response()->json([
            'data' => $result['items'],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'available_types' => $result['available_types'],
            ],
            'meta' => [
                'total' => count($result['items']),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $data = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $image = $this->images->store($data['image']);

        return response()->json(['data' => $image], 201);
    }

    public function update(Request $request, string $image): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $data = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $updated = $this->images->replace($image, $data['image']);

        return response()->json(['data' => $updated]);
    }

    public function destroy(Request $request, string $image): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $this->images->delete($image);

        return response()->json()->setStatusCode(204);
    }

    protected function authorizeAdmin($user): void
    {
        abort_unless($user && method_exists($user, 'isAdmin') && $user->isAdmin(), 403);
    }
}
