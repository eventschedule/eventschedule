<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiGroupController extends Controller
{
    private function findSchedule($subdomain)
    {
        return auth()->user()->roles()
            ->where('subdomain', $subdomain)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();
    }

    public function index(Request $request, $subdomain)
    {
        $role = $this->findSchedule($subdomain);

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $groups = $role->groups->map(function ($group) {
            return [
                'id' => UrlUtils::encodeId($group->id),
                'name' => $group->name,
                'slug' => $group->slug,
                'color' => $group->color,
            ];
        });

        return response()->json([
            'data' => $groups->values(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request, $subdomain)
    {
        $role = $this->findSchedule($subdomain);

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'color' => 'nullable|string|max:50',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $groupData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
        ];

        // Auto-translate name if schedule language is not English
        if ($role->language_code && $role->language_code !== 'en') {
            try {
                $translations = GeminiUtils::translateGroupNames([$request->name], $role->language_code);
                if (isset($translations[$request->name])) {
                    $groupData['name_en'] = $translations[$request->name];
                    $groupData['slug'] = Str::slug($translations[$request->name]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to translate group name via API: '.$e->getMessage());
            }
        }

        $group = $role->groups()->create($groupData);

        return response()->json([
            'data' => [
                'id' => UrlUtils::encodeId($group->id),
                'name' => $group->name,
                'slug' => $group->slug,
                'color' => $group->color,
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $subdomain, $group_id)
    {
        $role = $this->findSchedule($subdomain);

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        $decodedId = UrlUtils::decodeId($group_id);
        $group = $role->groups()->find($decodedId);

        if (! $group) {
            return response()->json(['error' => 'Sub-schedule not found'], 404);
        }

        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:50',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($request->has('name')) {
            $group->name = $request->name;
            $group->slug = Str::slug($request->name);

            // Auto-translate if schedule language is not English
            if ($role->language_code && $role->language_code !== 'en') {
                try {
                    $translations = GeminiUtils::translateGroupNames([$request->name], $role->language_code);
                    if (isset($translations[$request->name])) {
                        $group->name_en = $translations[$request->name];
                        $group->slug = Str::slug($translations[$request->name]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to translate group name via API: '.$e->getMessage());
                }
            }
        }

        if ($request->has('color')) {
            $group->color = $request->color;
        }

        $group->save();

        return response()->json([
            'data' => [
                'id' => UrlUtils::encodeId($group->id),
                'name' => $group->name,
                'slug' => $group->slug,
                'color' => $group->color,
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request, $subdomain, $group_id)
    {
        $role = $this->findSchedule($subdomain);

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        $decodedId = UrlUtils::decodeId($group_id);
        $group = $role->groups()->find($decodedId);

        if (! $group) {
            return response()->json(['error' => 'Sub-schedule not found'], 404);
        }

        $group->delete();

        return response()->json([
            'data' => [
                'message' => 'Sub-schedule deleted successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
