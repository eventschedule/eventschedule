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
