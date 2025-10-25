<?php

namespace App\Support;

use App\Support\ColorUtils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class BrandingManager
{
    public static function apply(?array $settings = null): void
    {
        $settings = $settings ?? [];

        $defaults = [
            'logo_path' => config('branding.logo_path'),
            'logo_disk' => config('branding.logo_disk'),
            'logo_alt' => config('branding.logo_alt', 'Event Schedule'),
            'primary_color' => data_get(config('branding'), 'colors.primary', '#1F2937'),
            'secondary_color' => data_get(config('branding'), 'colors.secondary', '#111827'),
            'tertiary_color' => data_get(config('branding'), 'colors.tertiary', '#374151'),
            'default_language' => config('branding.default_language', config('app.fallback_locale', 'en')),
        ];

        $logoPath = Arr::get($settings, 'logo_path', $defaults['logo_path']);
        $logoDisk = Arr::get($settings, 'logo_disk', $defaults['logo_disk']);
        $logoAlt = Arr::get($settings, 'logo_alt', $defaults['logo_alt']);
        $logoMediaAssetId = Arr::get($settings, 'logo_media_asset_id');
        $logoMediaVariantId = Arr::get($settings, 'logo_media_variant_id');

        $primary = ColorUtils::normalizeHexColor(
            Arr::get($settings, 'primary_color', $defaults['primary_color'])
        ) ?? '#1F2937';

        $secondary = ColorUtils::normalizeHexColor(
            Arr::get($settings, 'secondary_color', $defaults['secondary_color'])
        ) ?? '#111827';

        $tertiary = ColorUtils::normalizeHexColor(
            Arr::get($settings, 'tertiary_color', $defaults['tertiary_color'])
        ) ?? '#374151';

        $primaryRgb = ColorUtils::hexToRgbString($primary) ?? '31, 41, 55';
        $primaryLight = ColorUtils::mix($primary, '#FFFFFF', 0.55) ?? '#848991';

        $defaultLanguage = Arr::get($settings, 'default_language', $defaults['default_language']);
        if (! is_valid_language_code($defaultLanguage)) {
            $defaultLanguage = config('app.fallback_locale', 'en');
        }

        $logoUrl = self::resolveLogoUrl($logoPath, $logoDisk);

        $resolved = [
            'logo_path' => $logoPath,
            'logo_disk' => $logoDisk,
            'logo_url' => $logoUrl,
            'logo_alt' => is_string($logoAlt) && trim($logoAlt) !== ''
                ? trim($logoAlt)
                : 'Event Schedule',
            'logo_media_asset_id' => $logoMediaAssetId ? (int) $logoMediaAssetId : null,
            'logo_media_variant_id' => $logoMediaVariantId ? (int) $logoMediaVariantId : null,
            'colors' => [
                'primary' => $primary,
                'secondary' => $secondary,
                'tertiary' => $tertiary,
                'primary_rgb' => $primaryRgb,
                'primary_light' => $primaryLight,
            ],
            'default_language' => $defaultLanguage,
        ];

        Config::set('branding', $resolved);

        if (App::getLocale() !== $defaultLanguage) {
            App::setLocale($defaultLanguage);
        }

        Config::set('app.locale', $defaultLanguage);
    }

    protected static function resolveLogoUrl(?string $path, ?string $disk): string
    {
        if (! is_string($path) || trim($path) === '') {
            return url('images/light_logo.png');
        }

        $diskName = is_string($disk) && trim($disk) !== '' ? $disk : storage_public_disk();

        if ($diskName === storage_public_disk()) {
            return storage_asset_url($path);
        }

        try {
            if (Config::has("filesystems.disks.{$diskName}")) {
                return Storage::disk($diskName)->url($path);
            }
        } catch (\Throwable $exception) {
            // Fall back to the default asset URL below.
        }

        return storage_asset_url($path);
    }
}
