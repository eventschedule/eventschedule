{{--
    The single plan badge, replacing six competing variants that had drifted
    across the docs (amber-100/800 x11, blue-100/800 x10, cyan-100/800,
    teal-500/20 x2, and a bare text-cyan-600), with mixed rounded /
    rounded-full and ml-1 / ml-2 / mb-4 spacing.

    Tier names come from docs/FEATURES.md: Free, Pro, Enterprise. 'selfhost'
    exists because selfhosted deployments get every Enterprise feature and the
    selfhost docs need to say so without implying a paid tier.

    Full class strings in a PHP map - interpolated Tailwind colour classes do
    not JIT-generate.
--}}
@props([
    'plan' => 'pro',
    'align' => 'inline',
    'link' => false,
    'label' => null,
])

@php
    $tiers = [
        'free' => [
            'label' => 'Free',
            'sr' => 'Available on the Free plan',
            'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/25',
            'dot' => 'bg-emerald-500 dark:bg-emerald-400',
        ],
        'pro' => [
            'label' => 'Pro',
            'sr' => 'Requires the Pro plan',
            'chip' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-400/25',
            'dot' => 'bg-blue-500 dark:bg-blue-400',
        ],
        'enterprise' => [
            'label' => 'Enterprise',
            'sr' => 'Requires the Enterprise plan',
            'chip' => 'bg-amber-50 text-amber-800 ring-amber-600/25 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/25',
            'dot' => 'bg-amber-500 dark:bg-amber-400',
        ],
        'selfhost' => [
            'label' => 'Selfhost',
            'sr' => 'Selfhosted deployments only',
            'chip' => 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-white/10 dark:text-gray-300 dark:ring-white/15',
            'dot' => 'bg-slate-500 dark:bg-gray-400',
        ],
        'new' => [
            'label' => 'New',
            'sr' => 'Recently added',
            'chip' => 'bg-cyan-50 text-cyan-700 ring-cyan-600/20 dark:bg-cyan-500/15 dark:text-cyan-300 dark:ring-cyan-400/25',
            'dot' => 'bg-cyan-500 dark:bg-cyan-400',
        ],
    ];

    $tier = $tiers[$plan] ?? $tiers['pro'];

    // .doc-heading is display:flex, so a badge inside one needs no left margin.
    $spacing = $align === 'block' ? 'mb-3' : 'align-middle';

    $classes = 'inline-flex items-center gap-1.5 rounded-full px-2 py-[0.1875rem] text-[0.6875rem] font-semibold uppercase tracking-[0.06em] leading-none ring-1 whitespace-nowrap '
        .$tier['chip'].' '.$spacing;

    $isLink = $link && in_array($plan, ['pro', 'enterprise'], true);
@endphp

@if ($isLink)
    <a href="{{ route('marketing.pricing') }}"
       class="{{ $classes }} no-underline transition-shadow hover:ring-2"
       title="{{ $tier['sr'] }}">
        <span class="h-1.5 w-1.5 rounded-full {{ $tier['dot'] }}" aria-hidden="true"></span>
        {{ $label ?? $tier['label'] }}
        <span class="sr-only"> - {{ $tier['sr'] }}</span>
    </a>
@else
    <span class="{{ $classes }}" title="{{ $tier['sr'] }}">
        <span class="h-1.5 w-1.5 rounded-full {{ $tier['dot'] }}" aria-hidden="true"></span>
        {{ $label ?? $tier['label'] }}
        <span class="sr-only"> - {{ $tier['sr'] }}</span>
    </span>
@endif
