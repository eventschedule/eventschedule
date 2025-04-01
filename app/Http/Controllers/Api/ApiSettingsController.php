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
        
        // Generate new API key if enabled
        if ($request->boolean('enable_api')) {
            $user->api_key = Str::random(32);
        } else {
            $user->api_key = null;
        }
        
        $user->save();
        
        // Always show the new key when it's generated
        if ($user->api_key) {
            return back()
                ->with('success', 'API settings updated successfully')
                ->with('show_new_api_key', true);
        }
        
        return back()->with('success', 'API settings updated successfully');
    }
} 