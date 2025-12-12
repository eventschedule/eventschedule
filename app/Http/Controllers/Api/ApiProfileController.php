<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ApiProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => array_merge(['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)], config('app.hosted') ? [] : []),
            'timezone' => ['sometimes', 'required', 'string', 'max:255'],
            'language_code' => ['sometimes', 'required', 'string', 'max:10'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json(['data' => $user->only(['id','name','email','timezone','language_code'])], 200, [], JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request)
    {
        $request->validate([ 'password' => ['required', 'current_password']]);

        $user = $request->user();

        // remove user images
        $disk = storage_public_disk();

        if ($user->profile_image_url) {
            $path = storage_normalize_path($user->getAttributes()['profile_image_url']);
            if ($path !== '') {
                Storage::disk($disk)->delete($path);
            }
        }

        $roles = $user->owner()->get();

        foreach ($roles as $role) {
            if ($role->profile_image_url) {
                $path = storage_normalize_path($role->getAttributes()['profile_image_url']);
                if ($path !== '') {
                    Storage::disk($disk)->delete($path);
                }
            }

            if ($role->header_image_url) {
                $path = storage_normalize_path($role->getAttributes()['header_image_url']);
                if ($path !== '') {
                    Storage::disk($disk)->delete($path);
                }
            }

            if ($role->background_image_url) {
                $path = storage_normalize_path($role->getAttributes()['background_image_url']);
                if ($path !== '') {
                    Storage::disk($disk)->delete($path);
                }
            }
        }

        $user->delete();

        return response()->json(['meta' => ['message' => 'Account deleted']], 200, [], JSON_PRETTY_PRINT);
    }
}
