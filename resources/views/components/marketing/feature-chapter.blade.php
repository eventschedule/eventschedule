{{--
    Chapter divider for the /features page.

    Five of these break the run of 20 banners into acts. The bled ghost numeral
    is the page's wayfinding signal - on mobile, where the dot nav is hidden,
    it is the only "where am I" cue, so it stays substantial.

    The section is `overflow-hidden`, so the numeral can bleed past the leading
    edge without ever contributing to document scrollWidth.
--}}
@props([
    'number',
    'id',
    'accent' => 'blue',
    'title',
    'lede',
    'ground' => 'white',
])

@php
    $accents = [
        'blue' => [
            'text' => 'text-blue-600 dark:text-blue-400',
            'dot' => 'bg-blue-500',
            'rule' => 'from-blue-500/70',
            'glow' => 'rgba(37, 99, 235, ALPHA)',
        ],
        'sky' => [
            'text' => 'text-sky-600 dark:text-sky-400',
            'dot' => 'bg-sky-500',
            'rule' => 'from-sky-500/70',
            'glow' => 'rgba(14, 165, 233, ALPHA)',
        ],
        'cyan' => [
            'text' => 'text-cyan-600 dark:text-cyan-400',
            'dot' => 'bg-cyan-500',
            'rule' => 'from-cyan-500/70',
            'glow' => 'rgba(6, 182, 212, ALPHA)',
        ],
        'emerald' => [
            'text' => 'text-emerald-600 dark:text-emerald-400',
            'dot' => 'bg-emerald-500',
            'rule' => 'from-emerald-500/70',
            'glow' => 'rgba(16, 185, 129, ALPHA)',
        ],
        'amber' => [
            'text' => 'text-amber-600 dark:text-amber-400',
            'dot' => 'bg-amber-500',
            'rule' => 'from-amber-500/70',
            'glow' => 'rgba(245, 158, 11, ALPHA)',
        ],
    ];
    $a = $accents[$accent] ?? $accents['blue'];

    $groundClass = $ground === 'gray'
        ? 'bg-gray-50 dark:bg-[#0f0f14]'
        : 'bg-white dark:bg-[#0a0a0f]';

    // One aurora field per chapter, replacing the old two-blob-per-banner setup.
    $glow = 'radial-gradient(circle at 30% 50%, ' . str_replace('ALPHA', '0.30', $a['glow'])
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
                  class="pointer-events-none absolute top-1/2 -translate-y-1/2 select-none text-[7rem] font-black leading-none {{ $a['text'] }} opacity-[0.08] dark:opacity-[0.13] lg:text-[13rem] ltr:right-0 rtl:left-0">{{ $number }}</span>

            <div class="relative max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full {{ $a['dot'] }}"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] {{ $a['text'] }}">Chapter {{ $number }}</span>
                </div>

                <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $title }}</h2>
                <p class="text-lg text-gray-500 dark:text-gray-400">{{ $lede }}</p>

                <div class="es-ch-rule mt-8 h-px bg-gradient-to-r {{ $a['rule'] }} to-transparent rtl:bg-gradient-to-l"></div>
            </div>
        </div>
    </div>
</section>
