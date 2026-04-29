<?php

namespace App\Http\Controllers\Traits;

use App\Models\Newsletter;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

trait SanitizesNewsletterContent
{
    protected function sanitizeStyleSettings(?array $settings): array
    {
        $defaults = Newsletter::defaultStyleSettings();
        if (! $settings) {
            return $defaults;
        }

        $allowedFonts = ['Arial', 'Georgia', 'Verdana', 'Trebuchet MS', 'Times New Roman', 'Courier New', 'Helvetica', 'Tahoma'];
        $allowedRadii = ['rounded', 'square'];
        $allowedLayouts = ['cards', 'list'];

        $sanitized = [];
        $sanitized['backgroundColor'] = $this->sanitizeHexColor($settings['backgroundColor'] ?? null, $defaults['backgroundColor']);
        $sanitized['accentColor'] = $this->sanitizeHexColor($settings['accentColor'] ?? null, $defaults['accentColor']);
        $sanitized['textColor'] = $this->sanitizeHexColor($settings['textColor'] ?? null, $defaults['textColor']);
        $sanitized['fontFamily'] = in_array($settings['fontFamily'] ?? '', $allowedFonts) ? $settings['fontFamily'] : $defaults['fontFamily'];
        $sanitized['buttonRadius'] = in_array($settings['buttonRadius'] ?? '', $allowedRadii) ? $settings['buttonRadius'] : $defaults['buttonRadius'];
        $sanitized['eventLayout'] = in_array($settings['eventLayout'] ?? '', $allowedLayouts) ? $settings['eventLayout'] : $defaults['eventLayout'];
        $sanitized['footerText'] = mb_substr(strip_tags(trim($settings['footerText'] ?? '')), 0, 500);

        return $sanitized;
    }

    protected function sanitizeHexColor(?string $value, string $default): string
    {
        if ($value && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return $value;
        }

        return $default;
    }

