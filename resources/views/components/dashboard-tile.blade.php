@props([
    'label',
    'icon',
    'tint',
    'ink',
    'glow',
    // The page that explains the number. Every tile of the dashboard has one.
    'href' => null,
    // A small dot on the icon: the Realtime tile's "someone is here" (green) or "quiet" (grey).
    'dot' => null,
    // One label and its value under a hairline, the same on every tile.
    'footLabel' => '',
    'footValue' => '',
])

{{-- One of the four numbers on /dashboard, in the AP's stat-panel shape (CLAUDE.md, "Dashboard-style
     stat panels"). The slot is the figure with its caption; `bars` is a strip of small bars under
     it; the footer is one label with its value.

     Not <x-admin-stat-tile>, which is the admin dashboard's: that one jumps to a card on the same
     page and has a centred sentence for a footer. This one opens another page and says so with a
     chevron, and its footer is a label and a value, which is what stays readable when the label is
     a longer word in another language.

     The figure's block keeps room for a caption of two lines. Most captions are one, but the
     Realtime tile's wrap on a phone and in a longer language, and without the reserved line the
     bars of a row would stop sharing a baseline.

     A phone gives a tile about 140px of text. English fits that; "Visualizzazioni" and
     "Предстоящие события" do not, and a label that cannot wrap pushed its chevron, then itself,
     out of the tile. So below `sm` the label may take two lines (the header row is already that
     tall), the chevron goes (the whole tile is the link), and the footer's label wraps to two
     lines and keeps room for them on every tile, so the hairlines of a row stay level. --}}
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'group ap-card rounded-xl p-4 sm:p-5 h-full flex flex-col items-center transition-all duration-200'
        .($href ? ' hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center gap-2 sm:gap-3 mb-2 self-stretch min-h-[2.5rem]">
        <div class="relative dashboard-icon p-2 rounded-xl {{ $tint }}" style="--icon-glow: {{ $glow }}">
            <svg class="w-5 h-5 {{ $ink }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
            </svg>
            @if ($dot)
                <span data-tile-dot class="absolute -top-0.5 -end-0.5 w-2.5 h-2.5 rounded-full ring-2 ring-white dark:ring-gray-800 {{ $dot }}" aria-hidden="true"></span>
            @endif
        </div>
        <p class="min-w-0 text-[13px] sm:text-sm leading-tight font-medium text-gray-500 dark:text-gray-400 break-words sm:whitespace-nowrap">{{ $label }}</p>
        @if ($href)
            <svg class="hidden sm:block ms-auto w-4 h-4 shrink-0 text-gray-400 transition-all duration-200 group-hover:text-gray-600 dark:group-hover:text-gray-200 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        @endif
    </div>

    <div class="w-full min-h-[4.1rem]">{{ $slot }}</div>

    <div class="mt-3 w-full">{{ $bars ?? '' }}</div>

    <p class="mt-auto w-full">
        <span class="mt-3 pt-2 min-h-[2.5rem] sm:min-h-0 flex items-baseline justify-between gap-2 text-xs" style="border-top: 1px solid var(--ap-hairline)">
            <span class="min-w-0 max-sm:line-clamp-2 sm:truncate text-gray-500 dark:text-gray-400">{{ $footLabel }}</span>
            <span class="shrink-0 font-medium tabular-nums text-gray-900 dark:text-gray-100">{{ $footValue }}</span>
        </span>
    </p>
</{{ $tag }}>
