@props([
    'label',
    'icon',
    'tint',
    'ink',
    'glow',
    // An anchor on the same page: the tile jumps to the card that explains its number.
    'href' => null,
])

{{-- One headline number of the admin dashboard, in the AP's stat-panel shape (CLAUDE.md,
     "Dashboard-style stat panels").

     The number hangs from a header of reserved height, and the footer is pinned to the bottom, so
     the tiles of a row share a baseline whatever wraps: centring the number in the leftover space
     lifted it off the row whenever one label ran to two lines. The slot is the figure with its
     caption; `footer` is one line under it. --}}
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'ap-card rounded-xl p-4 sm:p-6 h-full flex flex-col items-center transition-all duration-200'
        .($href ? ' hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center gap-3 mb-3 self-start min-h-[2.5rem]">
        <div class="dashboard-icon p-2 rounded-xl {{ $tint }}" style="--icon-glow: {{ $glow }}">
            <svg class="w-5 h-5 {{ $ink }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
    </div>

    <div class="w-full flex justify-center">{{ $slot }}</div>

    <p class="mt-auto pt-2 w-full min-h-[1.25rem] text-center text-xs text-gray-500 dark:text-gray-400">{{ $footer ?? '' }}</p>
</{{ $tag }}>
