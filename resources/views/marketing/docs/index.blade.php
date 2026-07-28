@php
    use App\Utils\DocsUtils;

    // Both the visible glossary and the DefinedTermSet JSON-LD below read this
    // one array. They used to be two hand-maintained copies of the same nine
    // terms.
    $glossary = [
        ['term' => 'Schedule', 'def' => 'Your event calendar with its own unique URL and branding. Each schedule can contain unlimited events.'],
        ['term' => 'Event', 'def' => 'A single occurrence with a date, time, location, and details. Events belong to a schedule.'],
        ['term' => 'Sub-schedule', 'def' => 'A category to organize events within your schedule (e.g., Live Music, Comedy, Workshops).'],
        ['term' => 'Ticket', 'def' => 'A purchasable item for event attendance. Events can have multiple ticket types (e.g., General, VIP).'],
        ['term' => 'Follower', 'def' => 'Someone who follows your schedule to receive updates about new events.'],
        ['term' => 'Newsletter', 'def' => 'An email campaign sent to your followers or ticket buyers to announce events, share updates, or promote your schedule.'],
        ['term' => 'Segment', 'def' => 'A defined audience group for sending newsletters (e.g., all followers, ticket buyers, or a custom list).'],
        ['term' => 'Embed', 'def' => 'A widget that displays your schedule on an external website using an iframe or JavaScript snippet.'],
        ['term' => 'Admin Panel', 'def' => 'The management interface where you configure your schedule, add events, and view analytics.'],
    ];

    // Full class strings - interpolated Tailwind colour classes do not
    // JIT-generate.
    $platforms = [
        [
            'eyebrow' => 'Self-managed',
            'route' => 'marketing.docs.selfhost',
            'icon' => 'server',
            'title' => 'Selfhost Installation',
            'lede' => 'Deploy on your own server. Complete control, full customization, no vendor lock-in.',
            'links' => ['Installation guide', 'Stripe and calendar sync', 'Email and AI setup'],
            'cta' => 'View selfhost docs',
            'chip' => 'bg-sky-100 dark:bg-sky-500/15',
            'iconColor' => 'text-sky-600 dark:text-sky-400',
            'hover' => 'hover:border-sky-500/50 dark:hover:border-sky-400/40',
            'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            'link' => 'text-sky-600 dark:text-sky-400',
            'glow' => 'bg-sky-500/12 dark:bg-sky-500/15',
        ],
        [
            'eyebrow' => 'Multi-tenant',
            'route' => 'marketing.docs.saas.setup',
            'icon' => 'cloud',
            'title' => 'SaaS Platform',
            'lede' => 'Run Event Schedule as a multi-tenant SaaS with subdomains, plans and custom domains.',
            'links' => ['Multi-tenant setup', 'Custom domains', 'Twilio verification'],
            'cta' => 'View SaaS docs',
            'chip' => 'bg-cyan-100 dark:bg-cyan-500/15',
            'iconColor' => 'text-cyan-600 dark:text-cyan-400',
            'hover' => 'hover:border-cyan-500/50 dark:hover:border-cyan-400/40',
            'badge' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
            'link' => 'text-cyan-600 dark:text-cyan-400',
            'glow' => 'bg-cyan-500/12 dark:bg-cyan-500/15',
        ],
        [
            'eyebrow' => 'REST API',
            'route' => 'marketing.docs.developer.api',
            'icon' => 'code',
            'title' => 'Developer',
            'lede' => 'REST and webhook documentation for integrating with Event Schedule programmatically.',
            'links' => ['REST API reference', 'Webhooks', 'HMAC signing'],
            'cta' => 'Explore the API',
            'chip' => 'bg-emerald-100 dark:bg-emerald-500/15',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'hover' => 'hover:border-emerald-500/50 dark:hover:border-emerald-400/40',
            'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'link' => 'text-emerald-600 dark:text-emerald-400',
            'glow' => 'bg-emerald-500/12 dark:bg-emerald-500/15',
        ],
    ];

    $steps = [
        ['icon' => 'cog', 'title' => 'Create a schedule', 'text' => 'Pick a type, claim a URL, and add your logo and colours.', 'route' => 'marketing.docs.getting_started'],
        ['icon' => 'plus', 'title' => 'Add your events', 'text' => 'Enter them by hand, import from text, or scan a printed agenda.', 'route' => 'marketing.docs.creating_events'],
        ['icon' => 'share', 'title' => 'Share it', 'text' => 'Embed it on your site, post the link, and grow your followers.', 'route' => 'marketing.docs.sharing'],
    ];
@endphp

