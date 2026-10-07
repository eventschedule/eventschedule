<?php

namespace App\Utils;

class HelpUtils
{
    private static array $mappings = [
        // Newsletter routes (flat, must be checked before {subdomain}/* patterns
        // because {subdomain} resolves to * on flat routes and */edit would match newsletters/{hash}/edit)
        // The builder and a sent newsletter's statistics, ahead of the list they hang from.
        'newsletters/create' => '/docs/newsletters#newsletter-builder',
        'newsletters/*/edit' => '/docs/newsletters#newsletter-builder',
        'newsletters/*/stats' => '/docs/newsletters#analytics',
        'newsletters*' => '/docs/newsletters',
        'newsletter-segments*' => '/docs/newsletters#managing-segments',
        'newsletter-import*' => '/docs/newsletters#importing-emails',
        // Without this the template editor (newsletter-templates/{hash}/edit) fell through to
        // {subdomain}/edit below and Help opened the Creating Schedules guide.
        'newsletter-templates*' => '/docs/newsletters#saved-templates',

        // Pages with section-level anchor mapping
        '{subdomain}/edit' => [
            'doc' => '/docs/creating-schedules',
            'anchors' => [
                'section-details' => '/docs/creating-schedules#details',
                'details-tab-general' => '/docs/creating-schedules#details-general',
                'details-tab-localization' => '/docs/creating-schedules#details-localization',
                'section-address' => '/docs/creating-schedules#address',
                'details-tab-contact' => '/docs/creating-schedules#contact-info',
                'section-merge' => '/docs/creating-schedules#merge',
                'section-style' => '/docs/schedule-styling#overview',
                'section-gallery' => '/docs/creating-schedules#gallery',
                'style-tab-animation' => '/docs/schedule-styling#list-animation',
                'style-tab-background' => '/docs/schedule-styling#backgrounds',
                // The row is "Header and layout": it opens on the header style, with custom CSS last.
                'style-tab-advanced' => '/docs/schedule-styling#header-style',
                'section-subschedules' => '/docs/creating-schedules#customize',
                'customize-tab-subschedules' => '/docs/creating-schedules#customize-subschedules',
                'customize-tab-custom-fields' => '/docs/creating-schedules#customize-custom-fields',
                'customize-tab-categories' => '/docs/creating-schedules#customize-categories',
                'customize-tab-custom-labels' => '/docs/creating-schedules#customize-custom-labels',
                'section-settings' => '/docs/creating-schedules#settings',
                'section-links' => '/docs/creating-schedules#videos-links',
                'links-tab-youtube_videos' => '/docs/creating-schedules#videos-links',
                'links-tab-social_links' => '/docs/creating-schedules#videos-links',
                'section-engagement' => '/docs/creating-schedules#engagement',
                'engagement-tab-feedback' => '/docs/creating-schedules#engagement-feedback',
                'engagement-tab-fan_content' => '/docs/creating-schedules#engagement-fan-content',
                'engagement-tab-requests' => '/docs/creating-schedules#engagement-requests',
                'engagement-tab-carpool' => '/docs/creating-schedules#engagement-carpool',
                'engagement-tab-sponsors' => '/docs/creating-schedules#engagement-sponsors',
                'engagement-tab-map' => '/docs/creating-schedules#engagement-venue-map',
                'engagement-tab-accommodation' => '/docs/creating-schedules#engagement-accommodation',
                'section-gift-cards' => '/docs/gift-cards#setup',
                'section-sources' => '/docs/creating-schedules#event-sources',
                'section-auto-import' => '/docs/creating-schedules#auto-import',
                'section-integrations' => '/docs/creating-schedules#integrations',
                'integration-tab-email' => '/docs/creating-schedules#integrations-email',
                'settings-tab-general' => '/docs/creating-schedules#settings-general',
                'settings-tab-notifications' => '/docs/creating-schedules#settings-notifications',
                'settings-tab-advanced' => '/docs/creating-schedules#settings-advanced',
                'integration-tab-google' => '/docs/creating-schedules#integrations-google',
                'integration-tab-microsoft' => '/docs/creating-schedules#integrations-microsoft',
                'integration-tab-caldav' => '/docs/creating-schedules#integrations-caldav',
                'integration-tab-advanced' => '/docs/creating-schedules#integrations-advanced',
            ],
        ],
        '{subdomain}/edit-event/*' => [
            'doc' => '/docs/creating-events#details',
            'anchors' => [
                'section-details' => '/docs/creating-events#details',
                'section-listing' => '/docs/creating-events#listing',
                'section-calendar-sync' => '/docs/creating-events#google-calendar',
                'section-venue' => '/docs/creating-events#venue',
                'section-gallery' => '/docs/creating-events#gallery',
                'section-participants' => '/docs/creating-events#participants',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-agenda' => '/docs/creating-events#agenda',
                'section-schedules' => '/docs/creating-events#schedules',
                'section-google-calendar' => '/docs/creating-events#google-calendar',
                'section-microsoft-calendar' => '/docs/creating-events#google-calendar',
                'section-tickets' => '/docs/tickets#general',
                'ticket-mode-external' => '/docs/tickets#external',
                'ticket-mode-rsvp' => '/docs/tickets#registration',
                'ticket-mode-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-payment' => '/docs/tickets#payment-row',
                'ticket-tab-options' => '/docs/tickets#options',
                'ticket-tab-promo_codes' => '/docs/tickets#promo-codes',
                'ticket-tab-add_ons' => '/docs/tickets#add-ons',
                'section-event-settings' => '/docs/creating-events#sponsors',
                'settings-tab-sponsors' => '/docs/creating-events#sponsors',
                'section-engagement' => '/docs/creating-events#engagement',
                'engagement-tab-polls' => '/docs/creating-events#polls',
                'engagement-tab-feedback' => '/docs/creating-events#feedback',
                'engagement-tab-fan_content' => '/docs/creating-events#fan-content',
                'engagement-tab-carpool' => '/docs/creating-schedules#engagement-carpool',
                // Fragments that open a tab inside Engagement (window.eventSectionAliases in event/edit.blade.php).
                'section-fan-content' => '/docs/creating-events#fan-content',
                'section-polls' => '/docs/creating-events#polls',
                'section-carpool' => '/docs/creating-schedules#engagement-carpool',
            ],
        ],
        '{subdomain}/add-event' => [
            'doc' => '/docs/creating-events#manual',
            'anchors' => [
                'section-details' => '/docs/creating-events#details',
                'section-listing' => '/docs/creating-events#listing',
                'section-calendar-sync' => '/docs/creating-events#google-calendar',
                'section-venue' => '/docs/creating-events#venue',
                'section-gallery' => '/docs/creating-events#gallery',
                'section-participants' => '/docs/creating-events#participants',
                'section-recurring' => '/docs/creating-events#recurring',
                'section-agenda' => '/docs/creating-events#agenda',
                'section-schedules' => '/docs/creating-events#schedules',
                'section-google-calendar' => '/docs/creating-events#google-calendar',
                'section-microsoft-calendar' => '/docs/creating-events#google-calendar',
                'section-tickets' => '/docs/tickets#general',
                'ticket-mode-external' => '/docs/tickets#external',
                'ticket-mode-rsvp' => '/docs/tickets#registration',
                'ticket-mode-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-tickets' => '/docs/tickets#ticketing',
                'ticket-tab-payment' => '/docs/tickets#payment-row',
                'ticket-tab-options' => '/docs/tickets#options',
                'ticket-tab-promo_codes' => '/docs/tickets#promo-codes',
                'ticket-tab-add_ons' => '/docs/tickets#add-ons',
                'section-event-settings' => '/docs/creating-events#sponsors',
                'settings-tab-sponsors' => '/docs/creating-events#sponsors',
                'section-engagement' => '/docs/creating-events#engagement',
                'engagement-tab-polls' => '/docs/creating-events#polls',
                'engagement-tab-feedback' => '/docs/creating-events#feedback',
                'engagement-tab-fan_content' => '/docs/creating-events#fan-content',
                'engagement-tab-carpool' => '/docs/creating-schedules#engagement-carpool',
                // Fragments that open a tab inside Engagement (window.eventSectionAliases in event/edit.blade.php).
                'section-fan-content' => '/docs/creating-events#fan-content',
                'section-polls' => '/docs/creating-events#polls',
                'section-carpool' => '/docs/creating-schedules#engagement-carpool',
            ],
        ],
        'settings' => [
            'doc' => '/docs/account-settings',
            'anchors' => [
                'section-profile' => '/docs/account-settings#profile',
                // The rows of the profile, by the pane each one opens: there is no section of
                // these names. Help follows a row when it is pressed (layouts/navigation).
                'profile-tab-localization' => '/docs/account-settings#localization',
                'profile-tab-appearance' => '/docs/account-settings#appearance',
                'profile-tab-accessibility' => '/docs/account-settings#accessibility',
                'section-payment-methods' => '/docs/account-settings#payments',
                'payment-tab-stripe' => '/docs/account-settings#stripe',
                'payment-tab-invoiceninja' => '/docs/account-settings#invoice-ninja',
                'payment-tab-payment-url' => '/docs/account-settings#payment-url',
                'payment-tab-payfast' => '/docs/account-settings#payfast',
                'payment-tab-paypal' => '/docs/account-settings#paypal',
                // A tab that holds several sections opens the guide at the first of them; pressing
                // inside one of its sections moves Help to that section (layouts/navigation).
                'section-security' => '/docs/account-settings#password',
                'section-integrations' => '/docs/account-settings#google',
                'section-developers' => '/docs/account-settings#api',
                'section-account-data' => '/docs/account-settings#backup',
                'section-api' => '/docs/account-settings#api',
                'section-webhooks' => '/docs/account-settings#webhooks',
                'section-google-calendar' => '/docs/account-settings#google',
                'section-microsoft-calendar' => '/docs/account-settings#microsoft',
                'section-facebook' => '/docs/account-settings#facebook',
                'section-backup' => '/docs/account-settings#backup',
                // Its two rows are the backup tool's own (a Vue app), reached by address.
                'backup-tab-export' => '/docs/account-settings#backup-export',
                'backup-tab-import' => '/docs/account-settings#backup-import',
                'section-app' => '/docs/account-settings#app-update',
                'section-password' => '/docs/account-settings#password',
                'section-two-factor' => '/docs/account-settings#two-factor',
                'section-data' => '/docs/account-settings#your-data',
                'section-delete' => '/docs/account-settings#delete-account',
            ],
        ],

        // Simple page-level mappings
        '{subdomain}/schedule' => '/docs/managing-schedules#schedule',
        '{subdomain}/appointments' => '/docs/appointments',
        '{subdomain}/templates' => '/docs/managing-schedules#templates',
        '{subdomain}/availability' => '/docs/managing-schedules#availability',
        // The seating tab, the designer and the box office console. No ticket-tab-* entry to go
        // with these: the plan picker lives on the Tickets tab itself, which already maps to
        // #ticketing, and there is no seating tab in that strip to key on.
        '{subdomain}/seating' => '/docs/allocated-seating#plans-tab',
        // Ahead of the two below, whose wildcards cross slashes: the one-date designer
        // (seating/occurrence/{hash}/design) matched "seating/*/design", and the report
        // (seating/box-office/{event}/report) matched "seating/box-office/*".
        '{subdomain}/seating/occurrence/*' => '/docs/allocated-seating#one-date',
        '{subdomain}/seating/box-office/*/report*' => '/docs/allocated-seating#report',
        '{subdomain}/seating/*/design' => '/docs/allocated-seating#build',
        '{subdomain}/seating/box-office/*' => '/docs/allocated-seating#box-office',
        '{subdomain}/requests' => '/docs/managing-schedules#requests',
        '{subdomain}/followers' => '/docs/managing-schedules#followers',
        '{subdomain}/team' => '/docs/managing-schedules#team',
        '{subdomain}/plan' => '/docs/managing-schedules#plan',
        '{subdomain}/audit-log' => '/docs/managing-schedules#audit-log',
        '{subdomain}/videos' => '/docs/managing-schedules#videos',
        '{subdomain}/merge-venues*' => '/docs/creating-schedules#merge',
        '{subdomain}/subscribe' => '/docs/creating-schedules',
        'my-carpools' => '/docs/creating-schedules#engagement-carpool',
        '{subdomain}/import' => '/docs/ai-import',
        '{subdomain}/import/ai' => '/docs/ai-import',
        '{subdomain}/import/eventbrite' => '/docs/ai-import#eventbrite-import',
        '{subdomain}/scan-agenda' => '/docs/scan-agenda',
        '{subdomain}/events-graphic*' => '/docs/event-graphics',
        'events' => '/docs/getting-started',
        // Explicit rather than load-bearing: '{subdomain}/merge-venues*' above already matches
        // this page, because resolvePattern() substitutes '*' for {subdomain} when there is no
        // route parameter. Kept so the mapping survives if that fallback ever tightens, and so
        // the page is not silently relying on it.
        'following/merge-venues*' => '/docs/creating-schedules#merge',
        'following' => '/docs/sharing#followers',
        'tickets' => '/docs/tickets',
        // /sales carries six tabs covering four different doc pages, so a flat mapping sent the
        // Help button to "Managing Sales" from the Installments, Subscriptions and Gift Cards
        // tabs alike. Keyed on the tab BUTTON ids, which is what navigation.blade.php reads from
        // a .sales-tab click and reconstructs from the ?tab= parameter on load.
        'sales' => [
            'doc' => '/docs/tickets#managing-sales',
            'anchors' => [
                'tab-sales' => '/docs/tickets#sales-list',
                'tab-waitlist' => '/docs/tickets#waitlist',
                'tab-feedback' => '/docs/tickets#feedback-tab',
                'tab-subscriptions' => '/docs/subscriptions#monitoring',
                'tab-installments' => '/docs/tickets#installments-tracking',
                'tab-gift-cards' => '/docs/gift-cards#managing',
            ],
        ],
        'sales.import' => '/docs/tickets#importing-attendees',
        // The signed-in dashboard.
        'dashboard' => '/docs/getting-started#dashboard',
        // Realtime is the second tab here: a schedule owner's own live view (not /admin/realtime,
        // which is the operator's and has its own entry below).
        'analytics' => [
            'doc' => '/docs/analytics',
            'anchors' => [
                'tab-web' => '/docs/analytics',
                'tab-revenue' => '/docs/analytics#revenue',
                'tab-checkins' => '/docs/analytics#checkins',
                'tab-realtime' => '/docs/analytics#realtime',
            ],
        ],
        // Buying an on-network promotion lives under /promotions, so the boost* pattern below
        // never matches it. Without this the Help button on the purchase form would open the
        // Facebook/Instagram docs, which describe a different product entirely.
        'promotions*' => '/docs/boost#on-network',
        'boost/create*' => '/docs/boost#quick-mode',
        'boost*' => '/docs/boost',
        'scan' => '/docs/tickets#check-in',
        'checkin' => '/docs/tickets#checkin-dashboard',
        'waitlist' => '/docs/tickets#waitlist',
        'referrals' => '/docs/referral-program',
        'admin/dashboard*' => '/docs/selfhost/admin#dashboard',
        'admin/realtime*' => '/docs/selfhost/admin#realtime',
        // The pages of the Insights, Manage and System tabs, in the navigation's own order
        // (admin/partials/_navigation). Each one opened the guide's front page until 2026-10.
        'admin/users*' => '/docs/selfhost/admin#insights-users',
        'admin/revenue*' => '/docs/selfhost/admin#insights-revenue',
        'admin/analytics*' => '/docs/selfhost/admin#insights-analytics',
        'admin/usage*' => '/docs/selfhost/admin#insights-usage',
        'admin/growth*' => '/docs/selfhost/admin#insights-growth',
        // Not the organizer's Boost guide: 'boost*' above does not match this path.
        'admin/boost*' => '/docs/selfhost/admin#manage-boost',
        'admin/schedules*' => '/docs/selfhost/admin#manage-plans',
        // Hosted only, and the operator's own walkthrough is on the SaaS side of the guide.
        'admin/domains*' => '/docs/saas/custom-domains#admin-management',
        'admin/referrals*' => '/docs/selfhost/admin#manage-referrals',
        'admin/newsletter*' => '/docs/selfhost/admin#manage-newsletters',
        'admin/blog*' => '/docs/selfhost/admin#manage-blog',
        'admin/audit-log*' => '/docs/selfhost/admin#system-audit-log',
        'admin/queue*' => '/docs/selfhost/admin#system-queue',
        'admin/logs*' => '/docs/selfhost/admin#system-logs',
        'admin/support' => '/docs/saas#support-chat',
        'admin/app-update*' => '/docs/selfhost/admin#system-app-update',
        'admin/translations*' => '/docs/selfhost/admin#system-translations',
        'admin/legal*' => '/docs/selfhost/admin#system-legal-pages',
        // Federation is mirrored into both docs trees; point the AP Help button at the
        // selfhost copy, which is the one every non-nexus install can act on.
        // admin/settings goes to the admin guide's own Settings section, not to this one:
        // federation is one card on a page that is mostly about header and footer code.
        'admin/federation*' => '/docs/selfhost/federation',
        'admin/settings*' => '/docs/selfhost/admin#system-settings',
        // The short form a new schedule starts on, which is what Getting Started describes.
        'new/*' => '/docs/getting-started#create-schedule',
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
            $requestPattern = self::resolvePattern($pattern);
            if (request()->is($requestPattern)) {
                if (is_array($value) && ! empty($value['anchors'])) {
                    $map = [];
                    foreach ($value['anchors'] as $sectionId => $docPath) {
                        $map[$sectionId] = marketing_url($docPath);
                    }

                    return $map;
                }

                // First matching pattern has no anchors - return empty
                return [];
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
