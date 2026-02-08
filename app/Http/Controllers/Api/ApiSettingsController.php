<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiSettingsController extends Controller
{
    public function update(Request $request)
    {
        // Demo mode: prevent API settings changes
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-api')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = auth()->user();
        $enableApi = $request->boolean('enable_api');
        $plaintextKey = null;

        // Only generate new key if:
        // 1. API was disabled and is now being enabled
        // 2. API was enabled and is now being disabled (set to null)
        if ($enableApi && ! $user->api_key) {
            // Generate new key when enabling using cryptographically secure random bytes
            // This ensures the API key has sufficient entropy and is unpredictable
            $plaintextKey = bin2hex(random_bytes(16)); // 32 hex characters = 128 bits of entropy

            // Store prefix for fast DB lookup (first 8 chars of SHA256)
            $user->api_key = substr(hash('sha256', $plaintextKey), 0, 8);
            // Store bcrypt hash for secure verification
            $user->api_key_hash = Hash::make($plaintextKey);
            // Set default expiration of 1 year
            $user->api_key_expires_at = now()->addYear();
            $showNewKey = true;
        } elseif (! $enableApi && $user->api_key) {
            // Remove key when disabling
            $user->api_key = null;
            $user->api_key_hash = null;
            $user->api_key_expires_at = null;
            $showNewKey = false;
        } else {
            // No change to key if just saving with same state
            $showNewKey = false;
        }

        $user->save();

        return redirect()->to(route('profile.edit').'#section-api')
            ->with('message', 'API settings updated successfully')
            ->with('show_new_api_key', $showNewKey)
            ->with('new_api_key', $plaintextKey);
    }
}
