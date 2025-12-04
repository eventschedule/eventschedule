<?php

namespace App\Support;

use App\Models\MediaAsset;
use App\Models\MediaAssetVariant;
use App\Utils\MarkdownUtils;
use Illuminate\Support\Str;

class HomePageSettings
{
    public const LAYOUT_FULL = 'calendar_full';
    public const LAYOUT_LEFT = 'calendar_left';
    public const LAYOUT_RIGHT = 'calendar_right';

    public const HERO_ALIGN_LEFT = 'left';
    public const HERO_ALIGN_CENTER = 'center';
    public const HERO_ALIGN_RIGHT = 'right';

    /**
     * @return array<int, string>
     */
    public static function allowedLayouts(): array
    {
        return [
            self::LAYOUT_FULL,
            self::LAYOUT_LEFT,
            self::LAYOUT_RIGHT,
        ];
    }

    public static function normalizeLayout(?string $layout): string
    {
        return in_array($layout, self::allowedLayouts(), true)
            ? $layout
            : self::LAYOUT_FULL;
    }

    /**
     * @return array<int, string>
     */
    public static function allowedHeroAlignments(): array
    {
        return [
            self::HERO_ALIGN_CENTER,
            self::HERO_ALIGN_LEFT,
            self::HERO_ALIGN_RIGHT,
        ];
    }

    public static function normalizeHeroAlignment(?string $alignment): string
    {
        return in_array($alignment, self::allowedHeroAlignments(), true)
            ? $alignment
            : self::HERO_ALIGN_CENTER;
    }

    public static function normalizeBoolean(null|bool|string|int $value, bool $default = false): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return $default;
    }

    public static function clean(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public static function compileHtml(?string $storedHtml, ?string $markdown): ?string
    {
        if (is_string($storedHtml) && trim($storedHtml) !== '') {
            return $storedHtml;
        }

        $markdown = self::clean($markdown);

        return $markdown ? MarkdownUtils::convertToHtml($markdown) : null;
    }

    public static function isSafeCtaUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        if (Str::startsWith($url, ['/', '#', 'mailto:', 'tel:'])) {
            return true;
        }

        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @return array{asset_id: int|null, variant_id: int|null, url: string|null}
     */
    public static function resolveImagePreview(?int $assetId, ?int $variantId): array
    {
        $result = [
            'asset_id' => null,
            'variant_id' => null,
            'url' => null,
        ];

        if ($variantId) {
            $variant = MediaAssetVariant::find($variantId);

            if ($variant && (! $assetId || $variant->media_asset_id === $assetId)) {
                $result['asset_id'] = $variant->media_asset_id;
                $result['variant_id'] = $variant->id;
                $result['url'] = $variant->url;

                return $result;
            }
        }

        if ($assetId) {
            $asset = MediaAsset::find($assetId);

            if ($asset) {
                $result['asset_id'] = $asset->id;
                $result['url'] = $asset->url;
            }
        }

        return $result;
    }
}
