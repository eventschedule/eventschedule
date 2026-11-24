@props([
    'label',
    'icon',
    'tint',
    'ink',
    'glow',
    // An anchor on the same page: the tile jumps to the card that explains its number.
    'href' => null,
])

{{-- One headline number of a platform admin page, in the AP's stat-panel shape (CLAUDE.md,
     "Dashboard-style stat panels").

     The number hangs from a header of reserved height, and the footer is pinned to the bottom, so
     the tiles of a row share a baseline whatever wraps: centring the number in the leftover space
     lifted it off the row whenever one label ran to two lines. The slot is the figure with its
     caption; `footer` is one line under it. --}}
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'ap-card rounded-xl p-4 sm:p-5 h-full flex flex-col items-start transition-all duration-200'
        .($href ? ' hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{-- 2026-11 redesign: the anatomy of the dashboard's tiles. A small capitals label (12px/600,
         +0.06em, the design-language review's label), the figure and its caption from the start
         of the line, one quiet line under it, no icon tile ("Decoration: no icon chips"). The icon
         props are kept so no caller changes. --}}
    <p class="self-stretch mb-2 min-h-[1.25rem] text-xs leading-tight font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400">{{ $label }}</p>

    <div class="w-full flex justify-start text-start admin-stat-figure">{{ $slot }}</div>

    <p class="mt-auto pt-2 w-full min-h-[1.25rem] text-start text-xs text-gray-500 dark:text-gray-400">{{ $footer ?? '' }}</p>
</{{ $tag }}>
