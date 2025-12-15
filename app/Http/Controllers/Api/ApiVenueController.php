<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApiVenueController extends Controller
{
    /**
     * Display a listing of venues.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Role::where('type', 'venue')
            ->where('user_id', $user->id)
            ->orderBy('name');

        $venues = $query->get();

        return response()->json([
            'data' => $venues->map(function ($v) {
                return $this->formatVenue($v);
            })->values()->all(),
        ]);
    }

    /**
     * Display the specified venue.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $venue = Role::where('type', 'venue')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json($this->formatVenue($venue));
    }

    /**
     * Store a newly created venue.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('roles')->where(function ($query) use ($user) {
                return $query->where('type', 'venue')->where('user_id', $user->id);
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
            'formatted_address' => 'nullable|string|max:500',
            'geo_lat' => 'nullable|numeric|between:-90,90',
            'geo_lon' => 'nullable|numeric|between:-180,180',
        ]);

        // Generate unique subdomain
        $baseSubdomain = Str::slug($validated['name']);
        $subdomain = $baseSubdomain;
        $counter = 1;

        while (Role::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain . '-' . $counter;
            $counter++;
        }

        $venue = Role::create([
            'type' => 'venue',
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
            'formatted_address' => $validated['formatted_address'] ?? null,
            'geo_lat' => $validated['geo_lat'] ?? null,
            'geo_lon' => $validated['geo_lon'] ?? null,
        ]);

        return response()->json($this->formatVenue($venue), 201);
    }

    /**
     * Update the specified venue.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $venue = Role::where('type', 'venue')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('roles')->where(function ($query) use ($user) {
                return $query->where('type', 'venue')->where('user_id', $user->id);
            })->ignore($venue->id)],
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
            'formatted_address' => 'nullable|string|max:500',
            'geo_lat' => 'nullable|numeric|between:-90,90',
            'geo_lon' => 'nullable|numeric|between:-180,180',
        ]);

        $venue->update($validated);

        return response()->json($this->formatVenue($venue));
    }

    /**
     * Remove the specified venue.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $venue = Role::where('type', 'venue')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $venue->delete();

        return response()->json(['message' => 'Venue deleted successfully'], 200);
    }

    /**
     * Format venue data for API response.
     */
    private function formatVenue(Role $venue): array
    {
        return [
            'id' => $venue->id,
            'name' => $venue->name,
            'email' => $venue->email,
            'phone' => $venue->phone,
            'website' => $venue->website,
            'description' => $venue->description,
            'address1' => $venue->address1,
            'address2' => $venue->address2,
            'city' => $venue->city,
            'state' => $venue->state,
            'postal_code' => $venue->postal_code,
            'country_code' => $venue->country_code,
            'formatted_address' => $venue->formatted_address,
            'geo_lat' => $venue->geo_lat ? (float) $venue->geo_lat : null,
            'geo_lon' => $venue->geo_lon ? (float) $venue->geo_lon : null,
            'timezone' => $venue->timezone,
            'subdomain' => $venue->subdomain,
            'created_at' => $venue->created_at?->toIso8601String(),
            'updated_at' => $venue->updated_at?->toIso8601String(),
        ];
    }
}
