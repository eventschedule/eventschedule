{{--
    Leaf-page hero.

    Full-bleed, so the aurora and grid reach the viewport edges and read as the
    marketing language - boxed into a column they stop being atmosphere and
    become a card. But the copy inside uses the SAME grid template as the shell
    below, so the h1 lands exactly over the content column's leading edge and
    both rails start from one clean horizontal line.

    The section label sits in the LEFT cell, over the nav rail, where it reads
    as the rail's caption. It used to be a pill in the content column, which
    made three location cues stack up (breadcrumb, pill, rail-current) before
    the reader had seen a word of content.

    Compact on purpose - pt-7/pb-8 rather than py-16 - so content starts high.

    Motion: es-mask on the h1 and es-fade-up on the rest. This is the only part
    of a doc page that animates; nothing in the body uses [data-reveal],
    because reference material must not fade in while it is being scanned. The
    layout's gate also skips es-anim entirely when the URL carries a hash.
--}}
@props([
    'accent' => 'guide',
    'icon' => 'book',
    'title',
    'emphasis' => null,
    'lede' => null,
    'eyebrow' => null,
    'plan' => null,
    'section' => null,
    'sectionTitle' => null,
    'sectionRoute' => null,
])

@php
    // Full class strings: interpolated Tailwind colour classes do not
    // JIT-generate. Mirrors the idiom in components/marketing/feature-banner.
    $accents = [
        'guide' => [
            'label' => 'User Guide',
            'chip' => 'bg-blue-100 dark:bg-blue-500/15',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'dot' => 'bg-blue-500 dark:bg-blue-400',
            'text' => 'text-blue-700 dark:text-blue-300',
            'aurora' => 'rgba(37, 99, 235, 0.22)',
            'aurora2' => 'rgba(14, 165, 233, 0.16)',
        ],
        'selfhost' => [
            'label' => 'Selfhost',
            'chip' => 'bg-sky-100 dark:bg-sky-500/15',
            'icon' => 'text-sky-600 dark:text-sky-400',
            'dot' => 'bg-sky-500 dark:bg-sky-400',
            'text' => 'text-sky-700 dark:text-sky-300',
            'aurora' => 'rgba(2, 132, 199, 0.22)',
            'aurora2' => 'rgba(34, 211, 238, 0.16)',
        ],
        'saas' => [
            'label' => 'SaaS Platform',
            'chip' => 'bg-cyan-100 dark:bg-cyan-500/15',
            'icon' => 'text-cyan-600 dark:text-cyan-400',
            'dot' => 'bg-cyan-500 dark:bg-cyan-400',
            'text' => 'text-cyan-700 dark:text-cyan-300',
            'aurora' => 'rgba(8, 145, 178, 0.22)',
            'aurora2' => 'rgba(45, 212, 191, 0.16)',
        ],
        'developer' => [
            'label' => 'Developer',
            'chip' => 'bg-emerald-100 dark:bg-emerald-500/15',
            'icon' => 'text-emerald-600 dark:text-emerald-400',
            'dot' => 'bg-emerald-500 dark:bg-emerald-400',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'aurora' => 'rgba(5, 150, 105, 0.22)',
            'aurora2' => 'rgba(20, 184, 166, 0.16)',
        ],
    ];

    $a = $accents[$accent] ?? $accents['guide'];

    // Gradient-accent the last word unless the caller names the span.
    if ($emphasis === null) {
        $parts = preg_split('/\s+/', trim($title));
        $emphasis = array_pop($parts);
        $lead = implode(' ', $parts);
    } else {
        $lead = trim(str_replace($emphasis, '', $title));
    }
@endphp

<section class="doc-hero noise relative overflow-hidden border-b border-gray-200 bg-white pb-8 pt-7 dark:border-white/5 dark:bg-[#0a0a0f] lg:pb-11 lg:pt-9">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 40%, {{ $a['aurora'] }}, transparent 66%);"></div>
        <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 76% 66%, {{ $a['aurora2'] }}, transparent 66%);"></div>
        <div class="doc-hero-grid absolute inset-0 grid-pattern"></div>
    </div>

    <div class="relative z-10 mx-auto max-w-[92rem] px-4 sm:px-6 lg:px-8">
        <div class="doc-shell">
            {{-- Left cell: sits over the nav rail --}}
            <div class="es-fade-up es-d-1 mb-3 hidden lg:mb-0 lg:block lg:pt-1">
                <span class="inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full {{ $a['dot'] }}"></span>
                    <span class="text-[0.6875rem] font-bold uppercase tracking-[0.14em] {{ $a['text'] }}">{{ $eyebrow ?? $a['label'] }}</span>
                </span>
            </div>

            {{-- Centre cell: aligned with the content column --}}
            <div class="min-w-0">
                <div class="es-fade-up es-d-1">
                    <x-docs-breadcrumb :currentTitle="$title" :section="$section" :sectionTitle="$sectionTitle" :sectionRoute="$sectionRoute" />
                </div>

                <div class="flex items-start gap-3.5">
                    <span class="es-fade-up es-d-1 mt-0.5 inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $a['chip'] }}">
                        <x-docs.icon :name="$icon" class="h-6 w-6 {{ $a['icon'] }}" stroke="1.7" />
                    </span>

                    <h1 class="es-balance text-[1.75rem] font-black leading-[1.1] tracking-tight text-gray-900 dark:text-white sm:text-4xl lg:text-[2.5rem]">
                        <span class="es-mask"><span class="es-mask-line">@if ($lead){{ $lead }} @endif<span class="text-gradient-docs">{{ $emphasis }}</span></span></span>
                    </h1>
                </div>

                @if ($lede)
                    <p class="es-fade-up es-d-2 mt-3.5 max-w-2xl text-base text-gray-500 dark:text-gray-400 sm:text-lg">{{ $lede }}</p>
                @endif
            </div>

            {{-- Right cell: the page's plan badge, or nothing.
                 items-start matters - the shell grid stretches its cells, and
                 an inline-flex badge in a stretched flex cell renders as a
                 full-height capsule. --}}
            <div class="es-fade-up es-d-3 mt-4 hidden self-start xl:mt-0 xl:flex xl:items-start xl:justify-end xl:pt-1">
                @if ($plan)
                    <x-doc-badge :plan="$plan" align="block" link />
                @endif
            </div>
        </div>
    </div>
</section>
