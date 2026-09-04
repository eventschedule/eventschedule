{{--
    A grid of doc-page cards, rendered from config/docs.php.

    Replaces the hand-written card walls on the section hubs and the /docs
    index, which each duplicated every page's title, blurb and icon - and had
    already drifted out of step with the prev/next order.

    Pass either `group` (all pages in a manifest group) or `pages` (an explicit
    list). `except` drops the hub's own entry so it does not link to itself.

    The accent shows up in exactly three quiet places: the icon chip, a 1px top
    hairline and the hover border. The old cards used saturated
    `dark:from-blue-900` gradient fills, which is why a wall of them read as a
    rainbow wash in dark mode.

    No es-bento / es-tilt-inner / es-glare here: .es-glare is positioned from the
    --gx/--gy that marketing-home.js sets on [data-tilt] elements only, so
    without a data-tilt the glare never tracks the cursor - and the selfhost hub
    does not load marketing-home.js at all. The border/shadow/lift hover below
    works everywhere, including with JS off.
--}}
@props([
    'group' => null,
    'pages' => null,
    'except' => null,
    // Heading level for the card titles. /docs nests these under an h3, but the
    // selfhost hub puts a grid straight under its h2, and a fixed h4 made that a
    // level skip. Callers say which rank they are actually sitting at.
    'level' => 4,
    'accent' => 'blue',
    /** 3 or 4 - how many cards per row at the widest breakpoint. Null = plain grid. */
    'cols' => null,
    'columns' => 'sm:grid-cols-2 lg:grid-cols-3',
])

@php
    $items = $pages ?? ($group ? \App\Utils\DocsUtils::pagesInGroup($group) : []);

    if ($except) {
        $items = array_values(array_filter($items, fn ($p) => $p['key'] !== $except));
    }

    // Full class strings - interpolated Tailwind colour classes do not
    // JIT-generate.
    $accents = [
        'blue' => ['chip' => 'bg-blue-100 dark:bg-blue-500/15', 'icon' => 'text-blue-600 dark:text-blue-400', 'rule' => 'from-blue-500/60', 'hover' => 'hover:border-blue-500/50 dark:hover:border-blue-400/40'],
        'sky' => ['chip' => 'bg-sky-100 dark:bg-sky-500/15', 'icon' => 'text-sky-600 dark:text-sky-400', 'rule' => 'from-sky-500/60', 'hover' => 'hover:border-sky-500/50 dark:hover:border-sky-400/40'],
        'cyan' => ['chip' => 'bg-cyan-100 dark:bg-cyan-500/15', 'icon' => 'text-cyan-600 dark:text-cyan-400', 'rule' => 'from-cyan-500/60', 'hover' => 'hover:border-cyan-500/50 dark:hover:border-cyan-400/40'],
        'teal' => ['chip' => 'bg-teal-100 dark:bg-teal-500/15', 'icon' => 'text-teal-600 dark:text-teal-400', 'rule' => 'from-teal-500/60', 'hover' => 'hover:border-teal-500/50 dark:hover:border-teal-400/40'],
        'emerald' => ['chip' => 'bg-emerald-100 dark:bg-emerald-500/15', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'rule' => 'from-emerald-500/60', 'hover' => 'hover:border-emerald-500/50 dark:hover:border-emerald-400/40'],
    ];

    $a = $accents[$accent] ?? $accents['blue'];

    // Full class strings live HERE, not in config/docs.php - Tailwind's content
    // globs cover resources/views/** but not config/, so a class string there is
    // invisible to the JIT and silently never generated.
    //
    // Both options fill complete rows at every breakpoint: 3 x span-4 = one row
    // of three; 4 x span-6 = two rows of two at lg, then 4 x span-3 = one row of
    // four at xl. Four across at lg left each card ~215px, which wrapped the
    // longer titles and broke the shared baseline within a row.
    // 5 x span-4 leaves a two-card row at lg, so the last card widens to close it - the same
    // trick as the 3-card sm rule below. At xl the track is four across and the fifth card takes
    // the remaining eight columns.
    $spans = [
        3 => 'lg:col-span-4',
        4 => 'lg:col-span-6 xl:col-span-3',
        5 => 'lg:col-span-6 xl:col-span-4',
    ];

    $span = $cols ? ($spans[$cols] ?? null) : null;
@endphp

@if (count($items))
    {{-- The sm fallback must survive even when a caller passes `span`: without
         it the 12-column track was the ONLY multi-column rule, so the grid
         collapsed to a single column everywhere below lg - which put a stack of
         full-width bands directly under the 3-up rows around it. --}}
    <ul class="grid list-none grid-cols-1 gap-4 p-0 sm:grid-cols-2 {{ $span ? 'lg:grid-cols-12' : $columns }}">
        @foreach ($items as $item)
            {{-- At sm the grid is 2-up, so an odd-length cluster would leave a
                 hole in the bottom-right. The last card of a 3-card cluster
                 spans both columns to close it; lg:col-span-* overrides this
                 again at the wider breakpoints. --}}
            {{-- The wide last card REPLACES the standard span rather than joining it. Tailwind emits
                 col-span-* in lexicographic order, so col-span-12 lands before col-span-2..6 in the
                 stylesheet and `lg:col-span-6 lg:col-span-12` on one element resolves to 6. --}}
            @php
                $itemSpan = ($cols === 5 && $loop->last)
                    ? 'sm:col-span-2 lg:col-span-12 xl:col-span-8'
                    : $span;
            @endphp
            <li class="{{ $itemSpan }} @if ($cols === 3 && $loop->last) sm:col-span-2 @endif">
                <a href="{{ route($item['route']) }}"
                   class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/[0.04] sm:p-5 {{ $a['hover'] }}">

                    <span class="absolute inset-x-0 top-0 h-px bg-gradient-to-r {{ $a['rule'] }} to-transparent" aria-hidden="true"></span>

                    <span class="mb-2 flex items-center gap-3 sm:mb-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $a['chip'] }}">
                            <x-docs.icon :name="$item['icon']" class="h-[1.125rem] w-[1.125rem] {{ $a['icon'] }}" />
                        </span>
                        {{-- A heading, not a span: these are the page names, and
                             they are what a screen-reader user browsing by
                             heading actually wants from a documentation index.
                             A heading is valid flow content inside an <a>. --}}
                        <h{{ $level }} class="text-[0.9375rem] font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</h{{ $level }}>
                    </span>

                    <span class="block text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $item['blurb'] }}</span>

                    {{-- Hidden below sm: the whole card is already a link, and 18
                         of these stacked added ~800px to the mobile page for no
                         extra information. Same trade features.blade.php makes.
                         mt-auto keeps the row bottoms aligned where it shows. --}}
                    <span class="mt-auto hidden items-center gap-1.5 pt-4 text-sm font-medium text-gray-500 transition-all group-hover:gap-2.5 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white sm:inline-flex">
                        Read guide
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                        </svg>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
