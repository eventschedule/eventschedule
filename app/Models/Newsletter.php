<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'blocks' => 'array',
        'style_settings' => 'array',
        'event_ids' => 'array',
        'segment_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

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

        return $blocks;
    }

    public static function defaultStyleSettings(): array
    {
        return [
            'backgroundColor' => '#ffffff',
            'accentColor' => '#4E81FA',
            'textColor' => '#333333',
            'fontFamily' => 'Arial',
            'buttonRadius' => 'rounded',
            'eventLayout' => 'cards',
        ];
    }

    public static function defaultStyleSettingsForRole(Role $role): array
    {
        $defaults = self::defaultStyleSettings();
        if ($role->accent_color) {
            $defaults['accentColor'] = $role->accent_color;
        }
        return $defaults;
    }

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
            ],
            'minimal' => [
                'backgroundColor' => '#ffffff',
                'accentColor' => '#666666',
                'textColor' => '#333333',
                'fontFamily' => 'Verdana',
                'buttonRadius' => 'rounded',
                'eventLayout' => 'list',
            ],
            'bold' => [
                'backgroundColor' => '#1a1a2e',
                'accentColor' => '#e94560',
                'textColor' => '#eaeaea',
                'fontFamily' => 'Arial',
                'buttonRadius' => 'rounded',
                'eventLayout' => 'cards',
            ],
            'compact' => [
                'backgroundColor' => '#f5f5f5',
                'accentColor' => '#2d6a4f',
                'textColor' => '#333333',
                'fontFamily' => 'Trebuchet MS',
                'buttonRadius' => 'square',
                'eventLayout' => 'list',
            ],
            default => self::defaultStyleSettings(), // 'modern'
        };
    }
}
