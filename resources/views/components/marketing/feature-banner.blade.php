{{--
    Full-width feature banner for the /features page.

    One component so all 20 banners stay identical in structure - the old page
    hand-rolled each one and drifted (a missing hover class, mismatched link
    helpers, 65 muddy chips).

    Two scales: `lead` banners open a chapter and get the larger treatment plus
    the ring glow; the rest sit a step quieter.

    Interaction: the text column carries a stretched link (so the accessible
    name is just the heading, not the whole banner), and the mockup is its own
    aria-hidden link. They cannot be one element - a stretched overlay covering
    the mockup would swallow the pointermove that `data-tilt` needs, and tilt
    would silently never fire.
--}}
@props([
    'href',
    'accent' => 'blue',
    'badge',
    'heading',
    'lede',
    'chips' => [],
    'lead' => false,
    'flip' => false,
    'frame' => 'panel',
    'frameUrl' => null,
    'ground' => 'white',
])

@php
    // Full class strings - interpolated Tailwind colour classes do not JIT-generate.
    $accents = [
        'sky' => [
            'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            'head' => 'group-hover:text-sky-600 dark:group-hover:text-sky-400',
            'link' => 'text-sky-600 dark:text-sky-400',
        ],
        'blue' => [
            'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            'head' => 'group-hover:text-blue-600 dark:group-hover:text-blue-400',
            'link' => 'text-blue-600 dark:text-blue-400',
        ],
        'cyan' => [
            'badge' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
            'head' => 'group-hover:text-cyan-600 dark:group-hover:text-cyan-400',
            'link' => 'text-cyan-600 dark:text-cyan-400',
        ],
        'teal' => [
            'badge' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300',
            'head' => 'group-hover:text-teal-600 dark:group-hover:text-teal-400',
            'link' => 'text-teal-600 dark:text-teal-400',
        ],
        'emerald' => [
            'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'head' => 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400',
            'link' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'amber' => [
            'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'head' => 'group-hover:text-amber-600 dark:group-hover:text-amber-400',
            'link' => 'text-amber-600 dark:text-amber-400',
        ],
        'yellow' => [
            'badge' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300',
            'head' => 'group-hover:text-yellow-600 dark:group-hover:text-yellow-400',
            'link' => 'text-yellow-600 dark:text-yellow-400',
        ],
        'orange' => [
            'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
            'head' => 'group-hover:text-orange-600 dark:group-hover:text-orange-400',
            'link' => 'text-orange-600 dark:text-orange-400',
        ],
        'gray' => [
            'badge' => 'bg-gray-200 text-gray-700 dark:bg-white/10 dark:text-gray-300',
            'head' => 'group-hover:text-gray-600 dark:group-hover:text-gray-300',
            'link' => 'text-gray-600 dark:text-gray-400',
        ],
    ];
    $a = $accents[$accent] ?? $accents['blue'];

    $groundClass = $ground === 'gray'
        ? 'bg-gray-50 dark:bg-[#0f0f14]'
        : 'bg-white dark:bg-[#0a0a0f]';

    // Mobile stays tight; the desktop values are far too airy on a phone.
    $pad = $lead ? 'py-12 lg:py-20' : 'py-10 lg:py-14';
    $gap = $lead ? 'gap-8 lg:gap-16' : 'gap-6 lg:gap-12';
    $headSize = $lead ? 'text-3xl lg:text-5xl' : 'text-2xl lg:text-4xl';
    // Desktop is where the big mockups earn their space; on a phone they only
    // need to be legible, so they stay near the original size.
    $mockWidth = $lead
        ? 'max-w-[17rem] sm:max-w-sm lg:max-w-lg'
        : 'max-w-[15rem] sm:max-w-[19rem] lg:max-w-md';

    // Text on the left enters from the left; the mockup mirrors it.
    $textReveal = $flip ? 'right' : 'left';
    $mockReveal = $flip ? 'left' : 'right';
@endphp

<section class="relative overflow-hidden {{ $groundClass }} {{ $pad }}">
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="group flex flex-col items-center {{ $gap }} {{ $flip ? 'lg:flex-row-reverse' : 'lg:flex-row' }}">

            {{-- Text column: `relative` scopes the stretched link to this side only. --}}
            <div class="relative flex-1 text-center lg:text-start" data-reveal="{{ $textReveal }}">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium {{ $a['badge'] }}">
                    {{ $badgeIcon ?? '' }}
                    {{ $badge }}
                </div>

                <h2 class="es-balance mb-4 {{ $headSize }} font-black tracking-tight text-gray-900 transition-colors dark:text-white {{ $a['head'] }}">
                    <a href="{{ $href }}"
                       class="rounded-sm after:absolute after:inset-0 after:content-[''] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">{{ $heading }}</a>
                </h2>

                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">{{ $lede }}</p>

                @if (! empty($chips))
                    <div class="mb-6 flex flex-wrap justify-center gap-2.5 lg:justify-start">
                        @foreach ($chips as $i => $chip)
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300 {{ $i >= 4 ? 'hidden sm:inline-flex' : '' }}">{{ $chip }}</span>
                        @endforeach
                    </div>
                @endif

                <span class="inline-flex items-center gap-2 font-medium transition-all group-hover:gap-3 {{ $a['link'] }}">
                    Learn more
                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>
            </div>

            {{-- Mockup: its own link so `data-tilt` receives pointer events.
                 aria-hidden + tabindex="-1" keeps it out of the a11y tree, so the
                 banner still announces once. --}}
            <div class="w-full shrink-0 {{ $mockWidth }}" data-reveal="{{ $mockReveal }}">
                {{-- The phone's width lives here rather than on .es-tilt-inner: .es-glare and
                     .es-ring-glow are inset-0 children of this anchor, so a narrower inner frame
                     would leave them tracing the full column instead of the device. --}}
                <a href="{{ $href }}" aria-hidden="true" tabindex="-1"
                   class="es-bento relative block {{ $frame === 'phone' ? 'mx-auto w-full max-w-[16rem]' : '' }}" data-tilt="{{ $lead ? '4' : '3' }}">
                    @switch($frame)
                        @case('browser')
                            <div class="es-tilt-inner overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-900/5 dark:border-white/10 dark:bg-[#101016] dark:shadow-black/40">
                                <div class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-50 px-3 py-2.5 dark:border-white/10 dark:bg-white/[0.04]">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FF5F57;"></span>
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #FEBC2E;"></span>
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #28C840;"></span>
                                    @if ($frameUrl)
                                        <span dir="ltr" class="ms-2 flex-1 truncate rounded-md bg-white px-2 py-1 text-[10px] text-gray-400 dark:bg-white/5 dark:text-gray-500">{{ $frameUrl }}</span>
                                    @endif
                                </div>
                                <div class="p-5">{{ $slot }}</div>
                            </div>
                            @break

                        @case('phone')
                            <div class="es-tilt-inner overflow-hidden rounded-[1.75rem] border-[6px] border-gray-200 bg-white shadow-xl shadow-gray-900/10 dark:border-[#26262c] dark:bg-[#101016] dark:shadow-black/50">
                                <div class="flex justify-center pt-2">
                                    <span class="h-1 w-10 rounded-full bg-gray-200 dark:bg-white/15"></span>
                                </div>
                                <div class="p-4">{{ $slot }}</div>
                            </div>
                            @break

                        @default
                            <div class="es-tilt-inner rounded-2xl border border-gray-200 bg-white p-5 shadow-xl shadow-gray-900/5 dark:border-white/10 dark:bg-[#15151c] dark:shadow-black/40">{{ $slot }}</div>
                    @endswitch

                    <div class="es-glare" aria-hidden="true"></div>
                    @if ($lead)
                        {{-- Radius follows the frame this ring outlines, not the class default. --}}
                        <div class="es-ring-glow" aria-hidden="true"
                             style="--es-ring-radius: {{ $frame === 'phone' ? '1.75rem' : '1rem' }};"></div>
                    @endif
                </a>
            </div>
        </div>
    </div>
</section>
