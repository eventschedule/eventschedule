<?php

/**
 * Documentation manifest - the single source of truth for the /docs page list.
 *
 * Before this file existed the page list was duplicated seven times
 * (getDocNavigation(), the /docs index card grid, getDocSearchIndex()'s route
 * map, the search widget's icon map, sitemap.blade.php, HelpUtils.php and
 * RouteLoadTest) and had already drifted - the index card order did not match
 * the prev/next chain.
 *
 * Consumed by App\Utils\DocsUtils, <x-docs-page>, the docs left rail, the
 * /docs index grid and the docs search index.
 *
 * IMPORTANT: this file is var_export()ed by `php artisan config:cache`.
 * Scalars and arrays only - never call route(), url() or __() here. Store
 * route NAMES and paths; resolve them at request time in DocsUtils.
 *
 * Docs are intentionally English-only for SEO (the marketing layout
 * canonicalizes every ?lang= variant), so titles and blurbs stay plain
 * strings rather than translation keys.
 *
 * Array order within each group IS the display and prev/next order.
 *
 * Page keys mirror the blade path under resources/views/marketing/docs/
 * (e.g. 'selfhost/stripe'), so <x-docs-page page="..."> reads naturally.
 *
 * Per-page keys:
 *   group     - key into 'groups' below
 *   route     - route name, registered in BOTH route blocks in routes/web.php
 *   path      - URL path, used by the sitemap and to cross-check HelpUtils
 *   title     - <h1> and nav label
 *   nav_title - optional shorter label for the left rail
 *   blurb     - one line, used on the index cards and in search results
 *   icon      - key into resources/views/components/docs/icon.blade.php
 *   cluster   - index-page grouping (User Guide only)
 *   hub       - true when the page is also its group's landing page
 *   layout    - 'standard' (default) or 'reference' (wide, code-rail)
 *   plan      - optional 'pro' | 'enterprise', renders a badge in the hero
 *   published / modified - TechArticle JSON-LD dates
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    |
    | 'category' must match the category strings used by
    | MarketingController::getDocSearchIndex() so search results can be
    | labelled from the manifest.
    |
    | 'index_route' is the group's landing page. When it equals a page's own
    | route, DocsUtils::breadcrumb() suppresses the middle crumb so the page
    | does not link to itself.
    |
    */

    'groups' => [

        'user-guide' => [
            'title' => 'User Guide',
            'category' => 'User Guide',
            'blurb' => 'Learn how to use Event Schedule.',
            'icon' => 'book',
            'accent' => 'guide',
            'index_route' => null,
        ],

        'selfhost' => [
            'title' => 'Selfhost',
            'category' => 'Selfhost',
            'blurb' => 'Deploy Event Schedule on your own server.',
            'icon' => 'server',
            'accent' => 'selfhost',
            'index_route' => 'marketing.docs.selfhost',
        ],

        'saas' => [
            'title' => 'SaaS',
            'category' => 'SaaS',
            'blurb' => 'Run Event Schedule as a multi-tenant SaaS.',
            'icon' => 'cloud',
            'accent' => 'saas',
            'index_route' => 'marketing.docs.saas.setup',
        ],

        'developer' => [
            'title' => 'Developer',
            'category' => 'Developer',
            'blurb' => 'Build integrations with the REST API and webhooks.',
            'icon' => 'code',
            'accent' => 'developer',
            'index_route' => 'marketing.docs.developer.api',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Index page clusters
    |--------------------------------------------------------------------------
    |
    | The /docs index groups the User Guide into task-shaped clusters that
    | mirror the admin portal's structure.
    |
    | 'cols' is how many cards sit in a row at the widest breakpoint; the grid
    | spans themselves live in components/docs/card-grid.blade.php. Keep it that
    | way: Tailwind's content globs cover resources/views/** but NOT config/, so
    | a raw class string here is invisible to the JIT and silently never gets
    | generated. Only 3 and 4 are supported, because both fill complete rows at
    | every breakpoint with no filler tiles.
    |
    */

    'clusters' => [
        'set-up' => [
            'title' => 'Set up',
            'blurb' => 'Create an account, a schedule, and make it yours.',
            'accent' => 'blue',
            'cols' => 3,
        ],
        'events' => [
            'title' => 'Events',
            'blurb' => 'Add events by hand, from text, or from a photo.',
            'accent' => 'sky',
            'cols' => 3,
        ],
        'sell' => [
            'title' => 'Sell',
            'blurb' => 'Take money for tickets, passes and bookings.',
            'accent' => 'cyan',
            'cols' => 4,
        ],
        'promote' => [
            'title' => 'Promote',
            'blurb' => 'Get your events in front of an audience.',
            'accent' => 'teal',
            'cols' => 4,
        ],
        'manage' => [
            'title' => 'Manage',
            'blurb' => 'Run the day to day and see how it is going.',
            'accent' => 'emerald',
            'cols' => 4,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    'pages' => [

        // ---- User Guide -----------------------------------------------------

        'getting-started' => [
            'group' => 'user-guide',
            'cluster' => 'set-up',
            'route' => 'marketing.docs.getting_started',
            'path' => '/docs/getting-started',
            'title' => 'Getting Started',
            'blurb' => 'Create your account and set up your first schedule.',
            'icon' => 'bolt',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'creating-schedules' => [
            'group' => 'user-guide',
            'cluster' => 'set-up',
            'route' => 'marketing.docs.creating_schedules',
            'path' => '/docs/creating-schedules',
            'title' => 'Creating Schedules',
            'blurb' => 'Configure details, settings, sub-schedules, auto import, and integrations.',
            'icon' => 'cog',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'schedule-styling' => [
            'group' => 'user-guide',
            'cluster' => 'set-up',
            'route' => 'marketing.docs.schedule_styling',
            'path' => '/docs/schedule-styling',
            'title' => 'Schedule Styling',
            'blurb' => 'Customize colors, fonts, and branding for your schedule.',
            'icon' => 'swatch',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'creating-events' => [
            'group' => 'user-guide',
            'cluster' => 'events',
            'route' => 'marketing.docs.creating_events',
            'path' => '/docs/creating-events',
            'title' => 'Creating Events',
            'blurb' => 'Add events and configure event settings.',
            'icon' => 'plus',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'ai-import' => [
            'group' => 'user-guide',
            'cluster' => 'events',
            'route' => 'marketing.docs.ai_import',
            'path' => '/docs/ai-import',
            'title' => 'AI Import',
            'blurb' => 'Import events from text or images using AI.',
            'icon' => 'sparkles',
            'published' => '2024-01-01',
            'modified' => '2026-03-08',
        ],

        'scan-agenda' => [
            'group' => 'user-guide',
            'cluster' => 'events',
            'route' => 'marketing.docs.scan_agenda',
            'path' => '/docs/scan-agenda',
            'title' => 'Scan Agenda',
            'blurb' => 'Use AI to scan a photo of a printed agenda and automatically create event parts.',
            'icon' => 'camera',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'tickets' => [
            'group' => 'user-guide',
            'cluster' => 'sell',
            'route' => 'marketing.docs.tickets',
            'path' => '/docs/tickets',
            'title' => 'Selling Tickets',
            'blurb' => 'Set up ticketing and manage sales.',
            'icon' => 'ticket',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'subscriptions' => [
            'group' => 'user-guide',
            'cluster' => 'sell',
            'route' => 'marketing.docs.subscriptions',
            'path' => '/docs/subscriptions',
            'title' => 'Subscriptions & Passes',
            'nav_title' => 'Subscriptions & Passes',
            'blurb' => 'Sell one pass a guest reuses across many events.',
            'icon' => 'pass',
            'published' => '2026-06-11',
            'modified' => '2026-06-11',
        ],

        'gift-cards' => [
            'group' => 'user-guide',
            'cluster' => 'sell',
            'route' => 'marketing.docs.gift_cards',
            'path' => '/docs/gift-cards',
            'title' => 'Gift Cards',
            'blurb' => 'Sell prepaid gift cards buyers send to someone else.',
            'icon' => 'gift',
            'published' => '2026-07-16',
            'modified' => '2026-07-16',
        ],

        'appointments' => [
            'group' => 'user-guide',
            'cluster' => 'sell',
            'route' => 'marketing.docs.appointments',
            'path' => '/docs/appointments',
            'title' => 'Appointments',
            'blurb' => 'Let guests book a time with you on a public page.',
            'icon' => 'clock',
            'published' => '2026-07-26',
            'modified' => '2026-07-30',
        ],

        'sharing' => [
            'group' => 'user-guide',
            'cluster' => 'promote',
            'route' => 'marketing.docs.sharing',
            'path' => '/docs/sharing',
            'title' => 'Sharing Your Schedule',
            'nav_title' => 'Sharing',
            'blurb' => 'Embed, share on social, and grow followers.',
            'icon' => 'share',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'event-graphics' => [
            'group' => 'user-guide',
            'cluster' => 'promote',
            'route' => 'marketing.docs.event_graphics',
            'path' => '/docs/event-graphics',
            'title' => 'Event Graphics',
            'blurb' => 'Generate shareable images for social media.',
            'icon' => 'image',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'newsletters' => [
            'group' => 'user-guide',
            'cluster' => 'promote',
            'route' => 'marketing.docs.newsletters',
            'path' => '/docs/newsletters',
            'title' => 'Newsletters',
            'blurb' => 'Send branded emails to your audience.',
            'icon' => 'mail',
            'published' => '2024-01-01',
            'modified' => '2026-03-08',
        ],

        'boost' => [
            'group' => 'user-guide',
            'cluster' => 'promote',
            'route' => 'marketing.docs.boost',
            'path' => '/docs/boost',
            'title' => 'Boost',
            'blurb' => 'Promote events with automated Facebook and Instagram ads.',
            'icon' => 'megaphone',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'managing-schedules' => [
            'group' => 'user-guide',
            'cluster' => 'manage',
            'route' => 'marketing.docs.managing_schedules',
            'path' => '/docs/managing-schedules',
            'title' => 'Managing Schedules',
            'blurb' => 'Manage events, team, availability, requests, and more.',
            'icon' => 'clipboard',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'analytics' => [
            'group' => 'user-guide',
            'cluster' => 'manage',
            'route' => 'marketing.docs.analytics',
            'path' => '/docs/analytics',
            'title' => 'Analytics',
            'blurb' => 'Track views, devices, traffic sources, and conversions.',
            'icon' => 'chart',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'account-settings' => [
            'group' => 'user-guide',
            'cluster' => 'manage',
            'route' => 'marketing.docs.account_settings',
            'path' => '/docs/account-settings',
            'title' => 'Account Settings',
            'blurb' => 'Manage your profile, payments, and API access.',
            'icon' => 'account',
            'published' => '2024-01-01',
            'modified' => '2026-08-09',
        ],

        'referral-program' => [
            'group' => 'user-guide',
            'cluster' => 'manage',
            'route' => 'marketing.docs.referral_program',
            'path' => '/docs/referral-program',
            'title' => 'Referral Program',
            'blurb' => 'Earn free months by referring other organizers.',
            'icon' => 'referral',
            'published' => '2024-01-01',
            'modified' => '2026-03-01',
        ],

        // ---- Selfhost -------------------------------------------------------

        'selfhost/index' => [
            'group' => 'selfhost',
            'hub' => true,
            'route' => 'marketing.docs.selfhost',
            'path' => '/docs/selfhost',
            'title' => 'Selfhost',
            'nav_title' => 'Overview',
            'blurb' => 'Install, configure and run Event Schedule on your own server.',
            'icon' => 'server',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'selfhost/installation' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.installation',
            'path' => '/docs/selfhost/installation',
            'title' => 'Installation',
            'blurb' => 'Step-by-step server setup: database, web server, and first run.',
            'icon' => 'terminal',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'selfhost/stripe' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.stripe',
            'path' => '/docs/selfhost/stripe',
            'title' => 'Stripe Integration',
            'nav_title' => 'Stripe',
            'blurb' => 'Set up Stripe payments for a selfhost or SaaS deployment.',
            'icon' => 'credit-card',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'selfhost/google-calendar' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.google_calendar',
            'path' => '/docs/selfhost/google-calendar',
            'title' => 'Google Calendar',
            'blurb' => 'Bidirectional Google Calendar sync.',
            'icon' => 'calendar',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'selfhost/microsoft-calendar' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.microsoft_calendar',
            'path' => '/docs/selfhost/microsoft-calendar',
            'title' => 'Outlook Calendar',
            'blurb' => 'Bidirectional Outlook sync via Microsoft Graph.',
            'icon' => 'calendar',
            'published' => '2026-07-14',
            'modified' => '2026-07-14',
        ],

        'selfhost/email' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.email',
            'path' => '/docs/selfhost/email',
            'title' => 'Email Setup',
            'nav_title' => 'Email',
            'blurb' => 'Configure SMTP, Mailgun, Amazon SES or another mail driver.',
            'icon' => 'mail',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'selfhost/ai' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.ai',
            'path' => '/docs/selfhost/ai',
            'title' => 'AI Setup',
            'nav_title' => 'AI',
            'blurb' => 'Configure Google Gemini or OpenAI for import, scanning and translation.',
            'icon' => 'sparkles',
            'published' => '2024-01-01',
            'modified' => '2026-03-11',
        ],

        'selfhost/boost' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.boost',
            'path' => '/docs/selfhost/boost',
            'title' => 'Boost Setup',
            'nav_title' => 'Boost',
            'blurb' => 'Configure the Meta ads integration behind the boost feature.',
            'icon' => 'megaphone',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'selfhost/admin' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.admin',
            'path' => '/docs/selfhost/admin',
            'title' => 'Admin Panel',
            'blurb' => 'Monitor users, revenue and analytics, and manage platform settings.',
            'icon' => 'shield',
            'published' => '2024-01-01',
            'modified' => '2026-07-30',
        ],

        'selfhost/federation' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.federation',
            'path' => '/docs/selfhost/federation',
            'title' => 'Federation',
            'blurb' => 'Share your public events with the eventschedule.com listings.',
            'icon' => 'globe',
            'published' => '2026-07-26',
            'modified' => '2026-07-26',
        ],

        'selfhost/accessibility' => [
            'group' => 'selfhost',
            'route' => 'marketing.docs.selfhost.accessibility',
            'path' => '/docs/selfhost/accessibility',
            'title' => 'Web accessibility',
            'nav_title' => 'Accessibility',
            'blurb' => 'Accessibility declarations, configuration and user-generated content.',
            'icon' => 'accessibility',
            'published' => '2026-05-03',
            'modified' => '2026-05-03',
        ],

        // ---- SaaS -----------------------------------------------------------

        'saas/setup' => [
            'group' => 'saas',
            'hub' => true,
            'route' => 'marketing.docs.saas.setup',
            'path' => '/docs/saas',
            'title' => 'SaaS Setup',
            'nav_title' => 'Overview',
            'blurb' => 'Subdomain-based multi-tenant routing, branding and plans.',
            'icon' => 'cloud',
            'published' => '2024-01-01',
            'modified' => '2026-02-01',
        ],

        'saas/custom-domains' => [
            'group' => 'saas',
            'route' => 'marketing.docs.saas.custom_domains',
            'path' => '/docs/saas/custom-domains',
            'title' => 'Custom Domains',
            'blurb' => 'Automatic SSL for tenant domains on DigitalOcean App Platform.',
            'icon' => 'link',
            'published' => '2026-02-01',
            'modified' => '2026-02-01',
        ],

        'saas/twilio' => [
            'group' => 'saas',
            'route' => 'marketing.docs.saas.twilio',
            'path' => '/docs/saas/twilio',
            'title' => 'Twilio Integration',
            'nav_title' => 'Twilio',
            'blurb' => 'Phone verification and WhatsApp messaging.',
            'icon' => 'phone',
            'published' => '2026-02-27',
            'modified' => '2026-02-27',
        ],

        'saas/federation' => [
            'group' => 'saas',
            'route' => 'marketing.docs.saas.federation',
            'path' => '/docs/saas/federation',
            'title' => 'Federation',
            'blurb' => 'Share your public events with the eventschedule.com listings.',
            'icon' => 'globe',
            'published' => '2026-07-26',
            'modified' => '2026-07-26',
        ],

        // SaaS group only: a single-tenant selfhost has no free tier, so nothing is ever
        // monetized there and the page would only confuse.
        'saas/monetization' => [
            'group' => 'saas',
            'route' => 'marketing.docs.saas.monetization',
            'path' => '/docs/saas/monetization',
            'title' => 'Monetization',
            'blurb' => 'Show ads on free schedules and sell promotional placement to paid ones.',
            'icon' => 'megaphone',
            'published' => '2026-07-28',
            'modified' => '2026-07-28',
        ],

        // ---- Developer ------------------------------------------------------

        'developer/api' => [
            'group' => 'developer',
            'hub' => true,
            'layout' => 'reference',
            'route' => 'marketing.docs.developer.api',
            'path' => '/docs/developer/api',
            'title' => 'API Reference',
            'blurb' => 'Programmatically manage schedules and events over REST.',
            'icon' => 'code',
            'published' => '2024-01-01',
            'modified' => '2026-03-01',
        ],

        'developer/webhooks' => [
            'group' => 'developer',
            'route' => 'marketing.docs.developer.webhooks',
            'path' => '/docs/developer/webhooks',
            'title' => 'Webhooks',
            'blurb' => 'HMAC-signed POST notifications for sales, events and check-ins.',
            'icon' => 'webhook',
            'published' => '2026-03-01',
            'modified' => '2026-03-01',
        ],

    ],

];
