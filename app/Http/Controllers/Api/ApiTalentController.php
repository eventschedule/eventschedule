<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiTalentController extends Controller
{
    /**
     * Display a listing of talent.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Role::where('type', 'talent')
            ->where('user_id', $user->id)
            ->orderBy('name');

        $talent = $query->get();

        return response()->json([
            'data' => $talent->map(function ($t) {
                return $this->formatTalent($t);
            })->values()->all(),
        ]);
    }

    /**
     * Display the specified talent.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $talent = Role::where('type', 'talent')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json($this->formatTalent($talent));
    }

    /**
     * Store a newly created talent.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('roles')->where(function ($query) use ($user) {
                return $query->where('type', 'talent')->where('user_id', $user->id);
            })],
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'timezone' => 'nullable|string|max:100',
        ]);

        // Generate unique subdomain
        $baseSubdomain = Str::slug($validated['name']);
        $subdomain = $baseSubdomain;
        $counter = 1;

        while (Role::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain . '-' . $counter;
            $counter++;
        }

        $talent = Role::create([
            'type' => 'talent',
            'user_id' => $user->id,
            'subdomain' => $subdomain,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
            'address1' => $validated['address1'] ?? null,
            'address2' => $validated['address2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country_code' => $validated['country_code'] ?? null,
            'timezone' => $validated['timezone'] ?? $user->timezone ?? 'UTC',
            'language_code' => $user->language_code ?? 'en',
            'profile_image_url' => $validated['profile_image_url'] ?? null,
            'header_image_url' => $validated['header_image_url'] ?? null,
            'background_image_url' => $validated['background_image_url'] ?? null,
        ]);

        return response()->json($this->formatTalent($talent), 201);
    }

    /**
     * Update the specified talent.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $talent = Role::where('type', 'talent')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('roles')->where(function ($query) use ($user) {
                return $query->where('type', 'talent')->where('user_id', $user->id);
            })->ignore($talent->id)],
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'timezone' => 'nullable|string|max:100',
            'profile_image_url' => 'nullable|url|max:500',
            'header_image_url' => 'nullable|url|max:500',
            'background_image_url' => 'nullable|url|max:500',
        ]);

        $talent->update($validated);

        return response()->json($this->formatTalent($talent));
    }

    /**
     * Remove the specified talent.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $talent = Role::where('type', 'talent')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $talent->delete();

        return response()->json(['message' => 'Talent deleted successfully'], 200);
    }

    /**
     * Format talent data for API response.
     */
    private function formatTalent(Role $talent): array
    {
        return [
            'id' => $talent->id,
            'name' => $talent->name,
            'email' => $talent->email,
            'phone' => $talent->phone,
            'website' => $talent->website,
            'description' => $talent->description,
            'address1' => $talent->address1,
            'address2' => $talent->address2,
            'city' => $talent->city,
            'state' => $talent->state,
            'postal_code' => $talent->postal_code,
            'country_code' => $talent->country_code,
            'timezone' => $talent->timezone,
            'subdomain' => $talent->subdomain,
            'profile_image_url' => $talent->profile_image_url,
            'header_image_url' => $talent->header_image_url,
            'background_image_url' => $talent->background_image_url,
            'created_at' => $talent->created_at?->toIso8601String(),
            'updated_at' => $talent->updated_at?->toIso8601String(),
        ];
    }
}
