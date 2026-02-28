<?php

namespace App\Utils;

class HelpUtils
{
    private static array $mappings = [
        // Pages with section-level anchor mapping
        '{subdomain}/edit' => [
            'doc' => '/docs/creating-schedules',
            'anchors' => [
                'section-details' => '/docs/creating-schedules#details',
                'section-address' => '/docs/creating-schedules#address',
                'section-contact-info' => '/docs/creating-schedules#contact-info',
                'section-style' => '/docs/schedule-styling#overview',
                'section-subschedules' => '/docs/creating-schedules#subschedules',
                'section-settings' => '/docs/creating-schedules#settings',
                'section-auto-import' => '/docs/creating-schedules#auto-import',
                'section-integrations' => '/docs/creating-schedules#integrations',
                'section-email-settings' => '/docs/creating-schedules#email-settings',
                'settings-tab-general' => '/docs/creating-schedules#settings-general',
                'settings-tab-custom-fields' => '/docs/creating-schedules#settings-custom-fields',
                'settings-tab-requests' => '/docs/creating-schedules#settings-requests',
                'settings-tab-notifications' => '/docs/creating-schedules#settings-notifications',
                'settings-tab-advanced' => '/docs/creating-schedules#settings-advanced',
                'integration-tab-google' => '/docs/creating-schedules#integrations-google',
                'integration-tab-caldav' => '/docs/creating-schedules#integrations-caldav',
            ],
        ],
        '{subdomain}/edit-event/*' => [
            'doc' => '/docs/creating-events#details',
            'anchors' => [
                'section-details' => '/docs/creating-events#details',
                'section-venue' => '/docs/creating-events#venue',
                'section-participants' => '/docs/creating-events#participants',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-agenda' => '/docs/creating-events#agenda',
                'section-schedules' => '/docs/creating-events#schedules',
                'section-google-calendar' => '/docs/creating-events#google-calendar',
                'section-tickets' => '/docs/tickets#general',
                'section-privacy' => '/docs/creating-events#privacy',
                'section-custom-fields' => '/docs/creating-events#custom-fields',
                'section-fan-content' => '/docs/creating-events#fan-content',
                'section-polls' => '/docs/creating-events#polls',
            ],
        ],
        '{subdomain}/add-event' => [
            'doc' => '/docs/creating-events#manual',
            'anchors' => [
                'section-details' => '/docs/creating-events#details',
                'section-venue' => '/docs/creating-events#venue',
                'section-participants' => '/docs/creating-events#participants',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-agenda' => '/docs/creating-events#agenda',
                'section-schedules' => '/docs/creating-events#schedules',
                'section-google-calendar' => '/docs/creating-events#google-calendar',
                'section-tickets' => '/docs/tickets#general',
                'section-privacy' => '/docs/creating-events#privacy',
                'section-custom-fields' => '/docs/creating-events#custom-fields',
                'section-fan-content' => '/docs/creating-events#fan-content',
                'section-polls' => '/docs/creating-events#polls',
            ],
        ],
        'settings' => [
            'doc' => '/docs/account-settings',
            'anchors' => [
                'section-profile' => '/docs/account-settings#profile',
                'section-payment-methods' => '/docs/account-settings#payments',
                'payment-tab-stripe' => '/docs/account-settings#stripe',
                'payment-tab-invoiceninja' => '/docs/account-settings#invoice-ninja',
                'payment-tab-payment-url' => '/docs/account-settings#payment-url',
                'section-api' => '/docs/account-settings#api',
                'section-google-calendar' => '/docs/account-settings#google',
                'section-app' => '/docs/account-settings#app-update',
                'section-password' => '/docs/account-settings#password',
                'section-two-factor' => '/docs/account-settings#two-factor',
                'section-delete' => '/docs/account-settings#delete-account',
            ],
        ],

        // Simple page-level mappings
        '{subdomain}/schedule' => '/docs/managing-schedules#schedule',
        '{subdomain}/availability' => '/docs/managing-schedules#availability',
        '{subdomain}/requests' => '/docs/managing-schedules#requests',
        '{subdomain}/profile' => '/docs/managing-schedules#profile',
        '{subdomain}/followers' => '/docs/managing-schedules#followers',
        '{subdomain}/team' => '/docs/managing-schedules#team',
        '{subdomain}/plan' => '/docs/managing-schedules#plan',
        '{subdomain}/videos' => '/docs/managing-schedules#videos',
        '{subdomain}/subscribe' => '/docs/creating-schedules',
        '{subdomain}/import' => '/docs/ai-import',
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
