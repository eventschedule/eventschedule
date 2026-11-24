@props([
    'label',
    // Kept so a caller does not change; the 2026-11 design draws no icon tile.
    'icon' => null,
    'tint' => null,
    'ink' => null,
    'glow' => null,
    // The page that explains the number. Every tile of the dashboard has one.
    'href' => null,
    // A small dot before the label: the Realtime tile's "someone is here" (green) or "quiet" (grey).
    'dot' => null,
])

{{-- One of the four numbers on /dashboard (CLAUDE.md, "Dashboard-style stat panels"), in the
     2026-11 design's short form: a small capitals label that says what and over how long
     ("Views · 30 days"), the figure (a change badge may sit inline beside it), an optional strip
     of bars, and one quiet line ("277 sales", "Next event today"). The whole tile is the link.

     Slots: the default slot is the figure; `bars` is the strip; `line` is the one line under it.

     A phone gives a tile about 140px of text. English fits that; "Visualizzazioni · 30 giorni"
     does not, so the label may wrap (it is the one thing that can) and the line wraps too. --}}
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'group ap-card rounded-xl p-4 sm:p-[18px] h-full flex flex-col items-stretch gap-2.5 transition-all duration-200 text-gray-900 dark:text-white'
        .($href ? ' hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    <p class="flex items-center gap-2 min-w-0 text-xs leading-tight font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400 break-words">
        @if ($dot)
            <span data-tile-dot class="w-2 h-2 rounded-full shrink-0 {{ $dot }}" aria-hidden="true"></span>
        @endif
        <span class="min-w-0">{{ $label }}</span>
    </p>

    <div class="w-full dashboard-tile-figure">{{ $slot }}</div>

    @isset($bars)
        <div class="w-full">{{ $bars }}</div>
    @endisset

    @isset($line)
        <p class="w-full text-sm leading-snug text-gray-600 dark:text-gray-400">{{ $line }}</p>
    @endisset
</{{ $tag }}>
