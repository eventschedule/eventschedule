<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Utils\ColorUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiRoleController extends Controller
{
    protected const MAX_PER_PAGE = 1000;
    protected const DEFAULT_PER_PAGE = 100;

    public function index(Request $request)
    {
        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $query = Role::where('user_id', auth()->id())
            ->whereIn('type', ['venue', 'curator', 'talent']);

        if ($request->filled('type')) {
            $types = $request->input('type');
            $types = is_array($types) ? $types : explode(',', $types);
            $types = array_filter(array_map('trim', $types));

            if (! empty($types)) {
                $query->whereIn('type', $types);
            }
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $roles = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => collect($roles->items())->map(function ($role) {
                return $role->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'from' => $roles->firstItem(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'to' => $roles->lastItem(),
                'total' => $roles->total(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:venue,curator,talent'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'language_code' => ['nullable', 'string', 'max:10'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'string', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:255'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['nullable', 'string', 'max:255'],
            'address1' => ['required_if:type,venue', 'nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
        ]);

        $role = new Role();
        $role->fill($validated);
        $role->type = $validated['type'];
        $role->subdomain = Role::generateSubdomain($validated['name']);
        $role->user_id = $request->user()->id;

        if (config('app.hosted')) {
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->plan_type = 'pro';
            $role->plan_term = 'year';
        } else {
            $role->email_verified_at = now();
        }

        if (! $role->background_colors) {
            $role->background_colors = ColorUtils::randomGradient();
            $role->background_rotation = rand(0, 359);
            $role->font_color = '#ffffff';
        }

        if ($request->filled('contacts')) {
            $role->contacts = $request->input('contacts');
        }

        $role->save();

        if ($request->filled('groups') && is_array($request->groups)) {
            foreach ($request->groups as $groupName) {
                $name = trim((string) $groupName);

                if ($name === '') {
                    continue;
                }

                $role->groups()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]);
            }
        }

        $request->user()->roles()->syncWithoutDetaching([
            $role->id => ['level' => 'owner', 'created_at' => now()],
        ]);
        $request->user()->addResourceToScope($role);

        return response()->json([
            'data' => $role->fresh(['groups'])->toApiData(),
            'meta' => [
                'message' => 'Role created successfully',
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }
}