    protected function parseBlocks(Request $request, array $extraAllowedTypes = []): ?array
    {
        $blocksJson = $request->input('blocks');
        if (! $blocksJson) {
            return null;
        }

        if (is_string($blocksJson)) {
            $blocks = json_decode($blocksJson, true);
        } else {
            $blocks = $blocksJson;
        }

        if (! is_array($blocks)) {
            return null;
        }

        // Sanitize blocks
        $allowed = array_merge(
            ['profile_image', 'header_banner', 'heading', 'text', 'events', 'button', 'divider', 'spacer', 'image', 'social_links', 'video', 'quote', 'sponsors', 'poll'],
            $extraAllowedTypes
        );
        $dangerousSchemes = ['javascript:', 'data:', 'vbscript:'];

        $blocks = array_values(array_filter($blocks, function ($block) use ($allowed) {
            return isset($block['type']) && in_array($block['type'], $allowed) && isset($block['id']);
        }));

        // Sanitize URLs in blocks to prevent javascript: and other dangerous URI schemes
        foreach ($blocks as &$block) {
            if (isset($block['data']['url'])) {
                $urlLower = strtolower(trim($block['data']['url']));
                foreach ($dangerousSchemes as $scheme) {
                    if (str_starts_with($urlLower, $scheme)) {
                        $block['data']['url'] = '#';
                        break;
                    }
                }
            }
            if (isset($block['data']['buttonUrl'])) {
                $urlLower = strtolower(trim($block['data']['buttonUrl']));
                foreach ($dangerousSchemes as $scheme) {
                    if (str_starts_with($urlLower, $scheme)) {
                        $block['data']['buttonUrl'] = '#';
                        break;
                    }
                }
            }
            if (isset($block['data']['link'])) {
                $urlLower = strtolower(trim($block['data']['link']));
                foreach ($dangerousSchemes as $scheme) {
                    if (str_starts_with($urlLower, $scheme)) {
                        $block['data']['link'] = '#';
                        break;
                    }
                }
            }
            if (isset($block['data']['links']) && is_array($block['data']['links'])) {
                foreach ($block['data']['links'] as &$link) {
                    if (isset($link['url'])) {
                        $urlLower = strtolower(trim($link['url']));
                        foreach ($dangerousSchemes as $scheme) {
                            if (str_starts_with($urlLower, $scheme)) {
                                $link['url'] = '#';
                                break;
                            }
                        }
                    }
                }
                unset($link);
            }

            // Validate heading level
            if (($block['type'] ?? '') === 'heading') {
                $block['data']['level'] = in_array($block['data']['level'] ?? '', ['h1', 'h2', 'h3'])
                    ? $block['data']['level'] : 'h1';
            }

            // Validate social link platforms
            if (($block['type'] ?? '') === 'social_links' && isset($block['data']['links'])) {
                $allowedPlatforms = array_merge(['website'], UrlUtils::getUniquePlatforms());
                $block['data']['links'] = array_values(array_filter($block['data']['links'], fn ($l) => ! empty($l['platform']) && in_array($l['platform'], $allowedPlatforms, true)
                ));
            }

            // Validate align fields
            if (isset($block['data']['align'])) {
                $allowedAligns = ['left', 'center', 'right'];
                $block['data']['align'] = in_array($block['data']['align'], $allowedAligns) ? $block['data']['align'] : 'center';
            }

            // Validate image block
            if (($block['type'] ?? '') === 'image') {
                $w = $block['data']['width'] ?? '100%';
                if (! preg_match('/^\d+(px|%)?$/', $w)) {
                    $block['data']['width'] = '100%';
                }
                if (isset($block['data']['layout'])) {
                    $allowedLayouts = ['column', 'row', 'grid'];
                    $block['data']['layout'] = in_array($block['data']['layout'], $allowedLayouts)
                        ? $block['data']['layout'] : 'column';
                }
                if (isset($block['data']['images']) && is_array($block['data']['images'])) {
                    $block['data']['images'] = array_slice($block['data']['images'], 0, 4);
                    foreach ($block['data']['images'] as &$img) {
                        unset($img['_id']);
                        foreach (['url', 'link'] as $urlField) {
                            if (isset($img[$urlField])) {
                                $urlLower = strtolower(trim($img[$urlField]));
                                foreach ($dangerousSchemes as $scheme) {
                                    if (str_starts_with($urlLower, $scheme)) {
                                        $img[$urlField] = '#';
                                        break;
                                    }
                                }
                            }
                        }
                        foreach (['caption', 'alt'] as $textField) {
                            if (isset($img[$textField])) {
                                $img[$textField] = mb_substr(strip_tags(trim($img[$textField])), 0, 200);
                            }
                        }
                    }
                    unset($img);
                }
            }

            // Validate sponsors source
            if (($block['type'] ?? '') === 'sponsors') {
                $block['data']['source'] = in_array($block['data']['source'] ?? '', ['schedule', 'first_event'])
                    ? $block['data']['source'] : 'schedule';
            }

            // Validate YouTube URL for video blocks
            if (($block['type'] ?? '') === 'video') {
                $videoUrl = trim($block['data']['url'] ?? '');
                $videoId = null;
                if (preg_match('/(?:youtube\.com\/watch\?.*v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
                    $videoId = $m[1];
                }
                $block['data']['url'] = $videoId ? 'https://www.youtube.com/watch?v='.$videoId : '';
            }
        }
        unset($block);

        return $blocks;
    }

    protected function handleNewsletterImageUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        if (! $request->hasFile('image')) {
            return response()->json(['error' => __('messages.no_file_uploaded')], 400);
        }

        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (! in_array($extension, $allowedExtensions)) {
            return response()->json(['error' => __('messages.invalid_file_type')], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimeTypes)) {
            return response()->json(['error' => __('messages.invalid_file_type')], 400);
        }

        if (@getimagesize($file->getPathname()) === false) {
            return response()->json(['error' => __('messages.invalid_file_type')], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json(['error' => __('messages.file_too_large')], 400);
        }

        $filename = strtolower('newsletter_image_'.\Illuminate\Support\Str::random(32).'.'.$extension);
        $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

        if (config('filesystems.default') == 'local') {
            $url = url('/storage/'.$filename);
        } else {
            $url = \Illuminate\Support\Facades\Storage::url($filename);
        }

        return response()->json([
            'success' => true,
            'url' => $url,
        ]);
    }
}
