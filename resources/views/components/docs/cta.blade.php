{{--
    End-of-page panel. Audience-aware, not one CTA everywhere.

    "Start for free" is off-key on a selfhost or developer page: those readers
    are already running the product or integrating against it. User Guide pages
    get the sign-up CTA; the platform groups get a support card instead.

    Deliberately not es-finale-panel - no confetti, no data-reveal, one static
    glow. This is the tail of a reference page, not a landing page.
--}}
@props(['group' => 'user-guide'])

@php
    $variants = [
        'user-guide' => [
            'icon' => 'bolt',
            'title' => 'Ready to try it?',
            'text' => 'Create a schedule and add your first event in under five minutes. Free forever, no credit card.',
            'primary' => ['label' => 'Get started free', 'href' => app_url('/sign_up')],
            'secondary' => ['label' => 'See pricing', 'href' => route('marketing.pricing')],
        ],
        'selfhost' => [
            'icon' => 'server',
            'title' => 'Need a hand with your install?',
            'text' => 'Event Schedule is fully open source. Ask a question or report an issue on GitHub.',
            'primary' => ['label' => 'GitHub Discussions', 'href' => 'https://github.com/eventschedule/eventschedule/discussions'],
            'secondary' => ['label' => 'Report an issue', 'href' => 'https://github.com/eventschedule/eventschedule/issues'],
        ],
        'saas' => [
            'icon' => 'cloud',
            'title' => 'Running Event Schedule as a SaaS?',
            'text' => 'Ask operators running the same setup, or open an issue if something is missing.',
            'primary' => ['label' => 'GitHub Discussions', 'href' => 'https://github.com/eventschedule/eventschedule/discussions'],
            'secondary' => ['label' => 'Report an issue', 'href' => 'https://github.com/eventschedule/eventschedule/issues'],
        ],
        'developer' => [
            'icon' => 'code',
            'title' => 'Building an integration?',
            'text' => 'Grab an API key in your account settings, or ask on GitHub if an endpoint is missing.',
            'primary' => ['label' => 'GitHub Discussions', 'href' => 'https://github.com/eventschedule/eventschedule/discussions'],
            'secondary' => ['label' => 'Account settings', 'href' => route('marketing.docs.account_settings')],
        ],
    ];

    $v = $variants[$group] ?? $variants['user-guide'];
    $external = str_starts_with($v['primary']['href'], 'http') && ! str_contains($v['primary']['href'], 'eventschedule.test');
@endphp

<aside class="doc-cta">
    <span class="doc-cta-icon" aria-hidden="true">
        <x-docs.icon :name="$v['icon']" class="h-5 w-5" />
    </span>

    <div class="doc-cta-body">
        <p class="doc-cta-title">{{ $v['title'] }}</p>
        <p class="doc-cta-text">{{ $v['text'] }}</p>
    </div>

    <div class="doc-cta-actions">
        <a href="{{ $v['primary']['href'] }}"
           @if ($external) target="_blank" rel="noopener noreferrer" @endif
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] px-4 py-2.5 text-sm font-semibold text-white no-underline shadow-sm transition-all hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] hover:shadow-md">
            {{ $v['primary']['label'] }}
        </a>

        <a href="{{ $v['secondary']['href'] }}"
           @if (str_starts_with($v['secondary']['href'], 'http') && ! str_contains($v['secondary']['href'], config('app.url'))) target="_blank" rel="noopener noreferrer" @endif
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 no-underline transition-colors hover:bg-gray-50 dark:border-white/15 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">
            {{ $v['secondary']['label'] }}
        </a>
    </div>
</aside>