<x-marketing-layout :docs="true">
    <x-slot name="title">Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Documentation</x-slot>
    <x-slot name="description">Complete documentation for Event Schedule. User guides, selfhost installation, and developer resources.</x-slot>

    <x-slot name="structuredData">
        {{-- Every other doc page gets its TechArticle from <x-docs-page>. This
             page renders the layout directly, so it emits its own. The date is
             derived from the manifest rather than hardcoded - the version this
             replaced shipped a dateModified of 2026-02-01 that had gone stale. --}}
        <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'TechArticle',
                'headline' => 'Event Schedule Documentation',
                'description' => 'Complete documentation for Event Schedule. User guides, selfhost installation, and developer resources.',
                'author' => ['@type' => 'Organization', 'name' => 'Event Schedule'],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Event Schedule',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => config('app.url').'/images/light_logo.png',
                        'width' => 712,
                        'height' => 140,
                    ],
                ],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
                'datePublished' => '2024-01-01',
                'dateModified' => collect(DocsUtils::pages())->max('modified') ?: '2024-01-01',
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>

        <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'DefinedTermSet',
                'name' => 'Event Schedule Glossary',
                'description' => 'Key terms used throughout Event Schedule',
                'hasDefinedTerm' => array_map(fn ($g) => [
                    '@type' => 'DefinedTerm',
                    'name' => $g['term'],
                    'description' => $g['def'],
                ], $glossary),
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    </x-slot>

    <div class="doc-accent-guide">
        <x-docs.icon-sprite />

        {{-- Hero. The one docs page that gets the full es-* treatment: it is a
             landing page, not reference material. --}}
        <section class="es-hero noise relative overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(37,99,235,.26), transparent 65%);"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(14,165,233,.24), transparent 65%);"></div>
                <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(34,211,238,.16), transparent 62%);"></div>
                <div class="es-rays absolute inset-0"></div>
                <div class="absolute inset-0 grid-pattern"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2">
                    <x-docs.icon name="book" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span class="text-sm text-gray-600 dark:text-gray-300">Documentation</span>
                </div>

                <h1 class="es-balance mb-4 text-[2.25rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                    <span class="es-mask"><span class="es-mask-line">Everything you need,</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-docs">in one place</span></span></span>
                </h1>

                <p class="es-fade-up es-d-2 mx-auto mb-9 max-w-2xl text-lg text-gray-500 dark:text-gray-400">
                    Guides for organizers, selfhost installation, SaaS operations, and the REST API.
                </p>

                {{-- Hero scale here, rail scale on every leaf page. Never
                     [data-reveal] - the primary control must be usable on paint. --}}
                <div class="es-fade-up es-d-3 mx-auto max-w-2xl">
                    <x-docs.search variant="hero" />

                    <nav class="mt-4 flex flex-wrap items-center justify-center gap-2" aria-label="Page sections">
                        <span class="text-xs text-gray-400">Jump to</span>
                        @foreach ([['Get started', '#start'], ['User Guide', '#guide'], ['Platforms', '#platforms'], ['Glossary', '#glossary']] as [$label, $href])
                            <a href="{{ $href }}" class="rounded-full border border-gray-200 bg-white/70 px-3 py-1 text-xs font-medium text-gray-600 transition-colors hover:border-[var(--brand-blue)] hover:text-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-gray-300">{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </section>

        {{-- Start here. The rail on every other page owns exhaustive
             navigation, so this page's job is orientation: what a first-time
             visitor should do, in order. --}}
        <section id="start" class="scroll-mt-24 border-t border-gray-200 bg-gray-50 py-14 dark:border-white/5 dark:bg-[#0f0f14]">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6" data-reveal>
                    <p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-blue)]">Start here</p>
                    <h2 class="es-balance text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">From nothing to a live calendar</h2>
                </div>

                <ol class="grid gap-4 md:grid-cols-3" data-reveal-group="80">
                    @foreach ($steps as $i => $step)
                        <li data-reveal>
                            <a href="{{ route($step['route']) }}"
                               class="es-bento es-tilt-inner group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-200 hover:border-blue-500/50 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-400/40"
                               data-tilt="2.5">
                                <span class="mb-3 flex items-center gap-3">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">{{ $i + 1 }}</span>
                                    <span class="text-[0.9375rem] font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</span>
                                </span>
                                <span class="mb-4 block text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $step['text'] }}</span>
                                <span class="mt-auto inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 transition-all group-hover:gap-2.5 dark:text-blue-400">
                                    Read guide
                                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                                    </svg>
                                </span>
                                <span class="es-glare" aria-hidden="true"></span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- User Guide, in task-shaped clusters that mirror the admin portal.
             This used to be 18 hand-written cards in five colour bands, with
             saturated dark:from-*-900 fills that read as a rainbow wash in dark
             mode. Cluster sizes are chosen so every row closes on the 12-column
             grid, so there are no filler tiles. --}}
        <section id="guide" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8" data-reveal>
                    <p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-blue)]">User Guide</p>
                    <h2 class="es-balance text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">Every part of the product</h2>
                </div>

                @foreach (DocsUtils::clusters() as $cluster)
                    @if (count($cluster['pages']))
                        <div class="{{ $loop->first ? '' : 'mt-12' }}">
                            <div class="mb-5 flex flex-wrap items-baseline gap-x-3 gap-y-1" data-reveal>
                                <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $cluster['title'] }}</h3>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $cluster['blurb'] }}</span>
                                <span class="ms-auto text-xs font-medium text-gray-400">{{ count($cluster['pages']) }} guides</span>
                            </div>

                            <div data-reveal-group="70">
                                <x-docs.card-grid :pages="$cluster['pages']" :accent="$cluster['accent']" :cols="$cluster['cols']" />
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- FAQ --}}
        <section class="border-y border-gray-200 bg-gray-50 py-12 dark:border-white/5 dark:bg-[#0f0f14]">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <a href="{{ route('marketing.faq') }}" class="group block" data-reveal>
                    <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-blue-500/50 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-400/40">
                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/15">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            {{-- h2, not h3: this is a sibling section of the
                                 surrounding h2s, not a child of one. --}}
                            <h2 class="font-semibold text-gray-900 dark:text-white">Frequently Asked Questions</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Quick answers to common questions about Event Schedule.</p>
                        </div>
                        <svg class="ms-auto h-5 w-5 flex-shrink-0 text-gray-400 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </section>

        {{-- Platform docs. All three cards are structurally identical now -
             Developer used to carry an extra eyebrow and a wrapped row of check
             icons while the other two used chevron lists. --}}
        <section id="platforms" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8" data-reveal>
                    <p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-blue)]">Run it yourself</p>
                    <h2 class="es-balance text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">Platform and developer docs</h2>
                </div>

                <ul class="grid list-none gap-5 p-0 md:grid-cols-3" data-reveal-group="90">
                    @foreach ($platforms as $p)
                        <li class="es-bento" data-reveal data-tilt="2">
                        <a href="{{ route($p['route']) }}"
                           class="es-tilt-inner group relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 shadow-sm transition-all duration-200 hover:shadow-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/[0.04] {{ $p['hover'] }}">

                            <span class="pointer-events-none absolute -end-20 -top-20 h-40 w-40 rounded-full blur-[60px] {{ $p['glow'] }}" aria-hidden="true"></span>

                            <span class="relative mb-4 inline-flex w-fit items-center rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] {{ $p['badge'] }}">{{ $p['eyebrow'] }}</span>

                            <span class="relative mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $p['chip'] }}">
                                <x-docs.icon :name="$p['icon']" class="h-6 w-6 {{ $p['iconColor'] }}" />
                            </span>

                            <h3 class="relative mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $p['title'] }}</h3>
                            <span class="relative mb-5 block text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $p['lede'] }}</span>

                            <span class="relative mb-6 block space-y-2">
                                @foreach ($p['links'] as $l)
                                    <span class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                        <svg class="h-4 w-4 flex-shrink-0 {{ $p['iconColor'] }} rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                        {{ $l }}
                                    </span>
                                @endforeach
                            </span>

                            <span class="relative mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3 {{ $p['link'] }}">
                                {{ $p['cta'] }}
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4-4 4M3 12h18" />
                                </svg>
                            </span>

                            <span class="es-glare" aria-hidden="true"></span>
                            <span class="es-ring-glow" style="--es-ring-radius: 1.5rem;" aria-hidden="true"></span>
                        </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Glossary --}}
        <section id="glossary" class="scroll-mt-24 border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8" data-reveal>
                    <p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-blue)]">Reference</p>
                    <h2 class="es-balance text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">Glossary</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Key terms used throughout Event Schedule.</p>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="50">
                    @foreach ($glossary as $g)
                        <div class="doc-field" data-reveal>
                            <dt class="doc-field-title">{{ $g['term'] }}</dt>
                            <dd class="doc-field-desc">{{ $g['def'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- Open source. Fixed-dark band, the marketing "finale-lite" idiom -
             no confetti. --}}
        <section class="bg-white py-20 dark:bg-[#0a0a0f]">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="es-band-dark noise relative overflow-hidden rounded-[2rem] px-8 py-14 text-center" data-reveal="panel">
                    <div class="grid-overlay pointer-events-none absolute inset-0 opacity-40" aria-hidden="true"></div>
                    <div class="relative">
                        <span class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                            <svg class="h-7 w-7 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                        </span>
                        <h2 class="es-balance mb-3 text-2xl font-bold text-white md:text-3xl">100% open source</h2>
                        <p class="mx-auto mb-8 max-w-xl text-gray-400">Event Schedule is fully open source. Explore the code, report an issue, or contribute on GitHub.</p>

                        <div class="flex flex-wrap justify-center gap-3">
                            <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] px-6 py-3 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl">
                                View on GitHub
                            </a>
                            <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-6 py-3 font-semibold text-white transition-colors hover:bg-white/20">
                                Discussions
                            </a>
                            <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold text-gray-300 transition-colors hover:text-white">
                                Report an issue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- This page is the only doc page with [data-reveal], so it is the only
         one that opts into the reveal/tilt engine. --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
