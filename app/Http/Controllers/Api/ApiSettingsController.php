<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiSettingsController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();
        $enableApi = $request->boolean('enable_api');
        
        // Only generate new key if:
        // 1. API was disabled and is now being enabled
        // 2. API was enabled and is now being disabled (set to null)
        if ($enableApi && !$user->api_key) {
            // Generate new key when enabling
            $user->api_key = Str::random(32);
            $showNewKey = true;
        } elseif (!$enableApi && $user->api_key) {
            // Remove key when disabling
            $user->api_key = null;
            $showNewKey = false;
        } else {
            // No change to key if just saving with same state
            $showNewKey = false;
        }
        
        $user->save();
        
        return back()
            ->with('success', 'API settings updated successfully')
            ->with('show_new_api_key', $showNewKey);
    }
} 