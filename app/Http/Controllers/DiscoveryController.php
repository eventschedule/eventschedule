<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DiscoveryController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'apiBaseURL' => url('/api'),
            'brandingEndpoint' => url('/branding.json'),
            'features' => ['branding'],
        ]);
    }

    public function branding(): JsonResponse
    {
        $branding = config('branding', []);

        return response()->json([
            'logoUrl' => data_get($branding, 'logo_url', branding_logo_url()),
            'logoAlt' => data_get($branding, 'logo_alt', 'Event Schedule'),
            'colors' => [
                'primary' => data_get($branding, 'colors.primary', '#1F2937'),
                'secondary' => data_get($branding, 'colors.secondary', '#111827'),
                'tertiary' => data_get($branding, 'colors.tertiary', '#374151'),
                'onPrimary' => data_get($branding, 'colors.on_primary', '#FFFFFF'),
                'onSecondary' => data_get($branding, 'colors.on_secondary', '#FFFFFF'),
                'onTertiary' => data_get($branding, 'colors.on_tertiary', '#FFFFFF'),
            ],
            'defaultLanguage' => data_get($branding, 'default_language', config('app.locale', 'en')),
        ]);
    }
}
