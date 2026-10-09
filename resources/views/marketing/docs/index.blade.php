@php
    use App\Utils\DocsContents;
    use App\Utils\DocsUtils;

    // Both the visible glossary and the DefinedTermSet JSON-LD below read this one array. `guide`
    // is where the term is explained at length: a page key from config/docs.php, with an anchor.
    // `family` is the column the word stands in ($families, below).
    $glossary = [
        ['term' => 'Schedule', 'family' => 'calendar', 'guide' => 'creating-schedules', 'def' => 'Your event calendar, with its own URL, branding and settings. One schedule holds as many events as you like, on every plan.'],
        ['term' => 'Schedule type', 'family' => 'calendar', 'guide' => 'getting-started#schedule-types', 'def' => 'Talent, Venue or Curator. The type decides what a schedule gets: a venue has a full address, a curator aggregates events from other schedules. Choose carefully, because the type is fixed once the schedule is saved.'],
        ['term' => 'Sub-schedule', 'family' => 'calendar', 'guide' => 'creating-schedules#customize-subschedules', 'def' => 'A category for events inside one schedule, such as Live Music, Comedy or Workshops. Each one has its own color and its own URL, and visitors can filter by it. It organizes and color-codes only: it cannot hide an event.'],
        ['term' => 'Embed', 'family' => 'calendar', 'guide' => 'sharing#embed', 'def' => 'Your schedule shown on another website inside an iframe. The calendar embed and the RSVP form are free; embedding the ticket purchase widget needs Pro.'],
        ['term' => 'Admin Panel', 'family' => 'calendar', 'guide' => 'managing-schedules', 'def' => 'The management side of one schedule, opened from its name in the sidebar: a row of tabs where you add events, answer requests and manage your team, with its settings behind Edit Schedule. Not the selfhost admin panel at /admin, which manages a whole install.'],
        ['term' => 'Event', 'family' => 'events', 'guide' => 'creating-events', 'def' => 'A single occurrence with a date, time, location and details. An event belongs to a schedule and can repeat daily, weekly, every few weeks, monthly or yearly, with dates you add or skip.'],
        ['term' => 'Request', 'family' => 'events', 'guide' => 'managing-schedules#requests', 'def' => 'An event waiting for your approval: one a visitor submitted from your public request page, or one another schedule added you to. Requests sit on the Requests tab until you accept or decline them, and you are emailed when new ones land unless you turn that off.'],
        ['term' => 'Unclaimed page', 'family' => 'events', 'guide' => 'creating-events#claim', 'def' => 'A schedule page created for a performer or venue you name on an event before they are on Event Schedule. It says who created it, credits each date to the schedule that added it, and stays out of search engines. The person it names claims it with an account on the email address or phone number it lists, and becomes its owner.'],
        ['term' => 'Ticket', 'family' => 'events', 'guide' => 'tickets', 'def' => 'A ticket type on an event, such as General or VIP. Buyers pay through your own Stripe or PayPal account (or Payfast, Invoice Ninja, a payment link or cash), with no platform fees on any plan. A ticket type that carries a price needs Pro; one priced at zero sells on every plan.'],
        ['term' => 'RSVP', 'family' => 'events', 'guide' => 'tickets#registration', 'def' => 'Free sign-up for an event, with an optional capacity limit per date. Available on every plan, and no payment account is needed.'],
        ['term' => 'Follower', 'family' => 'audience', 'guide' => 'sharing#followers', 'def' => 'Someone with an account who follows your schedule, which shares their name and email with you. Pressing Follow on its own reaches an account follower only through a newsletter you send; the automatic digest goes to people who asked for email updates (see Subscriber).'],
        ['term' => 'Subscriber', 'family' => 'audience', 'guide' => 'newsletters#email-subscribers', 'def' => 'Someone who asked your schedule for email updates: from the sign-up panel on your page, with their name and email, confirmed from the link we send, or from a tick box when they buy a ticket or register. Confirming the sign-up panel also sets up an account for them wherever sign-up is open. Subscribers get a digest automatically when you publish events your schedule created, at most one every few days, and it does not draw on the newsletter allowance.'],
        ['term' => 'Interest list', 'family' => 'audience', 'guide' => 'tickets#interest-list', 'def' => 'The email addresses left on one event with "Tell me when tickets go on sale", or "Tell me if anything changes" once it is selling, on the “Notify me” card that a schedule switches on for the events it creates. No account and no name. They hear about that date only: when tickets go on sale, a reminder before it starts, a notice if it is cancelled, and any notice you choose to send if the date or venue changes. It is not a subscription to your schedule.'],
        ['term' => 'Newsletter', 'family' => 'audience', 'guide' => 'newsletters', 'def' => 'An email you write and send to a segment. The monthly allowance counts recipients rather than sends: 10 free, 100 on Pro, 1,000 on Enterprise, and unlimited on selfhost or with your own mail server.'],
        ['term' => 'Segment', 'family' => 'audience', 'guide' => 'newsletters#managing-segments', 'def' => 'A saved audience for newsletters: all followers, ticket buyers, a ticket waitlist, buyers from one sub-schedule, or a list you enter by hand.'],
    ];

    // The three things the words are about. Each takes an act's colour and an icon of the guide's.
    $families = [
        'calendar' => ['name' => 'Your calendar', 'accent' => 'blue', 'icon' => 'calendar'],
        'events' => ['name' => 'What is on it', 'accent' => 'cyan', 'icon' => 'ticket'],
        'audience' => ['name' => 'Your audience', 'accent' => 'emerald', 'icon' => 'mail'],
    ];

    // What people come here to do. Ticking one adds its steps to the reading list beside it.
    // `tier` is the plan the thing needs (docs/FEATURES.md); none means it costs nothing.
    $goals = [
        ['key' => 'import', 'label' => 'Import my events'],
        ['key' => 'rsvp', 'label' => 'Take free sign-ups'],
        ['key' => 'tickets', 'label' => 'Sell tickets', 'tier' => 'pro'],
        ['key' => 'seats', 'label' => 'Reserved seats', 'tier' => 'enterprise'],
        ['key' => 'passes', 'label' => 'Sell passes, gift cards', 'tier' => 'pro'],
        ['key' => 'bookings', 'label' => 'Take bookings'],
        ['key' => 'website', 'label' => 'Embed on my website'],
        ['key' => 'audience', 'label' => 'Email my audience'],
        ['key' => 'social', 'label' => 'Make social graphics'],
        ['key' => 'team', 'label' => 'Work as a team', 'tier' => 'enterprise'],
    ];

    // The reading list, in the order the product is set up. `goal` is the tick that adds a step;
    // 'base' steps are everybody's. `to` is a page key and anchor from config/docs.php.
    $steps = [
        ['goal' => 'base', 'title' => 'Create your schedule', 'to' => 'getting-started#create-schedule'],
        ['goal' => 'base', 'title' => 'Make it look like yours', 'to' => 'schedule-styling#overview'],
        ['goal' => 'base', 'title' => 'Add your first event', 'to' => 'creating-events#manual'],
        ['goal' => 'import', 'title' => 'Bring in the rest from a link, text or a flyer', 'to' => 'ai-import#ai-import'],
        ['goal' => 'import', 'title' => 'Keep it in step with your calendar', 'to' => 'creating-schedules#calendar-sync'],
        ['goal' => 'rsvp', 'title' => 'Open free sign-ups', 'to' => 'tickets#registration'],
        ['goal' => 'tickets', 'title' => 'Connect a way to get paid', 'to' => 'tickets#payment'],
        ['goal' => 'tickets', 'title' => 'Set up your ticket types', 'to' => 'tickets#ticket-types'],
        ['goal' => 'seats', 'title' => 'Draw your seating plan', 'to' => 'allocated-seating#build'],
        ['goal' => 'seats', 'title' => 'Put the seats on sale', 'to' => 'allocated-seating#sell'],
        ['goal' => 'passes', 'title' => 'Create a pass', 'to' => 'subscriptions#setup'],
        ['goal' => 'passes', 'title' => 'Switch on gift cards', 'to' => 'gift-cards#setup'],
        ['goal' => 'bookings', 'title' => 'List what can be booked', 'to' => 'appointments#appointment-types'],
        ['goal' => 'bookings', 'title' => 'Set your weekly hours', 'to' => 'appointments#weekly-hours'],
        ['goal' => 'base', 'title' => 'Share your page', 'to' => 'sharing#schedule-url'],
        ['goal' => 'website', 'title' => 'Put the calendar on your website', 'to' => 'sharing#embed'],
        ['goal' => 'audience', 'title' => 'Start collecting subscribers', 'to' => 'sharing#followers'],
        ['goal' => 'audience', 'title' => 'Send your first newsletter', 'to' => 'newsletters#newsletter-builder'],
        ['goal' => 'social', 'title' => 'Make a graphic of your events to post', 'to' => 'event-graphics#overview'],
        ['goal' => 'tickets', 'title' => 'Scan tickets at the door', 'to' => 'tickets#check-in'],
        ['goal' => 'team', 'title' => 'Bring in your team', 'to' => 'managing-schedules#team'],
    ];

    // Questions that come up most, each answered by one section.
    $asked = [
        ['How do refunds work?', 'tickets#refunds'],
        ['Embed my calendar', 'sharing#embed'],
        ['Events that repeat', 'creating-events#recurring'],
        ['Import from Eventbrite', 'ai-import#eventbrite-import'],
        ['Scan tickets at the door', 'tickets#check-in'],
        ['Use my own domain', 'creating-schedules#custom-domain'],
    ];

    // The three shelves for people who run the software or write code against it. `line` is
    // where the guide prints the first line each shelf shows; the look is keyed on `key`.
    $doors = [
        [
            'key' => 'selfhost',
            'tag' => 'Self-managed',
            'title' => 'Selfhost',
            'lede' => 'Run Event Schedule on your own server. A selfhosted install resolves to Enterprise, so no plan gate applies.',
            'file' => 'crontab',
            'line' => 'selfhost/installation#cron',
            'why' => 'The cron entry behind reminder emails, calendar sync and released reservations.',
            'where' => 'Installation, the cron job',
        ],
        [
            'key' => 'saas',
            'tag' => 'Multi-tenant',
            'title' => 'SaaS',
            'lede' => 'Run Event Schedule as a multi-tenant SaaS with subdomains, plans and per-tenant custom domains.',
            'file' => '.env',
            'line' => 'saas/setup#core-settings',
            'why' => 'The setting that turns on SaaS mode, with a subdomain for each customer.',
            'where' => 'SaaS Setup, core settings',
        ],
        [
            'key' => 'developer',
            'tag' => 'REST API',
            'title' => 'Developer',
            'lede' => 'Drive Event Schedule from your own code over REST. API access is a Pro feature, on reads as well as writes.',
            'file' => 'cURL Example',
            'line' => 'developer/api#authentication',
            'why' => 'Every endpoint but Register and Login takes this one header.',
            'where' => 'API Reference, authentication',
        ],
    ];

    $tierNames = ['pro' => 'Pro', 'enterprise' => 'Enterprise'];
    $goalNames = array_column($goals, 'label', 'key');
    $numberWords = [1 => 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty'];

    $pageCount = count(DocsUtils::pages());
    $clusters = array_filter(DocsUtils::clusters(), fn ($cluster) => count($cluster['pages']) > 0);
    $arrow = 'M13 7l5 5m0 0l-5 5m5-5H6';

    // The name of a part of a guide, as that guide's own contents list calls it.
    $partName = function (string $key, string $anchor): ?string {
        foreach (DocsContents::for($key) as $part) {
            if ($part['anchor'] === $anchor) {
                return $part['label'];
            }
            foreach ($part['children'] as $child) {
                if ($child['anchor'] === $anchor) {
                    return $child['label'];
                }
            }
        }

        return null;
    };

    $description = 'Guides for running your schedule on Event Schedule: events, tickets, subscribers and sharing, plus selfhost installation, SaaS operations and the REST API.';
@endphp

<x-marketing-layout :docs="true" :hp="true">
    <x-slot name="title">Event Schedule Documentation: User Guide, Selfhost, API</x-slot>
    <x-slot name="breadcrumbTitle">Documentation</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>

    <x-slot name="structuredData">
        {{-- Every other doc page gets its TechArticle from the docs-page component. This page
             renders the layout directly, so it emits its own. The date comes from the manifest. --}}
        <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd([
                '@context' => 'https://schema.org',
                '@type' => 'TechArticle',
                'headline' => 'Event Schedule Documentation',
                'description' => $description,
                'author' => \App\Utils\SeoUtils::organizationRef(),
                'publisher' => \App\Utils\SeoUtils::organization(),
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
                'datePublished' => '2024-01-01',
                'dateModified' => collect(DocsUtils::pages())->max('modified') ?: '2024-01-01',
            ], true) !!}
        </script>

        <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd([
                '@context' => 'https://schema.org',
                '@type' => 'DefinedTermSet',
                'name' => 'Event Schedule Glossary',
                'description' => 'Key terms used throughout Event Schedule',
                'hasDefinedTerm' => array_map(fn ($g) => [
                    '@type' => 'DefinedTerm',
                    'name' => $g['term'],
                    'description' => $g['def'],
                ], $glossary),
            ], true) !!}
        </script>
    </x-slot>

    {{-- Script-only controls (the goals, the stage) are laid out only when this class is on the
         page's wrapper. Without script every step and every guide's contents are simply printed. --}}
    <script {!! nonce_attr() !!}>
        document.getElementById('hp').classList.add('dx-js');
    </script>

    <style {!! nonce_attr() !!}>
        {{-- ==============================================================
           /docs: the home of the guide.

           Three jobs, in the order a visitor has them: find one answer (the search and the
           questions under it), find where to begin (tick what you want to do and the reading
           list is cut to that), and see what a guide holds before opening it (the line-up: point
           at a name and the stage beside it shows that guide's screen and its own contents).

           Everything is the house kit's tokens (--hp-*). The one colour of its own is each act's
           accent (--dx-a), which the old page already had as five card colours.

           Every note here is a Blade comment, so none of it is sent.
           ============================================================== --}}
        #hp .dx { --dx-a: #2f66ea; }
        #hp [data-dx-accent="sky"] { --dx-a: #0369a1; }
        #hp [data-dx-accent="cyan"] { --dx-a: #0e7490; }
        #hp [data-dx-accent="teal"] { --dx-a: #0f766e; }
        #hp [data-dx-accent="emerald"] { --dx-a: #047857; }
        .dark #hp .dx { --dx-a: #8db0ff; }
        .dark #hp [data-dx-accent="sky"] { --dx-a: #7dd3fc; }
        .dark #hp [data-dx-accent="cyan"] { --dx-a: #67e8f9; }
        .dark #hp [data-dx-accent="teal"] { --dx-a: #5eead4; }
        .dark #hp [data-dx-accent="emerald"] { --dx-a: #6ee7b7; }

        #hp .dx [hidden] { display: none !important; }
        #hp:not(.dx-js) .dx-if-js,
        #hp.dx-js .dx-if-plain { display: none; }
        #hp .dx-mono {
            font-family: var(--hp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--hp-ink-3);
        }
        #hp .dx-tile {
            display: grid;
            flex: none;
            place-items: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.8rem;
            background: color-mix(in srgb, var(--dx-a) 17%, transparent);
            color: var(--dx-a);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        #hp .dx-tile svg { width: 1.3rem; height: 1.3rem; }
        #hp .dx-tier {
            display: inline-flex;
            flex: none;
            align-items: center;
            min-height: 1.45rem;
            padding: 0 0.5rem;
            border: 1px solid currentColor;
            border-radius: 999px;
            font-family: var(--hp-mono);
            font-size: 0.66rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
            color: #2456d6;
        }
        .dark #hp .dx-tier { color: #8db0ff; }
        {{-- Enterprise is amber wherever the site names the tier. --}}
        #hp .dx-tier[data-tier="enterprise"] { color: #a4500a; }
        .dark #hp .dx-tier[data-tier="enterprise"] { color: #fbbf24; }

        {{-- ---------------------------------------------------------------
           The hero. The kit clips a hero to cut its sky off; here the sky clips itself, because
           the search results open below the field and must be free to hang over the next section.
           --------------------------------------------------------------- --}}
        #hp .dx-hero.is-short { z-index: 20; overflow: visible; padding-bottom: clamp(2.25rem, 5vh, 3.5rem); }
        #hp .dx-hero .hp-hero-sky { overflow: hidden; }
        #hp .dx-search { position: relative; z-index: 5; margin-top: clamp(1.6rem, 3.4vh, 2.3rem); }
        #hp .dx-search [data-docs-search] { max-width: 44rem; margin-inline: auto; text-align: start; }
        #hp .dx-search input[data-role="input"] {
            height: 4rem;
            padding-inline: 3.5rem 4.5rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.25rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            -webkit-backdrop-filter: none;
            backdrop-filter: none;
            font-size: 1.125rem;
            color: var(--hp-ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #hp .dx-search input[data-role="input"]::placeholder { color: var(--hp-ink-3); opacity: 1; }
        #hp .dx-search input[data-role="input"]:focus {
            border-color: var(--hp-blue);
            box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.3), var(--hp-card-shadow);
            outline: 0;
        }
        #hp .dx-search [data-docs-search] > svg { inset-inline-start: 1.25rem; width: 1.35rem; height: 1.35rem; color: var(--hp-ink-3); }
        #hp .dx-search kbd {
            inset-inline-end: 1.1rem;
            height: 1.75rem;
            padding-inline: 0.6rem;
            border-color: var(--hp-line-2);
            border-radius: 0.5rem;
            font-size: 0.8rem;
            color: var(--hp-ink-3);
        }
        #hp .dx-search [data-role="clear"] { inset-inline-end: 1rem; width: 2rem; height: 2rem; color: var(--hp-ink-3); }
        #hp .dx-search [data-role="results"] {
            right: 0;
            left: 0;
            width: auto;
            max-height: min(62vh, 31rem);
            margin-top: 0.6rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.25rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-pop-shadow);
            transform: none;
        }
        #hp .dx-search [data-role="results"] a { border-color: var(--hp-line); }
        #hp .dx-search [data-role="results"] a:hover,
        #hp .dx-search [data-role="results"] a[aria-selected="true"] { background: var(--hp-bg); }
        #hp .dx-search mark { padding: 0; border-radius: 0.2rem; background: rgba(78, 129, 250, 0.24); color: inherit; }

        {{-- The questions are a line of text, not a second row of pills: the page's parts by
           number, just under them, are the pills. --}}
        .dx-ask { margin-top: 1.1rem; font-size: 1rem; line-height: 2.1; text-align: center; text-wrap: balance; color: var(--hp-ink-3); }
        #hp .dx-ask .dx-mono { margin-inline-end: 0.6rem; }
        .dx-ask a {
            margin-inline: 0.55rem;
            padding-block: 0.3rem;
            white-space: nowrap;
            font-variation-settings: 'wght' 560;
            color: var(--hp-ink);
            text-decoration: underline;
            text-decoration-color: var(--hp-line-2);
            text-decoration-thickness: 1px;
            text-underline-offset: 0.28em;
            transition: color 0.2s ease, text-decoration-color 0.2s ease;
        }
        .dx-ask a:hover { color: var(--hp-blue); text-decoration-color: currentColor; }
        @media (max-width: 639.98px) {
            .dx-ask a:nth-of-type(n+5) { display: none; }
            #hp .dx-search input[data-role="input"] { padding-inline: 3.1rem 3.1rem; font-size: 1rem; }
            #hp .dx-search [data-docs-search] > svg { inset-inline-start: 1.05rem; }
        }
        {{-- A key to press means nothing where there is no keyboard. --}}
        @media (hover: none) {
            #hp .dx-search kbd { display: none; }
        }

        {{-- ---------------------------------------------------------------
           Start here: what you want to do on one side, the reading list for it on the other.
           --------------------------------------------------------------- --}}
        .dx-start { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2rem, 4vw, 4rem); }
        @media (min-width: 1024px) {
            .dx-start { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; }
        }
        @media (min-width: 1024px) and (min-height: 760px) {
            {{-- The shorter column stays in view beside the longer: the goals beside a long list,
               the list beside the goals before anything is ticked. --}}
            .dx-start > * { position: sticky; top: 6rem; }
        }
        .dx-path-anchor { scroll-margin-top: 5.5rem; }
        .dx-goals { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.55rem; margin-top: 1.75rem; }
        @media (min-width: 640px) {
            .dx-goals { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        #hp:not(.dx-js) .dx-goals,
        #hp:not(.dx-js) .dx-goals-note { display: none; }
        #hp .dx-goal {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            width: 100%;
            min-height: 3.25rem;
            padding: 0.5rem 0.8rem 0.5rem 0.75rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 1rem;
            background: var(--hp-bg);
            font-size: 1rem;
            font-weight: 700;
            font-variation-settings: 'wght' 620;
            line-height: 1.25;
            text-align: start;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .dark #hp .dx-goal { background: var(--hp-bg-2); }
        #hp .dx-goal:hover { border-color: var(--hp-blue); color: var(--hp-ink); transform: translateY(-1px); }
        .dx-goal-words { flex: 1 1 auto; min-width: 0; }
        .dx-goal-box {
            display: grid;
            flex: none;
            place-items: center;
            width: 1.4rem;
            height: 1.4rem;
            border: 1.5px solid var(--hp-ink-3);
            border-radius: 0.45rem;
            color: #fff;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .dark .dx-goal-box { color: #07102a; }
        .dx-goal-box svg { width: 0.9rem; height: 0.9rem; opacity: 0; transform: scale(0.5); transition: opacity 0.15s ease, transform 0.2s cubic-bezier(0.2, 1.4, 0.4, 1); }
        #hp .dx-goal[aria-pressed="true"] {
            border-color: var(--hp-blue);
            background: color-mix(in srgb, var(--hp-blue) 10%, var(--hp-bg-2));
            color: var(--hp-ink);
        }
        .dx-goal[aria-pressed="true"] .dx-goal-box { border-color: var(--hp-blue); background: var(--hp-blue); }
        .dx-goal[aria-pressed="true"] .dx-goal-box svg { opacity: 1; transform: none; }
        .dx-goals-note { margin-top: 1rem; font-size: 0.92rem; color: var(--hp-ink-3); }
        {{-- Where the list is below the goals: what the last tick did, and the way down to it. --}}
        .dx-seen {
            position: sticky;
            bottom: 0.75rem;
            z-index: 5;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem 1rem;
            margin-top: 1rem;
            {{-- Its end is kept clear: the accessibility launcher stands in that corner. --}}
            padding: 0.85rem 4.5rem 0.85rem 1.05rem;
            border-radius: 1rem;
            background: var(--hp-ink);
            box-shadow: var(--hp-pop-shadow);
            font-size: 0.95rem;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            color: var(--hp-bg);
        }
        .dx-seen-go { display: inline-flex; flex: none; align-items: center; gap: 0.35rem; }
        .dx-seen svg { width: 1rem; height: 1rem; }
        @media (min-width: 1024px) {
            #hp .dx-seen { display: none; }
        }

        .dx-path {
            position: relative;
            padding: clamp(1.1rem, 2.4vw, 1.75rem) clamp(1.1rem, 2.6vw, 2rem) clamp(1.1rem, 2.2vw, 1.6rem);
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background:
                radial-gradient(36rem 16rem at 70% 0%, var(--hp-glow), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-pop-shadow);
        }
        .dx-path-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; padding-bottom: 0.8rem; border-bottom: 2px solid var(--hp-ink); }
        .dx-path-tools { display: inline-flex; align-items: center; gap: 0.9rem; }
        #hp .dx-copy {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2.25rem;
            padding: 0 0.75rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 0.7rem;
            background: var(--hp-bg-2);
            font-size: 0.86rem;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            color: var(--hp-ink);
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        #hp .dx-copy:hover { border-color: var(--hp-blue); transform: translateY(-1px); }
        .dx-copy svg { width: 0.95rem; height: 0.95rem; }
        .dx-steps { counter-reset: dx-step; }
        .dx-step { counter-increment: dx-step; border-bottom: 1px solid var(--hp-line); }
        #hp.dx-js .dx-step:not(.is-on) { display: none; }
        .dx-step a {
            display: grid;
            grid-template-columns: 2.2rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0 0.9rem;
            padding: 0.8rem 0.25rem;
        }
        .dx-step a::before {
            content: counter(dx-step, decimal-leading-zero);
            font-family: var(--hp-mono);
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--hp-ink-3);
        }
        .dx-step-title { display: block; font-size: 1.12rem; font-weight: 700; font-variation-settings: 'wght' 720; letter-spacing: -0.02em; line-height: 1.3; color: var(--hp-ink); transition: color 0.2s ease; }
        .dx-step-where { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem 0.6rem; margin-top: 0.15rem; font-size: 0.9rem; line-height: 1.4; color: var(--hp-ink-3); }
        {{-- The goal that put a step on the list. Only shown once goals can be ticked. --}}
        .dx-step-for {
            padding: 0.05rem 0.55rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--hp-blue) 12%, transparent);
            font-size: 0.8rem;
            font-variation-settings: 'wght' 600;
            color: var(--hp-ink-2);
        }
        #hp:not(.dx-js) .dx-step-for { display: none; }
        .dx-step svg { width: 1.1rem; height: 1.1rem; color: var(--hp-ink-3); transition: transform 0.2s ease, color 0.2s ease; }
        .dx-step a:hover .dx-step-title { color: var(--hp-blue); }
        .dx-step a:hover svg { color: var(--hp-blue); transform: translateX(3px); }
        .dx-step.is-new { animation: dx-step-in 0.5s cubic-bezier(0.2, 0.7, 0.2, 1) both; }
        @keyframes dx-step-in {
            from { opacity: 0; transform: translateY(-0.5rem); background-color: color-mix(in srgb, var(--hp-blue) 16%, transparent); }
            to { opacity: 1; transform: none; background-color: transparent; }
        }
        .dx-step-ghost { margin-top: 0.8rem; padding: 0.85rem 1rem; border: 1.5px dashed var(--hp-line-2); border-radius: 0.9rem; font-size: 0.95rem; color: var(--hp-ink-3); }
        .dx-path-note { margin-top: 1rem; font-size: 0.9rem; color: var(--hp-ink-3); }

        {{-- ---------------------------------------------------------------
           The line-up and the stage.

           In the markup each guide is its name followed by its own panel, so the reading order
           and the tab order are: a name, what is in it, the next name. From a laptop up the
           panels are lifted out of that column by the grid: every wrapper is display: contents,
           the names take the first column row by row, and every panel is given the whole second
           column and made sticky in it. Only the panel of the guide that is pointed at is
           displayed, so they never stand on each other.
           --------------------------------------------------------------- --}}
        .dx-guide-head { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem 3rem; }
        @media (min-width: 1024px) {
            .dx-guide-head { grid-template-columns: minmax(0, 1fr) minmax(0, 25rem); align-items: end; }
            {{-- The note ends where the stage under it ends (see .dx-bill). --}}
            #hp.dx-js .dx-guide-head { margin-inline-end: calc(-1 * max(0rem, (min(100vw - 2.5rem, 96rem) - 76rem) / 2)); }
        }
        .dx-keep { white-space: nowrap; }
        .dx-help {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.15rem;
            border: 1px solid var(--hp-line);
            border-radius: 1.25rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            font-size: 0.98rem;
            line-height: 1.5;
            color: var(--hp-ink-2);
        }
        #hp .dx-help strong { color: var(--hp-ink); }
        .dx-help-key {
            display: grid;
            flex: none;
            place-items: center;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 0.65rem;
            background: var(--hp-ink);
            font-family: var(--hp-mono);
            font-size: 1rem;
            font-weight: 700;
            color: var(--hp-bg);
        }

        .dx-bill { margin-top: clamp(2rem, 4vw, 3.25rem); }
        .dx-act-head { padding: 1.5rem 0 0.6rem; }
        .dx-act:first-child .dx-act-head { padding-top: 0; }
        .dx-act-row { display: flex; align-items: baseline; gap: 0.75rem; }
        #hp .dx-act-no { color: var(--dx-a); }
        #hp .dx-act-name { font-family: var(--hp-mono); font-size: 0.8rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink); }
        .dx-act-row::after { content: ""; flex: 1 1 auto; height: 1px; background: var(--hp-line-2); transform: translateY(-0.25em); }
        .dx-act-blurb { margin-top: 0.3rem; font-size: 0.95rem; color: var(--hp-ink-3); }

        #hp .dx-g { margin: 0; font-size: inherit; letter-spacing: inherit; }
        #hp .dx-name {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            margin-inline: -0.6rem;
            padding: 0.5rem 0.6rem;
            border-radius: 1rem;
            font-size: clamp(1.35rem, 0.9vw + 1rem, 1.8rem);
            font-weight: 700;
            font-variation-settings: 'wght' 800;
            letter-spacing: -0.035em;
            line-height: 1.15;
            color: var(--hp-ink);
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .dx-name-go { flex: none; width: 1.3rem; height: 1.3rem; margin-inline-start: auto; color: var(--dx-a); opacity: 0; transform: translateX(-0.4rem); transition: opacity 0.2s ease, transform 0.2s ease; }
        #hp .dx-name:hover { color: var(--dx-a); }
        #hp.dx-js .dx-item.is-on .dx-name { background: color-mix(in srgb, var(--dx-a) 9%, transparent); color: var(--dx-a); }
        #hp.dx-js .dx-item.is-on .dx-name .dx-tile { background: var(--dx-a); color: #fff; }
        .dark #hp.dx-js .dx-item.is-on .dx-name .dx-tile { color: #07102a; }
        #hp.dx-js .dx-item.is-on .dx-name-go { opacity: 1; transform: none; }

        .dx-panel {
            margin: 0.4rem 0 1.1rem;
            border: 1px solid var(--hp-line);
            border-radius: 1.6rem;
            background:
                radial-gradient(36rem 18rem at 82% 0%, var(--hp-glow), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            overflow: hidden;
        }
        {{-- The picture: one of the guide's own screenshots (1280 by 757), from its top left corner.
           What is cut away depends on what it is a picture of (data-dx-frame): of the app, the
           sidebar and the top bar; of a dialog open over the app, everything but the dialog; of
           a page a visitor sees, nothing. --dx-z enlarges it where the frame is small, so that a
           small frame shows part of the screen at a size that can be read. --}}
        .dx-shot {
            --dx-z: 1;
            --dx-edge: 0;
            margin: 0.9rem 0.9rem 0;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.1rem;
            background: var(--hp-bg-2);
            overflow: hidden;
        }
        .dx-shot-view { position: relative; width: 100%; background: var(--hp-bg-3); aspect-ratio: 16 / 10; overflow: hidden; }
        .dx-shot-view[data-dx-next] { cursor: pointer; }
        .dx-shot picture { display: block; }
        .dx-shot img { display: block; width: calc(129.03% * var(--dx-z)); max-width: none; height: auto; margin-top: calc(-5.65% * var(--dx-z)); margin-left: calc(-29.03% * var(--dx-z)); }
        .dx-shot[data-dx-frame="dialog"] img { width: calc(133.3% * var(--dx-z)); margin-top: 0; margin-left: calc(-16.7% * var(--dx-z)); }
        .dx-shot[data-dx-frame="page"] img { width: calc(107% * var(--dx-z)); margin-top: 0; margin-left: calc((100% - 107% * var(--dx-z)) / 2); }
        .dx-shot .dx-shot-dark,
        .dark .dx-shot .dx-shot-light { display: none; }
        .dark .dx-shot .dx-shot-dark { display: block; }
        #hp:not(.dx-js) .dx-shot { display: none; }
        {{-- Where the picture is enlarged it runs off the frame's right edge; a soft edge there
           says the cut is meant. --}}
        .dx-shot-view::after { content: ""; position: absolute; inset: 0 0 0 auto; width: 3rem; background: linear-gradient(to right, transparent, var(--hp-bg-3)); opacity: var(--dx-edge); pointer-events: none; }
        {{-- The guide's other screens, a dot each, on a strip of its own under the picture. --}}
        .dx-shot-bar { display: flex; align-items: center; gap: 0.75rem; min-height: 2.6rem; padding: 0.3rem 0.6rem 0.3rem 0.95rem; border-top: 1px solid var(--hp-line-2); color: var(--hp-ink-2); }
        .dx-shot-cap { flex: 1 1 auto; min-width: 0; overflow: hidden; font-size: 0.9rem; font-variation-settings: 'wght' 600; text-overflow: ellipsis; white-space: nowrap; }
        .dx-pips { display: flex; flex: none; align-items: center; }
        #hp .dx-pip { position: relative; width: 1.5rem; height: 1.75rem; border: 0; background: transparent; cursor: pointer; }
        .dx-pip::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.55rem;
            height: 0.55rem;
            margin: -0.275rem 0 0 -0.275rem;
            border-radius: 999px;
            background: var(--hp-ink-3);
            opacity: 0.5;
            transition: width 0.2s ease, margin 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
        }
        .dx-pip:hover::before { opacity: 1; }
        .dx-pip[aria-pressed="true"]::before { width: 1.2rem; margin-left: -0.6rem; background: var(--dx-a); opacity: 1; }
        .dx-shot-of { flex: none; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; color: var(--hp-ink-3); }
        @media (max-width: 639.98px) {
            {{-- On a phone the frame shows the first part of the screen at a size that can be read. --}}
            .dx-shot { --dx-z: 1.6; --dx-edge: 1; }
            .dx-shot-view { aspect-ratio: 16 / 11; }
            .dx-shot[data-dx-frame="page"],
            .dx-shot[data-dx-frame="dialog"] { --dx-z: 1.25; }
            .dx-shot-of { display: none; }
        }

        .dx-panel-body { padding: 1.1rem 1.5rem 1.2rem; }
        .dx-panel-top { display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem 0.9rem; }
        .dx-panel-title { flex: 1 1 14rem; min-width: 0; }
        #hp .dx-panel-name { font-size: 1.5rem; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.035em; line-height: 1.1; color: var(--hp-ink); }
        .dx-panel-blurb { margin-top: 0.3rem; font-size: 0.98rem; line-height: 1.45; color: var(--hp-ink-2); }
        .dx-parts { margin-top: 0.95rem; padding-top: 0.75rem; border-top: 1px solid var(--hp-line); column-gap: 1.5rem; }
        @media (min-width: 560px) {
            .dx-parts { column-count: 2; }
        }
        .dx-parts li { break-inside: avoid; }
        .dx-parts a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0;
            font-size: 0.98rem;
            font-weight: 400;
            font-variation-settings: 'wght' 520;
            line-height: 1.35;
            color: var(--hp-ink-2);
            transition: color 0.15s ease;
        }
        .dx-parts a::before { content: ""; flex: none; width: 0.36rem; height: 0.36rem; border-radius: 999px; background: var(--dx-a); }
        .dx-parts a:hover,
        .dx-parts a.is-shown { color: var(--hp-ink); }
        .dx-parts a:hover span,
        .dx-parts a.is-shown span { text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; }
        .dx-parts-pic { flex: none; width: 1rem; height: 1rem; color: var(--dx-a); opacity: 0.75; }
        #hp .dx-parts .dx-parts-more a { font-variation-settings: 'wght' 700; font-weight: 700; color: var(--dx-a); }
        .dx-parts .dx-parts-more a::before { background: transparent; }
        {{-- "and N more in the guide" stands in for the lines a frame leaves out. Each of the
           three is shown only by the rule that leaves those lines out. --}}
        .dx-parts .dx-parts-more { display: none; }
        .dx-panel-foot { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.4rem; margin-top: 0.9rem; padding-top: 0.75rem; border-top: 1px solid var(--hp-line); }
        .dx-panel-foot .hp-more { margin-inline-start: auto; font-size: 0.95rem; }
        {{-- Without script a panel stands directly under its name, which need not be said twice. --}}
        #hp:not(.dx-js) .dx-panel-top .dx-tile,
        #hp:not(.dx-js) .dx-panel-name { display: none; }

        @media (max-width: 1023.98px) {
            {{-- A phone and a tablet: one column, a guide opens under its own name. --}}
            #hp.dx-js .dx-panel { display: none; }
            #hp.dx-js .dx-item.is-on .dx-panel { display: block; animation: dx-open 0.3s ease both; }
            #hp.dx-js .dx-name-go { opacity: 0.55; transform: rotate(90deg); }
            #hp.dx-js .dx-item.is-on .dx-name-go { opacity: 1; transform: rotate(-90deg); }
            #hp.dx-js .dx-panel-top .dx-tile,
            #hp.dx-js .dx-panel-name { display: none; }
            #hp.dx-js .dx-panel-blurb { margin-top: 0; }
            {{-- Eight lines, then how many are left. --}}
            #hp.dx-js .dx-parts.cut-8 li:nth-child(n+9):not(.dx-parts-more) { display: none; }
            #hp.dx-js .dx-parts.cut-8 .dx-rest-8 { display: block; }
        }
        @keyframes dx-open {
            from { opacity: 0; transform: translateY(-0.4rem); }
            to { opacity: 1; transform: none; }
        }
        {{-- Which sentence introduces the line-up: pointing, where there is a pointer and room
           for the stage; pressing, everywhere else. --}}
        #hp .dx-lead-point { display: none; }
        @media (min-width: 1024px) and (hover: hover) {
            #hp.dx-js .dx-lead-point { display: block; }
            #hp .dx-lead-tap { display: none; }
        }
        @media (min-width: 1024px) {
            #hp.dx-js .dx-bill {
                display: grid;
                grid-template-columns: minmax(0, 22rem) minmax(0, 1fr);
                grid-template-rows: repeat(var(--dx-rows), auto);
                column-gap: clamp(2rem, 4vw, 4.5rem);
                {{-- The names keep the page's left edge; the stage runs on to the right as far
                   as a wide window lets it. --}}
                margin-inline-end: calc(-1 * max(0rem, (min(100vw - 2.5rem, 96rem) - 76rem) / 2));
            }
            #hp.dx-js .dx-act,
            #hp.dx-js .dx-item { display: contents; }
            #hp.dx-js .dx-act-head,
            #hp.dx-js .dx-g { grid-column: 1; }
            #hp.dx-js .dx-panel {
                display: none;
                grid-column: 2;
                grid-row: 1 / -1;
                position: sticky;
                top: 5.5rem;
                align-self: start;
                margin: 0;
                border-radius: 1.9rem;
                box-shadow: var(--hp-pop-shadow);
            }
            #hp.dx-js .dx-item.is-on .dx-panel { display: block; }
            {{-- The stage keeps one height whichever guide is on it: the heading is given the
               room of a two-line blurb, and the contents twelve cells (eleven lines and the
               count of the rest, in two, three or four columns). The picture takes what the
               window has left, and is cropped by that limit, never narrowed. --}}
            #hp.dx-js .dx-shot { --dx-z: 1.45; --dx-edge: 1; }
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 34.2rem); min-height: 8rem; }
            #hp.dx-js .dx-panel-title { min-height: 4.6rem; }
            #hp.dx-js .dx-parts { min-height: 13rem; }
            #hp.dx-js .dx-item.is-on .dx-shot img { animation: dx-shot-in 0.35s ease both; }
            #hp.dx-js .dx-item.is-on .dx-panel-body { animation: dx-body-in 0.3s ease both; }
        }
        @media (min-width: 1280px) {
            #hp.dx-js .dx-bill { grid-template-columns: minmax(0, 25.5rem) minmax(0, 1fr); }
            #hp.dx-js .dx-shot { --dx-z: 1; --dx-edge: 0; }
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 30.1rem); }
            #hp.dx-js .dx-parts { column-count: 3; min-height: 8.9rem; }
        }
        @media (min-width: 1600px) {
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 28rem); }
            #hp.dx-js .dx-parts { column-count: 4; min-height: 6.9rem; }
        }
        @media (min-width: 1024px) and (min-height: 860.02px) {
            #hp.dx-js .dx-parts.cut-11 li:nth-child(n+12):not(.dx-parts-more) { display: none; }
            #hp.dx-js .dx-parts.cut-11 .dx-rest-11 { display: block; }
        }
        {{-- A laptop's window is short. There the contents give way before the picture does
           (eight cells: seven lines and the count, or nine with three columns), and the
           picture is a little enlarged so that what is left of it can still be read. --}}
        @media (min-width: 1024px) and (max-height: 860px) {
            #hp.dx-js .dx-parts.cut-7 li:nth-child(n+8):not(.dx-parts-more) { display: none; }
            #hp.dx-js .dx-parts.cut-7 .dx-rest-7 { display: block; }
            #hp.dx-js .dx-shot { --dx-z: 1.6; --dx-edge: 1; }
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 30.1rem); }
            #hp.dx-js .dx-parts { min-height: 8.9rem; }
        }
        @media (min-width: 1280px) and (max-width: 1599.98px) and (max-height: 860px) {
            #hp.dx-js .dx-parts.cut-7 li:nth-child(n+8):not(.dx-parts-more) { display: list-item; }
            #hp.dx-js .dx-parts.cut-7 .dx-rest-7 { display: none; }
            #hp.dx-js .dx-parts.cut-8 li:nth-child(n+9):not(.dx-parts-more) { display: none; }
            #hp.dx-js .dx-parts.cut-8 .dx-rest-8 { display: block; }
        }
        @media (min-width: 1280px) and (max-height: 860px) {
            #hp.dx-js .dx-shot { --dx-z: 1.25; }
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 28rem); }
            #hp.dx-js .dx-parts { min-height: 6.9rem; }
        }
        @media (min-width: 1600px) and (max-height: 860px) {
            #hp.dx-js .dx-shot-view { max-height: calc(100vh - 26rem); }
            #hp.dx-js .dx-parts { min-height: 4.9rem; }
        }
        @media (min-width: 1024px) and (max-height: 620px) {
            {{-- A window too short for the picture and the contents keeps the contents. --}}
            #hp.dx-js .dx-shot-view { display: none; }
        }
        @keyframes dx-shot-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes dx-body-in {
            from { opacity: 0; transform: translateY(0.35rem); }
            to { opacity: 1; transform: none; }
        }

        {{-- ---------------------------------------------------------------
           The night: for the people who run the software, or write code against it. Dark in both
           modes, so its colours are literal.
           --------------------------------------------------------------- --}}
        .dx-doors { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        .dx-door {
            --dx-n: #7dd3fc;
            --dx-n-glow: rgba(56, 189, 248, 0.2);
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: clamp(1.25rem, 2.4vw, 1.9rem);
            border: 1px solid rgba(125, 165, 255, 0.22);
            border-radius: 1.6rem;
            background:
                radial-gradient(26rem 14rem at 88% 0%, var(--dx-n-glow), transparent 70%),
                #0b1124;
            color: #eef2ff;
        }
        .dx-door[data-dx-door="saas"] { --dx-n: #67e8f9; --dx-n-glow: rgba(34, 211, 238, 0.18); }
        .dx-door[data-dx-door="developer"] { --dx-n: #6ee7b7; --dx-n-glow: rgba(52, 211, 153, 0.16); }
        @media (min-width: 1024px) {
            .dx-doors { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); }
            {{-- The developer's shelf runs across the foot, its request beside its pages. --}}
            .dx-door[data-dx-door="developer"] {
                display: grid;
                grid-column: 1 / -1;
                grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
                grid-template-areas: "main side" "pages side";
                align-items: start;
                column-gap: clamp(1.5rem, 4vw, 4rem);
            }
            .dx-door[data-dx-door="developer"] .dx-door-main { grid-area: main; }
            .dx-door[data-dx-door="developer"] .dx-door-side { grid-area: side; align-self: center; margin-top: 0; }
            .dx-door[data-dx-door="developer"] .dx-door-pages { grid-area: pages; }
        }
        .dx-door-main,
        .dx-door-side,
        .dx-door-pages { min-width: 0; }
        .dx-door-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        #hp .dx-door-top .dx-mono { color: #9fb1d6; }
        #hp .dx-door-top .dx-mono:first-child { color: var(--dx-n); }
        #hp .dx-door h3 { display: flex; align-items: center; gap: 0.8rem; margin-top: 1.1rem; font-size: 1.7rem; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.035em; line-height: 1.1; }
        #hp .dx-door .dx-tile { background: color-mix(in srgb, var(--dx-n) 18%, transparent); color: var(--dx-n); }
        .dx-door-lede { margin-top: 0.75rem; font-size: 1rem; line-height: 1.55; color: #c5cde2; }
        .dx-door-side { margin-top: 1.2rem; }
        #hp .dx-door .doc-code-block { margin: 0; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1rem; background: #050814; }
        {{-- A line too long for its shelf wraps (a comment in the narrow shelf of a laptop). On
           a phone a request broken in the middle of a word is worse than one that scrolls, so
           there it scrolls, and fades at the edge it runs past. --}}
        #hp .dx-door .doc-code-block pre { font-size: 0.88rem; line-height: 1.75; }
        @media (min-width: 640px) {
            #hp .dx-door .doc-code-block pre,
            #hp .dx-door .doc-code-block code { white-space: pre-wrap; overflow-wrap: anywhere; }
        }
        @media (max-width: 639.98px) {
            #hp .dx-door .doc-code-block pre { overflow-x: auto; -webkit-mask-image: linear-gradient(to left, transparent, #000 2.5rem); mask-image: linear-gradient(to left, transparent, #000 2.5rem); }
        }
        .dx-door-line { margin-top: 0.7rem; font-size: 0.9rem; line-height: 1.5; color: #9fb1d6; }
        .dx-door-line a { color: var(--dx-n); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.22em; white-space: nowrap; }
        .dx-door-list { margin-top: 1.2rem; padding-top: 0.5rem; border-top: 1px solid rgba(255, 255, 255, 0.11); column-gap: 1.75rem; }
        .dx-door-list.is-two { column-count: 2; }
        .dx-door-list li { break-inside: avoid; }
        .dx-door-list a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            min-height: 2.35rem;
            padding: 0.3rem 0;
            font-size: 1rem;
            font-weight: 400;
            font-variation-settings: 'wght' 540;
            line-height: 1.3;
            color: #e3e9fb;
            transition: color 0.15s ease;
        }
        .dx-door-list a svg { flex: none; width: 0.95rem; height: 0.95rem; opacity: 0.45; transition: opacity 0.15s ease, transform 0.2s ease; }
        .dx-door-list a:hover { color: var(--dx-n); }
        .dx-door-list a:hover svg { opacity: 1; transform: translateX(2px); }
        @media (max-width: 479.98px) {
            .dx-door-list.is-two a { font-size: 0.95rem; }
            .dx-door-list.is-two a svg { display: none; }
        }
        .dx-door-ref { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; margin-top: 1rem; }
        #hp .dx-door-ref .dx-mono { flex-basis: 100%; margin-bottom: 0.15rem; color: #9fb1d6; }
        .dx-door-ref a {
            display: inline-flex;
            align-items: center;
            min-height: 2rem;
            padding: 0.2rem 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            font-size: 0.9rem;
            font-variation-settings: 'wght' 560;
            color: #e3e9fb;
            transition: border-color 0.15s ease, color 0.15s ease;
        }
        .dx-door-ref a:hover { border-color: var(--dx-n); color: var(--dx-n); }

        {{-- ---------------------------------------------------------------
           The glossary: three families of words, a panel each in an act's colour. A word shows
           its first sentence and opens on the rest and on where the guide takes it further.
           --------------------------------------------------------------- --}}
        .dx-dict { display: grid; grid-template-columns: minmax(0, 1fr); align-items: start; gap: 1rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 960px) {
            .dx-dict { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .dx-family {
            padding: 1.25rem clamp(1.1rem, 1.8vw, 1.6rem) 0.5rem;
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background:
                radial-gradient(22rem 11rem at 88% 0%, color-mix(in srgb, var(--dx-a) 16%, transparent), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
        }
        #hp .dx-family-head { display: flex; align-items: center; gap: 0.8rem; padding-bottom: 1.1rem; font-size: 1.2rem; font-weight: 700; font-variation-settings: 'wght' 790; letter-spacing: -0.03em; line-height: 1.2; color: var(--hp-ink); }
        #hp .dx-family-head .dx-mono { margin-inline-start: auto; white-space: nowrap; }
        .dx-word { border-top: 1px solid var(--hp-line); scroll-margin-top: 6rem; }
        .dx-word summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.25rem 1rem;
            padding: 1.05rem 0 1.1rem;
            list-style: none;
            cursor: pointer;
        }
        .dx-word summary::-webkit-details-marker { display: none; }
        #hp .dx-word-no { grid-column: 1; color: var(--dx-a); }
        #hp .dx-word-name { grid-column: 1; font-size: clamp(1.4rem, 0.7vw + 1.15rem, 1.75rem); font-style: normal; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.035em; line-height: 1.1; color: var(--hp-ink); transition: color 0.2s ease; }
        .dx-word-first { grid-column: 1 / -1; margin-top: 0.3rem; font-size: 1rem; line-height: 1.55; color: var(--hp-ink-2); }
        .dx-word summary i { position: relative; grid-row: 1 / span 2; grid-column: 2; align-self: center; width: 2rem; height: 2rem; border: 1px solid var(--hp-line-2); border-radius: 999px; transition: transform 0.3s ease, background-color 0.2s ease, border-color 0.2s ease; }
        .dx-word summary i::before,
        .dx-word summary i::after { content: ""; position: absolute; top: 50%; left: 50%; width: 0.7rem; height: 2px; margin: -1px 0 0 -0.35rem; border-radius: 2px; background: currentColor; }
        .dx-word summary i::after { transform: rotate(90deg); transition: transform 0.3s ease; }
        .dx-word summary:hover .dx-word-name { color: var(--dx-a); }
        .dx-word[open] summary i { background: var(--dx-a); border-color: var(--dx-a); color: #fff; transform: rotate(180deg); }
        .dark .dx-word[open] summary i { color: #07102a; }
        .dx-word[open] summary i::after { transform: rotate(0deg); }
        .dx-word-more { padding: 0 0 1.3rem; }
        .dx-word-more p { font-size: 1rem; line-height: 1.6; color: var(--hp-ink-2); }
        .dx-word-more .hp-more { margin-top: 0.7rem; font-size: 0.95rem; }
        .dx-word.is-found .dx-word-name { animation: dx-found 1.4s ease; }
        @keyframes dx-found {
            0%, 60% { color: var(--dx-a); }
        }
        @media (max-width: 639.98px) {
            {{-- A phone shows the words; a word's sentence waits until it is opened. --}}
            .dx-word:not([open]) .dx-word-first { display: none; }
            .dx-word summary { padding-block: 0.85rem; }
        }

        {{-- ---------------------------------------------------------------
           The ways out for somebody whose answer was not here.
           --------------------------------------------------------------- --}}
        .dx-exits { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.75rem; margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        .dx-exit {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 1.15rem;
            border: 1px solid var(--hp-line);
            border-radius: 1.4rem;
            background: var(--hp-bg);
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .dx-exit { background: var(--hp-bg-2); }
        .dx-exit:hover { border-color: var(--hp-blue); box-shadow: var(--hp-card-shadow); transform: translateY(-3px); }
        .dx-exit-words { flex: 1 1 auto; min-width: 0; }
        #hp .dx-exit strong { display: block; font-size: 1.2rem; font-variation-settings: 'wght' 790; letter-spacing: -0.03em; line-height: 1.2; color: var(--hp-ink); }
        .dx-exit-text { display: block; margin-top: 0.3rem; line-height: 1.5; color: var(--hp-ink-2); }
        .dx-exit .hp-more { flex: none; }
        .dx-exit .hp-more span { display: none; }
        @media (min-width: 768px) {
            .dx-exits { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
            .dx-exit { flex-direction: column; align-items: flex-start; gap: 0; padding: 1.5rem; }
            #hp .dx-exit strong { margin-top: 1.1rem; font-size: 1.3rem; }
            .dx-exit-text { margin-top: 0.45rem; margin-bottom: 1.1rem; }
            .dx-exit .hp-more { margin-top: auto; }
            .dx-exit .hp-more span { display: inline; }
        }

        @media (max-width: 639.98px) {
            {{-- The last heading breaks before its second half instead of in the middle of it. --}}
            #hp .dx-break { display: block; }
        }

        @media (prefers-reduced-motion: reduce) {
            #hp .dx-goal,
            .dx-goal-box,
            .dx-goal-box svg,
            #hp .dx-copy,
            #hp .dx-name,
            .dx-name-go,
            .dx-step svg,
            .dx-exit,
            .dx-word summary i,
            .dx-word summary i::after,
            .dx-door-list a svg { transition: none; }
            .dx-step.is-new,
            .dx-word.is-found .dx-word-name,
            #hp.dx-js .dx-item.is-on .dx-panel,
            #hp.dx-js .dx-item.is-on .dx-shot img,
            #hp.dx-js .dx-item.is-on .dx-panel-body { animation: none; }
        }
    </style>

    <div class="doc-accent-guide dx">
        <x-docs.icon-sprite />

        {{-- ============================================================
             Hero: the headline, and the search. Nothing here waits for a reveal: the search is
             the page's first control and must work as soon as it is painted.

             The link-preview card (GenerateSocialImages) leaves out the search with the questions
             under it (the div that carries es-d-3) and the numbered way round (hp-toc).
             ============================================================ --}}
        <section id="top" class="es-hero hp-hero is-short dx-hero">
            <div class="hp-hero-sky" aria-hidden="true"></div>

            <div class="hp-hero-copy">
                <div class="es-fade-up es-d-1 hp-eyebrow">
                    <x-docs.icon name="book" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    Documentation
                </div>

                <h1 class="hp-h1">
                    <span class="es-mask"><span class="es-mask-line">The whole product,</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">written down</span></span></span>
                </h1>

                <p class="es-fade-up es-d-2 hp-sub">
                    {{ $pageCount }} guides, from a first schedule to an install of your own. Search them, get a reading list for what you are setting up, or look inside a guide before you open it.
                </p>

                <div class="es-fade-up es-d-3 dx-search">
                    <x-docs.search variant="hero" />

                    <p class="dx-ask">
                        <span class="dx-mono">People ask</span>
                        @foreach ($asked as [$question, $ref])
                            @php
                                $answer = DocsUtils::guide($ref);
                            @endphp
                            @if ($answer)
                                <a href="{{ $answer['url'] }}">{{ $question }}</a>
                            @endif
                        @endforeach
                    </p>
                </div>

                <nav class="es-fade-up es-d-4 hp-toc" aria-label="Page sections">
                    @foreach ([['Start here', '#start'], ['User Guide', '#guide'], ['Selfhost, SaaS and API', '#platforms'], ['Glossary', '#glossary']] as $tocIndex => [$label, $href])
                        <a href="{{ $href }}"><b>{{ sprintf('%02d', $tocIndex + 1) }}</b>{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
        </section>

        {{-- ============================================================
             Start here. A newcomer's question is "which of these do I read, and in what order",
             and the answer depends on what they came to do. Every step is printed by the server
             in the product's own order; ticking a goal shows the steps that belong to it.
             ============================================================ --}}
        <section id="start" class="hp-sec is-tight hp-alt">
            <div class="hp-wrap dx-start">
                <div class="dx-start-side">
                    <div class="hp-head" data-reveal>
                        <span class="hp-kicker">Start here</span>
                        <h2 class="hp-h2">From nothing to <span class="hp-ink-grad">a live calendar</span></h2>
                        <p class="hp-lead dx-if-js">Tick what you want to do. The list is the shortest way through the guide for exactly that, in the order you will need it.</p>
                        <p class="hp-lead dx-if-plain">The shortest way through the guide, in the order you will need it.</p>
                    </div>

                    <div class="dx-goals" role="group" aria-label="What you want to do">
                        @foreach ($goals as $goal)
                            <button type="button" class="dx-goal" data-dx-goal="{{ $goal['key'] }}" aria-pressed="false">
                                <span class="dx-goal-box" aria-hidden="true">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </span>
                                <span class="dx-goal-words">{{ $goal['label'] }}</span>
                                @isset($goal['tier'])
                                    <span class="dx-tier" data-tier="{{ $goal['tier'] }}">{{ $tierNames[$goal['tier']] }}</span>
                                @endisset
                            </button>
                        @endforeach
                    </div>
                    <p class="dx-goals-note">A tag names the plan a goal needs. No tag, no cost.</p>

                    {{-- On a phone the list is below the goals, so a tick would change nothing in
                         view. This says what the tick did and goes there. --}}
                    <a class="dx-seen" href="#reading-list" data-dx-seen data-no-smooth hidden>
                        <span data-dx-seen-count></span>
                        <span class="dx-seen-go">
                            See the list
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" /></svg>
                        </span>
                    </a>
                </div>

                {{-- The id is on a wrapper that never moves: the panel inside rises into place, and
                     a jump measured against it would land short. --}}
                <div id="reading-list" class="dx-path-anchor">
                <div class="dx-path" data-reveal="panel">
                    <div class="dx-path-top">
                        <span class="dx-mono dx-if-js">Your reading list</span>
                        <span class="dx-mono dx-if-plain">Everything, in order</span>
                        <span class="dx-path-tools">
                            <span class="dx-mono" data-dx-count aria-live="polite">{{ count($steps) }} steps</span>
                            <button type="button" class="dx-copy dx-if-js" data-dx-copy>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                                <span data-dx-copy-label aria-live="polite">Copy the link</span>
                            </button>
                        </span>
                    </div>

                    <ol class="dx-steps">
                        @foreach ($steps as $step)
                            @php
                                $to = DocsUtils::guide($step['to']);
                                $part = $to ? $partName($to['key'], $to['anchor']) : null;
                            @endphp
                            @if ($to)
                                <li class="dx-step {{ $step['goal'] === 'base' ? 'is-on' : '' }}" data-dx-step="{{ $step['goal'] }}" data-dx-guide="{{ $to['key'] }}">
                                    <a href="{{ $to['url'] }}">
                                        <span>
                                            <span class="dx-step-title">{{ $step['title'] }}</span>
                                            <span class="dx-step-where">
                                                {{ DocsUtils::navTitle(DocsUtils::page($to['key'])) }}@if ($part), {{ $part }}@endif
                                                @isset($goalNames[$step['goal']])
                                                    <span class="dx-step-for">{{ $goalNames[$step['goal']] }}</span>
                                                @endisset
                                            </span>
                                        </span>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrow }}" /></svg>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                        <li class="dx-step-ghost dx-if-js" data-dx-ghost>Tick something and its steps join the list, in their place.</li>
                    </ol>

                    <p class="dx-path-note dx-if-js">Your ticks ride in the link, so the link is the list: copy it to keep it, or to send it.</p>
                </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             The User Guide: every guide by name, under the five acts config/docs.php sorts them
             into, and beside the names a stage that shows the guide being pointed at. What the
             stage lists is that guide's own contents, and its pictures are the guide's own
             screenshots: one the guide opens on (the manifest's `shot`), then one for each
             section that prints a screenshot of its own. DocsContents reads both out of the
             guide's view, so a guide that gains a section or a picture changes here by itself.
             ============================================================ --}}
        @php
            $guideCount = array_sum(array_map(fn ($cluster) => count($cluster['pages']), $clusters));
            $shotFrames = config('docs.shot_frames', []);
            $emptyShots = config('docs.empty_shots', []);
            $pictureMark = 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25z';
        @endphp
        <section id="guide" class="hp-sec">
            <div class="hp-wrap">
                <div class="dx-guide-head">
                    <div class="hp-head" data-reveal>
                        <span class="hp-kicker">User Guide</span>
                        <h2 class="hp-h2">Every part of the product, <span class="hp-ink-grad">act by act</span></h2>
                        <p class="hp-lead dx-if-js dx-lead-point">These follow the app screen by screen. Point at one to put it on the stage: its screens, and its own contents, every line a way straight to that section.</p>
                        <p class="hp-lead dx-if-js dx-lead-tap">These follow the app screen by screen. Tap one to see its screens and its contents, every line a way straight to that section.</p>
                        <p class="hp-lead dx-if-plain">{{ $guideCount }} guides that follow the app screen by screen, and what each one holds. Every line goes straight to that section.</p>
                    </div>

                    <p class="dx-help" data-reveal>
                        <span class="dx-help-key" aria-hidden="true">?</span>
                        <span><strong>Already in the app?</strong> The Help button at the foot of the sidebar opens the section for the screen you are on, down to the tab.</span>
                    </p>
                </div>

                <div class="dx-bill" data-dx-bill data-dx-shots="{{ url('images/docs') }}/" data-dx-frames="{{ implode(' ', array_map(fn ($shot, $frame) => $shot.':'.$frame, array_keys($shotFrames), $shotFrames)) }}" style="--dx-rows: {{ count($clusters) + $guideCount }};">
                    @foreach ($clusters as $cluster)
                        <div class="dx-act" data-dx-accent="{{ $cluster['accent'] }}">
                            <div class="dx-act-head">
                                <div class="dx-act-row">
                                    <span class="dx-mono dx-act-no">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <h3 class="dx-act-name">{{ $cluster['title'] }}</h3>
                                </div>
                                <p class="dx-act-blurb">{{ $cluster['blurb'] }}</p>
                            </div>

                            @foreach ($cluster['pages'] as $page)
                                @php
                                    $guideUrl = route($page['route']);
                                    $panelId = 'dx-guide-'.str_replace('/', '-', $page['key']);
                                    $parts = DocsContents::for($page['key']);
                                    // A section's own picture: not an empty screen, and not the one
                                    // the guide already opens on.
                                    $sectionShots = array_diff(DocsContents::shots($page['key']), $emptyShots, [$page['shot'] ?? '']);
                                    // The guide's screens, in order: the one it opens on, then each
                                    // section's, once each.
                                    $screens = isset($page['shot']) ? [['shot' => $page['shot'], 'words' => $page['shot_alt'] ?? $page['title']]] : [];
                                    foreach ($parts as $part) {
                                        $partShot = $sectionShots[$part['anchor']] ?? null;
                                        if ($partShot && ! in_array($partShot, array_column($screens, 'shot'), true)) {
                                            $screens[] = ['shot' => $partShot, 'words' => $part['label']];
                                        }
                                    }
                                    $feature = DocsUtils::featureFor($page['key']);
                                    $first = $loop->parent->first && $loop->first;
                                @endphp
                                <div class="dx-item {{ $first ? 'is-on' : '' }}" data-dx-item>
                                    <h4 class="dx-g">
                                        <a class="dx-name" href="{{ $guideUrl }}" data-dx-name data-dx-panel="{{ $panelId }}">
                                            <span class="dx-tile"><x-docs.icon :name="$page['icon']" /></span>
                                            <span>{{ DocsUtils::navTitle($page) }}</span>
                                            <svg class="dx-name-go" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                    </h4>

                                    <div class="dx-panel" id="{{ $panelId }}">
                                        @isset($page['shot'])
                                            <div class="dx-shot" data-dx-shot="{{ $page['shot'] }}" data-dx-frame="{{ $shotFrames[$page['shot']] ?? 'app' }}">
                                                <div class="dx-shot-view" @if (count($screens) > 1) data-dx-next @endif>
                                                @foreach (['' => 'dx-shot-light', '-dark' => 'dx-shot-dark'] as $shotSuffix => $shotTheme)
                                                    <picture class="{{ $shotTheme }}">
                                                        <source srcset="{{ url('images/docs/'.$page['shot'].$shotSuffix.'.webp') }}" type="image/webp">
                                                        <img src="{{ url('images/docs/'.$page['shot'].$shotSuffix.'.png') }}" alt="{{ $page['title'] }}: {{ $page['shot_alt'] ?? 'a screen from the app' }}" width="1280" height="757" loading="lazy" decoding="async">
                                                    </picture>
                                                @endforeach
                                                </div>

                                                {{-- The guide's other screens: a dot each, on a strip under the picture they change. --}}
                                                @if (count($screens) > 1)
                                                    <div class="dx-shot-bar dx-if-js">
                                                        <span class="dx-shot-cap" data-dx-cap aria-live="polite">{{ $screens[0]['words'] }}</span>
                                                        <span class="dx-pips" role="group" aria-label="Screens of {{ $page['title'] }}">
                                                            @foreach ($screens as $screen)
                                                                <button type="button" class="dx-pip" data-dx-pip="{{ $screen['shot'] }}" data-dx-words="{{ $screen['words'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-label="Show: {{ $screen['words'] }}"></button>
                                                            @endforeach
                                                        </span>
                                                        <span class="dx-shot-of" aria-hidden="true"><span data-dx-of>1</span> of {{ count($screens) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endisset

                                        <div class="dx-panel-body">
                                            <div class="dx-panel-top">
                                                <span class="dx-tile"><x-docs.icon :name="$page['icon']" /></span>
                                                <div class="dx-panel-title">
                                                    <p class="dx-panel-name">{{ $page['title'] }}</p>
                                                    <p class="dx-panel-blurb">{{ $page['blurb'] }}</p>
                                                </div>
                                                <a class="hp-btn hp-btn-primary is-small" href="{{ $guideUrl }}">
                                                    Read the guide
                                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                                                </a>
                                            </div>

                                            @if (count($parts))
                                                {{-- A frame with less room shows the first 7, 8 or 11 lines and says how
                                                     many are left. It never does so for one line: a list is cut only
                                                     when at least two would go. --}}
                                                <ul class="dx-parts {{ count($parts) >= 9 ? 'cut-7' : '' }} {{ count($parts) >= 10 ? 'cut-8' : '' }} {{ count($parts) >= 13 ? 'cut-11' : '' }}">
                                                    @foreach ($parts as $part)
                                                        @php
                                                            $partShot = $sectionShots[$part['anchor']] ?? null;
                                                        @endphp
                                                        <li>
                                                            <a href="{{ $guideUrl }}#{{ $part['anchor'] }}" @if ($partShot) data-dx-pic="{{ $partShot }}" @endif>
                                                                <span>{{ $part['label'] }}</span>
                                                                @if ($partShot)
                                                                    <svg class="dx-parts-pic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pictureMark }}" /></svg>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                    @foreach ([7, 8, 11] as $kept)
                                                        @if (count($parts) >= $kept + 2)
                                                            <li class="dx-parts-more dx-rest-{{ $kept }}"><a href="{{ $guideUrl }}"><span>and {{ count($parts) - $kept }} more in the guide</span></a></li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <div class="dx-panel-foot">
                                                <span class="dx-mono">{{ count($parts) }} sections</span>
                                                @if ($feature)
                                                    <a class="hp-more" href="{{ $feature['url'] }}">
                                                        Feature overview
                                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================================================
             The night: the three shelves for people who run the software or write code against
             it. Each opens on one line from its guide, as that page prints it, and
             lists every page of its group (config/docs.php), so none is more than one press away.
             ============================================================ --}}
        <section id="platforms" class="hp-dark">
            <div class="hp-wrap">
                <div class="hp-head" data-reveal>
                    <span class="hp-kicker">Run it yourself</span>
                    <h2 class="hp-h2">Host it, <span class="dx-keep">resell it,</span> <span class="hp-ink-grad">or script it</span></h2>
                    <p class="hp-lead">Three more shelves, for the people who run the software and the people who write code against it. Each opens on a line from its own guide.</p>
                </div>

                <div class="dx-doors" data-reveal-group="90">
                    @foreach ($doors as $door)
                        @php
                            $group = DocsUtils::group($door['key']);
                            $groupPages = DocsUtils::pagesInGroup($door['key']);
                            $hubUrl = route($group['index_route']);
                            $line = DocsUtils::guide($door['line']);
                        @endphp
                        <article class="dx-door" data-dx-door="{{ $door['key'] }}" data-reveal>
                            <div class="dx-door-main">
                                <div class="dx-door-top">
                                    <span class="dx-mono">{{ $door['tag'] }}</span>
                                    <span class="dx-mono">{{ count($groupPages) }} pages</span>
                                </div>
                                <h3><span class="dx-tile"><x-docs.icon :name="$group['icon']" /></span>{{ $door['title'] }}</h3>
                                <p class="dx-door-lede">{{ $door['lede'] }}</p>

                            </div>

                            {{-- The line is the guide's own, copied here as that page prints it. --}}
                            <div class="dx-door-side">
                                <div class="doc-code-block">
                                    <div class="doc-code-header"><span>{{ $door['file'] }}</span><button type="button" class="doc-copy-btn">Copy</button></div>
                                    @if ($door['key'] === 'selfhost')
                                        <pre><code>* * * * * php /path/to/eventschedule/artisan schedule:run</code></pre>
                                    @elseif ($door['key'] === 'saas')
                                        <pre><code><span class="code-comment"># Enable SaaS mode with subdomain routing</span>
<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span></code></pre>
                                    @else
                                        <pre><code><span class="code-keyword">curl</span> -X GET <span class="code-string">"{{ config('app.url') }}/api/schedules"</span> \
     -H <span class="code-string">"X-API-Key: your_api_key_here"</span> \
     -H <span class="code-string">"Accept: application/json"</span></code></pre>
                                    @endif
                                </div>
                                @if ($line)
                                    <p class="dx-door-line">
                                        {{ $door['why'] }}
                                        <a href="{{ $line['url'] }}">{{ $door['where'] }}</a>
                                    </p>
                                @endif
                            </div>

                            <div class="dx-door-pages">
                                <ul class="dx-door-list {{ count($groupPages) > 6 ? 'is-two' : '' }}">
                                    @foreach ($groupPages as $listedPage)
                                        <li>
                                            <a href="{{ route($listedPage['route']) }}">
                                                {{ DocsUtils::navTitle($listedPage) }}
                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrow }}" /></svg>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($door['key'] === 'developer')
                                    <div class="dx-door-ref">
                                        <span class="dx-mono">In the reference</span>
                                        @foreach (DocsContents::for('developer/api') as $refPart)
                                            <a href="{{ $hubUrl }}#{{ $refPart['anchor'] }}">{{ $refPart['label'] }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================================================
             The glossary. The terms and their definitions are the ones the DefinedTermSet block
             in the head carries, word for word: a word shows its first sentence, and opens on
             the rest and on where the guide explains it at length.
             ============================================================ --}}
        <section id="glossary" class="hp-sec">
            <div class="hp-wrap">
                <div class="hp-head" data-reveal>
                    <span class="hp-kicker">Glossary</span>
                    <h2 class="hp-h2">{{ $numberWords[count($glossary)] ?? count($glossary) }} words <span class="hp-ink-grad">you will meet</span></h2>
                    <p class="hp-lead">The terms the app uses, in three families. Open one for the fine print.</p>
                </div>

                <div class="dx-dict" data-reveal-group="90">
                    @php
                        $wordNumber = 0;
                    @endphp
                    @foreach ($families as $familyKey => $family)
                        @php
                            $words = array_values(array_filter($glossary, fn ($g) => $g['family'] === $familyKey));
                        @endphp
                        <div class="dx-family" data-dx-accent="{{ $family['accent'] }}" data-reveal>
                            <h3 class="dx-family-head">
                                <span class="dx-tile"><x-docs.icon :name="$family['icon']" /></span>
                                <span>{{ $family['name'] }}</span>
                                <span class="dx-mono">{{ count($words) }} words</span>
                            </h3>

                            @foreach ($words as $g)
                                @php
                                    $wordNumber++;
                                    $where = DocsUtils::guide($g['guide']);
                                    $wherePart = $where && $where['anchor'] !== '' ? $partName($where['key'], $where['anchor']) : null;
                                    // The first sentence is the word at a glance; the rest waits behind it.
                                    [$firstSentence, $restOfIt] = array_pad(preg_split('/(?<=\.)\s+(?=[A-Z])/', $g['def'], 2), 2, '');
                                    // A definition that sends the reader to another word links to it.
                                    $restHtml = e($restOfIt);
                                    foreach ($glossary as $other) {
                                        $restHtml = str_replace(
                                            e('(see '.$other['term'].')'),
                                            '(see <a class="hp-inline" href="#term-'.\Illuminate\Support\Str::slug($other['term']).'" data-dx-word-link data-no-smooth>'.e($other['term']).'</a>)',
                                            $restHtml
                                        );
                                    }
                                @endphp
                                <details class="dx-word" id="term-{{ \Illuminate\Support\Str::slug($g['term']) }}" name="dx-word">
                                    <summary>
                                        <span class="dx-mono dx-word-no">{{ sprintf('%02d', $wordNumber) }}</span>
                                        <dfn class="dx-word-name">{{ $g['term'] }}</dfn>
                                        <span class="dx-word-first">{{ $firstSentence }}</span>
                                        <i aria-hidden="true"></i>
                                    </summary>
                                    <div class="dx-word-more">
                                        @if ($restOfIt !== '')
                                            <p>{!! $restHtml !!}</p>
                                        @endif
                                        @if ($where)
                                            <a class="hp-more" href="{{ $where['url'] }}">
                                                {{ DocsUtils::navTitle(DocsUtils::page($where['key'])) }}@if ($wherePart), {{ $wherePart }}@endif
                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============================================================
             Not here? The three places an answer comes from when the guide does not have it.
             ============================================================ --}}
        <section id="help" class="hp-sec is-tight hp-alt">
            <div class="hp-wrap">
                <div class="hp-head" data-reveal>
                    <span class="hp-kicker">Still stuck</span>
                    <h2 class="hp-h3">Not in here? Three more places to look.</h2>
                </div>

                {{-- The row is revealed as one: a reveal on a card itself would leave it a
                     transform that its own lift on hover could never override. --}}
                <div class="dx-exits" data-reveal>
                    <a class="dx-exit" href="{{ route('marketing.faq') }}">
                        <span class="dx-tile">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <span class="dx-exit-words">
                            <strong>Frequently asked questions</strong>
                            <span class="dx-exit-text">Quick answers about plans, payments and getting started.</span>
                        </span>
                        <span class="hp-more">
                            <span>Read the FAQ</span>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                        </span>
                    </a>

                    <a class="dx-exit" href="{{ route('marketing.contact') }}">
                        <span class="dx-tile">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                        </span>
                        <span class="dx-exit-words">
                            <strong>Ask us</strong>
                            <span class="dx-exit-text">Write to the people who build Event Schedule.</span>
                        </span>
                        <span class="hp-more">
                            <span>Contact us</span>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                        </span>
                    </a>

                    <a class="dx-exit" href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer">
                        <span class="dx-tile">
                            <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" /></svg>
                        </span>
                        <span class="dx-exit-words">
                            <strong>Open source on GitHub</strong>
                            <span class="dx-exit-text">Read the code, or open an issue to tell us what broke.</span>
                        </span>
                        <span class="hp-more">
                            <span>Open GitHub</span>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrow }}" /></svg>
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <x-marketing.hp-finale lead="Create your free schedule in seconds. No credit card required." placeholder="your-schedule" :foot="false">
        Read enough? <span class="hp-ink-grad dx-break">Claim your schedule</span>
    </x-marketing.hp-finale>

    {{-- The page's own behaviour: the reading list, the stage and the glossary's cross-links. All
         of it only chooses which of the things the server printed are shown; none of it builds
         markup, so nothing here can leave a reveal waiting or a link missing. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var hp = document.getElementById('hp');
            if (!hp) {
                return;
            }

            {{-- ---- The search, on a phone: the field's own sentence does not fit ---- --}}
            var field = hp.querySelector('.dx-search [data-role="input"]');
            if (field && window.matchMedia('(max-width: 479.98px)').matches) {
                field.setAttribute('placeholder', 'Search the guide');
            }

            {{-- ---- The reading list ---- --}}
            var goals = [].slice.call(hp.querySelectorAll('[data-dx-goal]'));
            var steps = [].slice.call(hp.querySelectorAll('[data-dx-step]'));
            var count = hp.querySelector('[data-dx-count]');
            var ghost = hp.querySelector('[data-dx-ghost]');
            var seen = hp.querySelector('[data-dx-seen]');
            var seenCount = hp.querySelector('[data-dx-seen-count]');
            var copy = hp.querySelector('[data-dx-copy]');
            var copyLabel = hp.querySelector('[data-dx-copy-label]');
            var copyWords = copyLabel ? copyLabel.textContent : '';
            var copyTimer = 0;

            function picked() {
                return goals.filter(function (goal) {
                    return goal.getAttribute('aria-pressed') === 'true';
                }).map(function (goal) {
                    return goal.getAttribute('data-dx-goal');
                });
            }

            function drawPath(arriving) {
                var on = picked();
                var shown = 0;
                var guides = {};

                steps.forEach(function (step) {
                    var goal = step.getAttribute('data-dx-step');
                    var show = goal === 'base' || on.indexOf(goal) !== -1;
                    var was = step.classList.contains('is-on');

                    step.classList.toggle('is-on', show);
                    step.classList.toggle('is-new', !!arriving && show && !was);

                    if (show) {
                        shown++;
                        guides[step.getAttribute('data-dx-guide')] = true;
                    }
                });

                var words = shown + ' steps in ' + Object.keys(guides).length + ' guides';

                if (count) {
                    count.textContent = words;
                }
                if (ghost) {
                    ghost.hidden = on.length > 0;
                }
                if (seen && seenCount) {
                    seenCount.textContent = words;
                    seen.hidden = on.length === 0;
                }
            }

            {{-- The ticks are kept in the address (#path-tickets-website) and nowhere else, so
                 Back returns to the same list and the address is the list. --}}
            function pathAddress() {
                var on = picked();

                return window.location.pathname + window.location.search + (on.length ? '#path-' + on.join('-') : '');
            }

            function writeAddress() {
                try {
                    history.replaceState(null, '', pathAddress());
                } catch (e) {}
            }

            {{-- Moving inside the page without writing its address: the shared docs script turns
                 every in-page link into an address of its own, which would drop the ticks. --}}
            function goTo(element) {
                if (element) {
                    window.scrollTo({ top: element.getBoundingClientRect().top + window.pageYOffset - 84, behavior: 'smooth' });
                }
            }

            function readAddress() {
                var found = /^#path-([a-z-]+)$/.exec(window.location.hash);

                if (!found) {
                    return false;
                }

                var asked = found[1].split('-');
                var any = false;

                goals.forEach(function (goal) {
                    var on = asked.indexOf(goal.getAttribute('data-dx-goal')) !== -1;

                    goal.setAttribute('aria-pressed', on ? 'true' : 'false');
                    any = any || on;
                });

                return any;
            }

            goals.forEach(function (goal) {
                goal.addEventListener('click', function () {
                    goal.setAttribute('aria-pressed', goal.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
                    drawPath(true);
                    writeAddress();
                });
            });

            if (copy && copyLabel) {
                copy.addEventListener('click', function () {
                    var say = function (words) {
                        copyLabel.textContent = words;
                        clearTimeout(copyTimer);
                        copyTimer = setTimeout(function () {
                            copyLabel.textContent = copyWords;
                        }, 2600);
                    };
                    var failed = function () {
                        say('Copy it from the address bar');
                    };

                    try {
                        navigator.clipboard.writeText(window.location.origin + pathAddress()).then(function () {
                            say('Link copied');
                        }, failed);
                    } catch (e) {
                        failed();
                    }
                });
            }

            if (seen) {
                seen.addEventListener('click', function (event) {
                    event.preventDefault();
                    goTo(document.getElementById('reading-list'));
                });
            }

            if (readAddress()) {
                var arrival = (window.performance && performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || {};
                var list = document.getElementById(window.matchMedia('(min-width: 1024px)').matches ? 'start' : 'reading-list');

                {{-- Somebody opening a list they were sent starts at the list. Back and reload
                     are left to the browser, which puts the page where it was. --}}
                if (list && arrival.type === 'navigate') {
                    list.scrollIntoView({ behavior: 'instant' });
                }
            }
            drawPath(false);

            {{-- A list opened in a tab that is already on this page. --}}
            window.addEventListener('hashchange', function () {
                if (readAddress()) {
                    drawPath(false);
                }
            });

            {{-- ---- The glossary: a word that names another word opens it ---- --}}
            function openWord(word) {
                if (word && word.classList.contains('dx-word')) {
                    word.open = true;
                    word.classList.remove('is-found');
                    void word.offsetWidth;
                    word.classList.add('is-found');

                    return true;
                }

                return false;
            }

            hp.addEventListener('click', function (event) {
                var link = event.target.closest('[data-dx-word-link]');
                var word = link ? document.getElementById(link.getAttribute('href').slice(1)) : null;

                if (openWord(word)) {
                    event.preventDefault();
                    goTo(word);
                }
            });

            {{-- An address that names a word (#term-ticket) arrives on that word open. --}}
            var named = function () {
                var id = window.location.hash.slice(1);

                if (/^term-[a-z0-9-]+$/.test(id)) {
                    openWord(document.getElementById(id));
                }
            };
            named();
            window.addEventListener('hashchange', named);

            {{-- ---- The line-up and the stage ---- --}}
            var bill = hp.querySelector('[data-dx-bill]');

            if (!bill) {
                return;
            }

            var items = [].slice.call(bill.querySelectorAll('[data-dx-item]'));
            var names = items.map(function (item) {
                return item.querySelector('[data-dx-name]');
            });
            var stage = window.matchMedia('(min-width: 1024px)');
            var pointing = window.matchMedia('(hover: hover) and (pointer: fine)');
            var shots = bill.getAttribute('data-dx-shots') || '';
            var frames = {};
            (bill.getAttribute('data-dx-frames') || '').split(' ').forEach(function (pair) {
                var at = pair.indexOf(':');

                if (at > 0) {
                    frames[pair.slice(0, at)] = pair.slice(at + 1);
                }
            });
            var current = bill.querySelector('[data-dx-item].is-on');
            var aimed = null;
            var aim = 0;
            var held = false;
            var queued = false;

            {{-- A name is a link to its guide. Below a laptop it opens its panel instead, so there
                 it is announced as the button it behaves as. --}}
            function announce() {
                names.forEach(function (name, i) {
                    if (stage.matches) {
                        name.removeAttribute('role');
                        name.removeAttribute('aria-expanded');
                        name.removeAttribute('aria-controls');
                    } else {
                        name.setAttribute('role', 'button');
                        name.setAttribute('aria-controls', name.getAttribute('data-dx-panel'));
                        name.setAttribute('aria-expanded', items[i] === current ? 'true' : 'false');
                    }
                });
            }

            {{-- The picture in a guide's frame: one of the guide's screens, chosen by its dot or
                 by pointing at its section's line. No id means the screen the guide opens on. The
                 picture is fetched first and swapped in when it has arrived, so the frame is
                 never blank; its dot, its caption and what it is said to show follow it. --}}
            function picture(item, id) {
                var frame = item ? item.querySelector('[data-dx-shot]') : null;

                if (!frame) {
                    return;
                }

                var pips = [].slice.call(frame.querySelectorAll('[data-dx-pip]'));
                var home = frame.getAttribute('data-dx-shot');

                id = id || home;

                if ((frame.dxNow || home) === id) {
                    return;
                }

                var pip = pips.filter(function (one) {
                    return one.getAttribute('data-dx-pip') === id;
                })[0];
                var words = pip ? pip.getAttribute('data-dx-words') : '';
                var cap = frame.querySelector('[data-dx-cap]');
                var of = frame.querySelector('[data-dx-of]');
                var asked = frame.dxAsked = (frame.dxAsked || 0) + 1;
                var dark = document.documentElement.classList.contains('dark');

                frame.dxNow = id;

                pips.forEach(function (one, i) {
                    one.setAttribute('aria-pressed', one === pip ? 'true' : 'false');
                    if (one === pip && of) {
                        of.textContent = i + 1;
                    }
                });
                [].forEach.call(item.querySelectorAll('[data-dx-pic]'), function (line) {
                    line.classList.toggle('is-shown', line.getAttribute('data-dx-pic') === id);
                });
                if (cap) {
                    cap.textContent = words;
                }

                var put = function () {
                    if (asked !== frame.dxAsked) {
                        return;
                    }

                    [['', '.dx-shot-light'], ['-dark', '.dx-shot-dark']].forEach(function (light) {
                        var holder = frame.querySelector(light[1]);
                        var image = holder.querySelector('img');

                        if (!image.dxAlt) {
                            image.dxAlt = image.getAttribute('alt');
                        }

                        holder.querySelector('source').setAttribute('srcset', shots + id + light[0] + '.webp');
                        image.setAttribute('src', shots + id + light[0] + '.png');
                        image.setAttribute('alt', id === home ? image.dxAlt : image.dxAlt.split(':')[0] + ': ' + words);
                    });

                    frame.setAttribute('data-dx-frame', frames[id] || 'app');
                };

                var ahead = new Image();
                ahead.onload = put;
                ahead.onerror = put;
                ahead.src = shots + id + (dark ? '-dark' : '') + '.webp';
            }

            function show(item) {
                if (item === current) {
                    return;
                }
                if (current) {
                    current.classList.remove('is-on');
                    picture(current, null);   {{-- a guide comes back to the stage on its own first screen --}}
                }
                current = item;
                if (current) {
                    current.classList.add('is-on');
                }
                announce();
            }

            {{-- Pointing: a name takes the stage, and a marked line shows its own screen, once
                 the pointer has rested there for a moment, so crossing other names on the way to
                 the stage changes nothing. The screen stays until another is chosen. Only a real movement counts: a page scrolled under a
                 still pointer is the scroll's business. --}}
            bill.addEventListener('pointermove', function (event) {
                if (!stage.matches || event.pointerType === 'touch') {
                    return;
                }

                held = !!event.target.closest('.dx-panel');

                var name = event.target.closest('[data-dx-name]');
                var line = event.target.closest('[data-dx-pic]');
                var target = name ? name.closest('[data-dx-item]') : line;

                if (target === aimed) {
                    return;
                }

                clearTimeout(aim);
                aimed = target;

                if (name) {
                    aim = setTimeout(function () {
                        show(target);
                    }, 70);
                } else if (line) {
                    aim = setTimeout(function () {
                        picture(current, line.getAttribute('data-dx-pic'));
                    }, 90);
                }
            });
            bill.addEventListener('pointerleave', function () {
                clearTimeout(aim);
                aimed = null;
                held = false;
            });

            {{-- The keyboard: a name that is tabbed to takes the stage. A press is not a tab: on
                 a touch screen it would put the guide on the stage in the same breath as it
                 opened it. --}}
            bill.addEventListener('focusin', function (event) {
                var item = event.target.closest('[data-dx-item]');
                var keyboard = true;

                try {
                    keyboard = event.target.matches(':focus-visible');
                } catch (e) {}

                if (!stage.matches || !item || !keyboard) {
                    return;
                }

                show(item);

                if (event.target.hasAttribute('data-dx-pic')) {
                    picture(item, event.target.getAttribute('data-dx-pic'));
                }
            });

            function press(event, name) {
                var item = name.closest('[data-dx-item]');

                if (stage.matches) {
                    {{-- A touch screen wide enough for the stage: the first press shows the guide,
                         the second opens it. --}}
                    if (!pointing.matches && item !== current) {
                        event.preventDefault();
                        show(item);
                    }

                    return;
                }

                event.preventDefault();

                {{-- Closing the panel above moves this name up the screen; put it back. --}}
                var before = name.getBoundingClientRect().top;

                show(item === current ? null : item);
                window.scrollBy({ top: name.getBoundingClientRect().top - before, behavior: 'instant' });
            }

            bill.addEventListener('click', function (event) {
                var pip = event.target.closest('[data-dx-pip]');
                var name = event.target.closest('[data-dx-name]');

                if (pip) {
                    picture(pip.closest('[data-dx-item]'), pip.getAttribute('data-dx-pip'));

                    return;
                }

                {{-- Pressing the picture itself goes on to the guide's next screen. --}}
                var view = event.target.closest('[data-dx-next]');

                if (view) {
                    var dots = [].slice.call(view.parentNode.querySelectorAll('[data-dx-pip]'));
                    var at = dots.map(function (dot) {
                        return dot.getAttribute('aria-pressed');
                    }).indexOf('true');

                    if (dots.length) {
                        picture(view.closest('[data-dx-item]'), dots[(at + 1) % dots.length].getAttribute('data-dx-pip'));
                    }

                    return;
                }

                if (name && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey) {
                    press(event, name);
                }
            });
            bill.addEventListener('keydown', function (event) {
                var name = event.target.closest('[data-dx-name]');

                if (name && !stage.matches && (event.key === ' ' || event.key === 'Spacebar')) {
                    press(event, name);
                }
            });

            {{-- Scrolling: the guide whose name has reached reading height takes the stage, so a
                 visitor who only scrolls still sees every guide go by. It stands back while the
                 pointer is on the stage or the keyboard is in the list. --}}
            function byKeyboard() {
                var focused = document.activeElement;

                try {
                    return !!focused && bill.contains(focused) && focused.matches(':focus-visible');
                } catch (e) {
                    return bill.contains(focused);
                }
            }

            {{-- The scroll takes the stage from a name the pointer still rests on; that name must
                 answer again as soon as the pointer moves, so its aim is forgotten. --}}
            function scrolledTo(item) {
                if (item !== current) {
                    clearTimeout(aim);
                    aimed = null;
                    show(item);
                }
            }

            function follow() {
                queued = false;

                if (!stage.matches || held || byKeyboard()) {
                    return;
                }

                var line = Math.min(window.innerHeight * 0.42, 420);
                var box = bill.getBoundingClientRect();

                {{-- Above the line-up the first guide is on the stage, however the visitor got
                     there (a jump to the top leaves no scroll to follow). Below it, nothing moves. --}}
                if (box.top > line) {
                    scrolledTo(items[0]);

                    return;
                }
                if (box.bottom < line) {
                    return;
                }

                var pick = items[0];

                for (var i = 0; i < names.length; i++) {
                    if (names[i].getBoundingClientRect().top > line) {
                        break;
                    }
                    pick = items[i];
                }

                scrolledTo(pick);
            }
            window.addEventListener('scroll', function () {
                if (!queued) {
                    queued = true;
                    requestAnimationFrame(follow);
                }
            }, { passive: true });

            {{-- The pictures wait their turn (loading="lazy" inside a panel that is not shown is
                 not fetched). Once the line-up is near, the ones for the light in use are fetched
                 one after another, so the stage changes without a blank frame. --}}
            function warm() {
                if (!stage.matches || (navigator.connection && navigator.connection.saveData)) {
                    return;
                }

                var dark = document.documentElement.classList.contains('dark');
                var waiting = [].slice.call(bill.querySelectorAll((dark ? '.dx-shot-dark' : '.dx-shot-light') + ' img[loading="lazy"]'));
                var next = 0;

                (function fetchNext() {
                    if (next < waiting.length) {
                        waiting[next++].loading = 'eager';
                        setTimeout(fetchNext, 140);
                    }
                })();
            }

            if ('IntersectionObserver' in window) {
                var near = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) {
                        near.disconnect();
                        setTimeout(warm, 500);
                    }
                }, { rootMargin: '400px 0px' });

                near.observe(bill);
            }

            {{-- Beside the stage one guide is always on it. Under a laptop's width the names are
                 a list first, so they start closed. --}}
            var settle = function () {
                if (stage.matches && !current) {
                    show(items[0]);
                }
                announce();
            };

            if (!stage.matches) {
                show(null);
            }
            if (stage.addEventListener) {
                stage.addEventListener('change', settle);
            } else if (stage.addListener) {
                stage.addListener(settle);
            }
            announce();
        })();
    </script>

    {{-- The reveals above ([data-reveal]) are fired by this script; the page must never lose it. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
