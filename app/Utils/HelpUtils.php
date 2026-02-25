<?php

namespace App\Utils;

class HelpUtils
{
    private static array $mappings = [
        // Pages with section-level anchor mapping
        '{subdomain}/edit' => [
            'doc' => '/docs/schedule-styling',
            'anchors' => [
                'section-details' => '/docs/schedule-basics#basic-information',
                'section-address' => '/docs/schedule-basics#location',
                'section-contact-info' => '/docs/schedule-basics#contact',
                'section-style' => '/docs/schedule-styling#overview',
                'section-subschedules' => '/docs/creating-schedules#subschedules',
                'section-settings' => '/docs/schedule-basics#settings',
                'section-auto-import' => '/docs/creating-schedules#auto-import',
                'section-integrations' => '/docs/creating-schedules#calendar-integrations',
                'section-email-settings' => '/docs/creating-schedules#email-settings',
            ],
        ],
        '{subdomain}/edit-event/*' => [
            'doc' => '/docs/creating-events#event-details',
            'anchors' => [
                'section-details' => '/docs/creating-events#event-details',
                'section-venue' => '/docs/creating-events#event-details',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-tickets' => '/docs/tickets#create-tickets',
                'section-privacy' => '/docs/creating-events#private-events',
                'section-custom-fields' => '/docs/creating-events#custom-fields',
                'section-fan-content' => '/docs/fan-content',
            ],
        ],
        '{subdomain}/add-event' => [
            'doc' => '/docs/creating-events#manual',
            'anchors' => [
                'section-details' => '/docs/creating-events#event-details',
                'section-venue' => '/docs/creating-events#event-details',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-tickets' => '/docs/tickets#create-tickets',
                'section-privacy' => '/docs/creating-events#private-events',
                'section-custom-fields' => '/docs/creating-events#custom-fields',
            ],
        ],
        'settings' => [
            'doc' => '/docs/account-settings',
            'anchors' => [
                'section-profile' => '/docs/account-settings#profile',
                'section-payment-methods' => '/docs/account-settings#payments',
                'section-api' => '/docs/account-settings#api',
                'section-google-calendar' => '/docs/account-settings#google',
                'section-app' => '/docs/account-settings#app-update',
                'section-password' => '/docs/account-settings#password',
                'section-two-factor' => '/docs/account-settings#password',
                'section-delete' => '/docs/account-settings#delete-account',
            ],
        ],

        // Simple page-level mappings
        '{subdomain}/schedule' => '/docs/schedule-basics',
        '{subdomain}/availability' => '/docs/availability',
        '{subdomain}/requests' => '/docs/schedule-basics',
        '{subdomain}/profile' => '/docs/schedule-basics',
        '{subdomain}/followers' => '/docs/sharing#followers',
        '{subdomain}/team' => '/docs/creating-schedules#team-members',
        '{subdomain}/plan' => '/docs/schedule-basics',
        '{subdomain}/videos' => '/docs/fan-content',
        '{subdomain}/subscribe' => '/docs/schedule-basics',
        '{subdomain}/import' => '/docs/creating-events#ai-import',
        '{subdomain}/scan-agenda' => '/docs/scan-agenda',
        '{subdomain}/events-graphic*' => '/docs/event-graphics',
        'events' => '/docs/getting-started',
        'following' => '/docs/sharing',
        'tickets' => '/docs/tickets',
        'sales' => '/docs/tickets#managing-sales',
        'analytics' => '/docs/analytics',
        'newsletters*' => '/docs/newsletters',
        'newsletter-segments*' => '/docs/newsletters#recipients',
        'newsletter-import*' => '/docs/newsletters',
        'boost*' => '/docs/boost',
        'scan' => '/docs/tickets#check-in',
        'new/*' => '/docs/creating-schedules',
    ];

    public static function getDocUrl(): string
    {
        foreach (self::$mappings as $pattern => $value) {
            $requestPattern = self::resolvePattern($pattern);
            if (request()->is($requestPattern)) {
                $docPath = is_array($value) ? $value['doc'] : $value;

                return marketing_url($docPath);
            }
        }

        return marketing_url('/docs');
    }

    public static function getAnchorMap(): array
    {
        foreach (self::$mappings as $pattern => $value) {
            if (! is_array($value) || empty($value['anchors'])) {
                continue;
            }

            $requestPattern = self::resolvePattern($pattern);
            if (request()->is($requestPattern)) {
                $map = [];
                foreach ($value['anchors'] as $sectionId => $docPath) {
                    $map[$sectionId] = marketing_url($docPath);
                }

                return $map;
            }
        }

        return [];
    }

    private static function resolvePattern(string $pattern): string
    {
        if (str_contains($pattern, '{subdomain}')) {
            $subdomain = request()->route('subdomain') ?? request()->subdomain ?? '*';

            return str_replace('{subdomain}', $subdomain, $pattern);
        }

        return $pattern;
    }
}
