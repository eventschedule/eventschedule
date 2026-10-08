<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'role_id',
        'user_id',
        'subject',
        'blocks',
        'style_settings',
        'template',
        'event_ids',
        'segment_ids',
        'status',
        'scheduled_at',
        'sent_at',
        'ab_test_id',
        'ab_variant',
        'send_token',
        'sent_count',
        'open_count',
        'click_count',
        'type',
    ];

    protected $casts = [
        'blocks' => 'array',
        'style_settings' => 'array',
        'event_ids' => 'array',
        'segment_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function scopeAdmin($query)
    {
        return $query->where('type', 'admin');
    }

    public function scopeSchedule($query)
    {
        return $query->where('type', 'schedule');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipients()
    {
        return $this->hasMany(NewsletterRecipient::class);
    }

    public function abTest()
    {
        return $this->belongsTo(NewsletterAbTest::class, 'ab_test_id');
    }

    public static function defaultBlocks(Role $role): array
    {
        $blocks = [];

        if ($role->profile_image_url) {
            $blocks[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'profile_image',
                'data' => [],
            ];
        }

        if ($role->header_image_url) {
            $blocks[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'header_banner',
                'data' => [],
            ];
        }

        $blocks[] = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'heading',
            'data' => ['text' => '', 'level' => 'h1', 'align' => 'center'],
        ];

        $blocks[] = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'text',
            'data' => ['content' => ''],
        ];

        $blocks[] = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'events',
            'data' => ['layout' => 'cards', 'useAllEvents' => true, 'eventIds' => []],
        ];

        $blocks[] = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'text',
            'data' => ['content' => ''],
        ];

        $socialLinks = self::buildSocialLinksForRole($role);
        if (! empty($socialLinks)) {
            $blocks[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'social_links',
                'data' => ['links' => $socialLinks],
            ];
        }

        return $blocks;
    }

    public static function buildSocialLinksForRole(Role $role): array
    {
        $links = [];

        if ($role->website) {
            $links[] = ['platform' => 'website', 'url' => $role->website];
        }

        $socialLinks = is_string($role->social_links) ? json_decode($role->social_links, true) : $role->social_links;
        if (is_array($socialLinks)) {
            foreach ($socialLinks as $link) {
                $url = $link['url'] ?? '';
                if ($url) {
                    $links[] = ['platform' => self::detectPlatform($url), 'url' => $url];
                }
            }
        }

        return $links;
    }

    public static function detectPlatform(string $url): string
    {
        return UrlUtils::detectPlatform($url);
    }

    /**
     * What a newsletter saved before a setting existed is read with, so these stay as they were:
     * NewsletterService::renderHtml() lays the saved settings over them. The starting values of a
     * NEW newsletter are the preset's (templateDefaults()).
     */
    public static function defaultStyleSettings(): array
    {
        return [
            'backgroundColor' => '#ffffff',
            'accentColor' => '#4E81FA',
            'textColor' => '#333333',
            'fontFamily' => 'Arial',
            'buttonRadius' => 'rounded',
            'eventLayout' => 'cards',
            'footerText' => '',
            // The line an inbox shows after the subject. Empty means the opening of the first
            // text block, as the mail reads.
            'previewText' => '',
        ];
    }

    /**
     * $settings as ANOTHER newsletter, or a saved template, should start with them: the design,
     * without the preview text, which was written for one mail. The settings travel in three
     * places (a new newsletter starts from the last one's, or from a template's, and a newsletter
     * can be saved as a template), and a line carried over would be sent under a subject it was
     * never written for.
     *
     * A clone and an A/B variant are the SAME mail again and keep the line: they replicate() the
     * newsletter and do not come through here.
     */
    public static function designSettings(?array $settings): ?array
    {
        if ($settings !== null) {
            $settings['previewText'] = '';
        }

        return $settings;
    }

    public static function defaultStyleSettingsForRole(Role $role): array
    {
        $defaults = self::templateDefaults('modern');
        if ($role->accent_color) {
            $defaults['accentColor'] = $role->accent_color;
        }

        return $defaults;
    }

    /**
     * The presets as they were before 2026-10, for the three whose values changed. Only the look
     * a preset sets. Not the events layout: choosing a preset in the builder has always kept the
     * layout already chosen, so an owner who only ever clicked Minimal holds Modern's "cards",
     * and comparing it would call that design changed by hand.
     */
    private const PRESETS_BEFORE_2026_10 = [
        'modern' => ['backgroundColor' => '#ffffff', 'accentColor' => '#4E81FA', 'textColor' => '#333333', 'fontFamily' => 'Arial', 'buttonRadius' => 'rounded'],
        'minimal' => ['backgroundColor' => '#ffffff', 'accentColor' => '#666666', 'textColor' => '#333333', 'fontFamily' => 'Verdana', 'buttonRadius' => 'rounded'],
        'bold' => ['backgroundColor' => '#1a1a2e', 'accentColor' => '#e94560', 'textColor' => '#eaeaea', 'fontFamily' => 'Arial', 'buttonRadius' => 'rounded'],
    ];

    /**
     * $settings for a NEW newsletter that starts from an earlier one's: a design nobody changed
     * from an old preset arrives as that preset is today.
     *
     * A new newsletter starts from the last one's settings, so an owner who sent once before the
     * presets were redrawn would otherwise stay on Verdana and mid-grey links for good, with the
     * new values one click away and no reason to click. "Nobody changed" is strict: each of the
     * three colours, the typeface and the corners is as it arrived. One colour or the typeface chosen by hand and the design
     * is theirs, and it is left exactly as it is. Modern arrived in the schedule's own accent, so
     * that or the default blue both count, and whichever it is stays.
     */
    public static function movedToCurrentPreset(?string $template, ?array $settings, ?Role $role = null): ?array
    {
        $template = $template ?: 'modern';
        $before = self::PRESETS_BEFORE_2026_10[$template] ?? null;

        if ($settings === null || $before === null) {
            return $settings;
        }

        $same = fn ($a, $b) => strcasecmp((string) $a, (string) $b) === 0;

        foreach ($before as $key => $was) {
            $ownAccent = $key === 'accentColor' && $template === 'modern' && $role?->accent_color
                && $same($settings[$key] ?? null, $role->accent_color);

            if (! $ownAccent && ! $same($settings[$key] ?? null, $was)) {
                return $settings;
            }
        }

        $now = self::templateDefaults($template);
        if ($template === 'modern') {
            $now['accentColor'] = $settings['accentColor'];
        }

        return array_merge($settings, array_intersect_key($now, $before));
    }

    /**
     * The settings a preset arrives with. The designs themselves live in App\Utils\NewsletterTheme
     * and the newsletter views; a preset only chooses the colours, typeface and corners they start
     * from. System is the reader's own interface font (NewsletterTheme::FONTS).
     */
    public static function templateDefaults(string $template): array
    {
        return match ($template) {
            'classic' => [
                'backgroundColor' => '#faf9f6',
                'accentColor' => '#8B4513',
                'textColor' => '#2c2c2c',
                'fontFamily' => 'Georgia',
                'buttonRadius' => 'square',
                'eventLayout' => 'cards',
                'footerText' => '',
                'previewText' => '',
            ],
            'minimal' => [
                'backgroundColor' => '#ffffff',
                // Near-black, so Minimal's links are black. At mid-grey every link looked disabled.
                'accentColor' => '#111111',
                'textColor' => '#333333',
                'fontFamily' => 'System',
                'buttonRadius' => 'rounded',
                'eventLayout' => 'list',
                'footerText' => '',
                'previewText' => '',
            ],
            'bold' => [
                'backgroundColor' => '#1a1a2e',
                'accentColor' => '#e94560',
                'textColor' => '#eaeaea',
                'fontFamily' => 'System',
                'buttonRadius' => 'rounded',
                'eventLayout' => 'cards',
                'footerText' => '',
                'previewText' => '',
            ],
            'compact' => [
                'backgroundColor' => '#f5f5f5',
                'accentColor' => '#2d6a4f',
                'textColor' => '#333333',
                'fontFamily' => 'Trebuchet MS',
                'buttonRadius' => 'square',
                'eventLayout' => 'list',
                'footerText' => '',
                'previewText' => '',
            ],
            default => ['fontFamily' => 'System'] + self::defaultStyleSettings(), // 'modern'
        };
    }
}
