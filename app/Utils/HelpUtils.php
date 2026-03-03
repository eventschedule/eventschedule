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
                'details-tab-general' => '/docs/creating-schedules#details-general',
                'details-tab-localization' => '/docs/creating-schedules#details-localization',
                'section-address' => '/docs/creating-schedules#address',
                'details-tab-contact' => '/docs/creating-schedules#contact-info',
                'section-style' => '/docs/schedule-styling#overview',
                'section-subschedules' => '/docs/creating-schedules#subschedules',
                'section-settings' => '/docs/creating-schedules#settings',
                'section-engagement' => '/docs/creating-schedules#engagement',
                'engagement-tab-feedback' => '/docs/creating-schedules#engagement-feedback',
                'engagement-tab-fan_content' => '/docs/creating-schedules#engagement-fan-content',
                'engagement-tab-requests' => '/docs/creating-schedules#engagement-requests',
                'section-auto-import' => '/docs/creating-schedules#auto-import',
                'section-integrations' => '/docs/creating-schedules#integrations',
                'integration-tab-email' => '/docs/creating-schedules#integrations-email',
                'settings-tab-general' => '/docs/creating-schedules#settings-general',
                'settings-tab-custom-domain' => '/docs/creating-schedules#custom-domain',
                'settings-tab-custom-fields' => '/docs/creating-schedules#settings-custom-fields',
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
                'section-rsvp' => '/docs/tickets#free-events',
                'ticket-mode-external' => '/docs/tickets#external',
                'ticket-mode-rsvp' => '/docs/tickets#registration',
                'ticket-mode-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-payment' => '/docs/tickets#payment',
                'ticket-tab-options' => '/docs/tickets#options',
                'ticket-tab-promo_codes' => '/docs/tickets#promo-codes',
                'section-event-settings' => '/docs/creating-events#privacy',
                'settings-tab-privacy' => '/docs/creating-events#privacy',
                'settings-tab-custom_fields' => '/docs/creating-events#custom-fields',
                'section-engagement' => '/docs/creating-events#polls',
                'engagement-tab-polls' => '/docs/creating-events#polls',
                'engagement-tab-feedback' => '/docs/tickets#feedback',
                'engagement-tab-fan_content' => '/docs/creating-events#fan-content',
                'section-embed-widget' => '/docs/tickets#embed-widget',
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
                'section-rsvp' => '/docs/tickets#free-events',
                'ticket-mode-external' => '/docs/tickets#external',
                'ticket-mode-rsvp' => '/docs/tickets#registration',
                'ticket-mode-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-payment' => '/docs/tickets#payment',
                'ticket-tab-options' => '/docs/tickets#options',
                'ticket-tab-promo_codes' => '/docs/tickets#promo-codes',
                'section-event-settings' => '/docs/creating-events#privacy',
                'settings-tab-privacy' => '/docs/creating-events#privacy',
                'settings-tab-custom_fields' => '/docs/creating-events#custom-fields',
                'section-engagement' => '/docs/creating-events#polls',
                'engagement-tab-polls' => '/docs/creating-events#polls',
                'engagement-tab-feedback' => '/docs/tickets#feedback',
                'engagement-tab-fan_content' => '/docs/creating-events#fan-content',
                'section-embed-widget' => '/docs/tickets#embed-widget',
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
                'section-webhooks' => '/docs/account-settings#webhooks',
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
        'checkin' => '/docs/tickets#check-in',
        'waitlist' => '/docs/tickets#waitlist',
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
