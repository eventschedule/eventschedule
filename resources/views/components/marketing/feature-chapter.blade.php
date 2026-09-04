{{--
    Chapter divider for the /features page.

    Five of these break the run of 20 banners into acts. The bled ghost numeral
    is the page's wayfinding signal - on mobile, where the dot nav is hidden,
    it is the only "where am I" cue, so it stays substantial.

    The section is `overflow-hidden`, so the numeral can bleed past the leading
    edge without ever contributing to document scrollWidth.

    `ground="dark"` is a fixed-dark band (dark in light mode too) for pages that
    dive somewhere else mid-scroll, and `label` renames the eyebrow ("Act 01").
    Both are opt-in; /features passes neither.
--}}
@props([
    'number',
    'id',
    'accent' => 'blue',
    'title',
    'lede',
    'ground' => 'white',
    'label' => 'Chapter',
])

@php
    // `onDark` is the same hue with the light-mode step dropped - needed because a
    // `ground="dark"` chapter is dark in BOTH colour modes, so `dark:` never fires
    // for a light-mode visitor. Full class strings: interpolated Tailwind colour
    // classes do not JIT-generate.
    $accents = [
        'blue' => [
            'text' => 'text-blue-700 dark:text-blue-400',
            'onDark' => 'text-blue-400',
            'dot' => 'bg-blue-500',
            'rule' => 'from-blue-500/70',
            'glow' => 'rgba(37, 99, 235, ALPHA)',
        ],
        'sky' => [
            'text' => 'text-sky-700 dark:text-sky-400',
            'onDark' => 'text-sky-400',
            'dot' => 'bg-sky-500',
            'rule' => 'from-sky-500/70',
            'glow' => 'rgba(14, 165, 233, ALPHA)',
        ],
        'cyan' => [
            'text' => 'text-cyan-700 dark:text-cyan-400',
            'onDark' => 'text-cyan-400',
            'dot' => 'bg-cyan-500',
            'rule' => 'from-cyan-500/70',
            'glow' => 'rgba(6, 182, 212, ALPHA)',
        ],
        'emerald' => [
            'text' => 'text-emerald-700 dark:text-emerald-400',
            'onDark' => 'text-emerald-400',
            'dot' => 'bg-emerald-500',
            'rule' => 'from-emerald-500/70',
            'glow' => 'rgba(16, 185, 129, ALPHA)',
        ],
        'amber' => [
            'text' => 'text-amber-700 dark:text-amber-400',
            'onDark' => 'text-amber-400',
            'dot' => 'bg-amber-500',
            'rule' => 'from-amber-500/70',
            'glow' => 'rgba(245, 158, 11, ALPHA)',
        ],
    ];
    $a = $accents[$accent] ?? $accents['blue'];

    // `dark` is a FIXED-dark ground: it stays dark in light mode too, so a page can
    // dive backstage mid-scroll. Everything keyed off `dark:` therefore has to be
    // forced on unconditionally below, or light mode renders black on black.
    $isDark = $ground === 'dark';
    $groundClass = match ($ground) {
        'gray' => 'bg-gray-50 dark:bg-[#0f0f14]',
        'dark' => 'es-band-dark noise',
        default => 'bg-white dark:bg-[#0a0a0f]',
    };

    $accentText = $isDark ? $a['onDark'] : $a['text'];
    $titleClass = $isDark ? 'text-white' : 'text-gray-900 dark:text-white';
    $ledeClass = $isDark ? 'text-gray-400' : 'text-gray-500 dark:text-gray-400';
    $numeralOpacity = $isDark ? 'opacity-[0.13]' : 'opacity-[0.08] dark:opacity-[0.13]';

    // One aurora field per chapter, replacing the old two-blob-per-banner setup.
    $glow = 'radial-gradient(circle at 30% 50%, ' . str_replace('ALPHA', $isDark ? '0.22' : '0.30', $a['glow'])
        . ', ' . str_replace('ALPHA', '0', $a['glow']) . ' 65%)';
@endphp

<section id="{{ $id }}" class="relative scroll-mt-24 overflow-hidden {{ $groundClass }} pb-4 pt-14 lg:pb-8 lg:pt-20">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="es-aurora es-ch-aurora es-aurora-1" style="background: {{ $glow }};"></div>
    </div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative" data-reveal>
            {{-- The copy is capped at max-w-3xl, so the trailing half of the row is
                 empty - the numeral lives there. Bleeding it off the leading edge
                 instead put it directly behind the eyebrow, where it read as a smudge. --}}
            <span aria-hidden="true"
                  class="pointer-events-none absolute top-1/2 -translate-y-1/2 select-none text-[7rem] font-black leading-none {{ $accentText }} {{ $numeralOpacity }} lg:text-[13rem] ltr:right-0 rtl:left-0">{{ $number }}</span>

            <div class="relative max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full {{ $a['dot'] }}"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] {{ $accentText }}">{{ $label }} {{ $number }}</span>
                </div>

                <h2 class="es-balance mb-3 text-3xl font-black tracking-tight {{ $titleClass }} md:text-4xl">{{ $title }}</h2>
                <p class="text-lg {{ $ledeClass }}">{{ $lede }}</p>

                <div class="es-ch-rule mt-8 h-px bg-gradient-to-r {{ $a['rule'] }} to-transparent rtl:bg-gradient-to-l"></div>
            </div>
        </div>
    </div>
</section>
