{{--
    One audience card in the /use-cases directory.

    Replaces 30 hand-rolled copies of the same 15-line block. Accent belongs to the
    GROUP, not the card, so a single card tells you which section you are in without
    reading the heading.

    Tags stay fully visible at every breakpoint - they are how a visitor recognises
    themselves ("Tribute Acts", "Resident DJs"), so they get quieter, never hidden.
--}}
@props([
    'url',
    'name',
    'blurb',
    'accent' => 'blue',
    'tags' => [],
    'external' => false,
])

@php
    // Full class strings - interpolated Tailwind colour classes do not JIT-generate.
    $accents = [
        'blue' => [
            'chip' => 'bg-blue-100 dark:bg-blue-500/20',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'hover' => 'group-hover:text-blue-600 dark:group-hover:text-blue-400',
        ],
        'amber' => [
            'chip' => 'bg-amber-100 dark:bg-amber-500/20',
            'icon' => 'text-amber-700 dark:text-amber-400',
            'hover' => 'group-hover:text-amber-700 dark:group-hover:text-amber-400',
        ],
        'emerald' => [
            'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
            'icon' => 'text-emerald-600 dark:text-emerald-400',
            'hover' => 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400',
        ],
        'cyan' => [
            'chip' => 'bg-cyan-100 dark:bg-cyan-500/20',
            'icon' => 'text-cyan-600 dark:text-cyan-400',
            'hover' => 'group-hover:text-cyan-600 dark:group-hover:text-cyan-400',
        ],
        'slate' => [
            'chip' => 'bg-slate-100 dark:bg-slate-500/20',
            'icon' => 'text-slate-600 dark:text-slate-300',
            'hover' => 'group-hover:text-slate-700 dark:group-hover:text-slate-200',
        ],
    ];
    $a = $accents[$accent] ?? $accents['blue'];

    $surface = 'border border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.04]';
@endphp

{{-- es-bento is the hover scope the shared glare rule keys off. es-tilt-inner sits on
     the same element (not a child) so it cannot be a second grid item; that means the
     shared .es-bento:hover .es-tilt-inner shadow will not match, hence the explicit
     hover:shadow-xl here. --}}
<a href="{{ $url }}"
   @if ($external) target="_blank" rel="noopener" @endif
   data-reveal
   data-tilt="2.5"
   class="es-bento es-tilt-inner group relative flex h-full flex-col overflow-hidden rounded-3xl p-6 shadow-sm transition-shadow duration-200 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] {{ $surface }}">


    <div class="mb-3 flex items-center gap-3">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $a['chip'] }}">
            <svg class="h-5 w-5 {{ $a['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">{{ $icon }}</svg>
        </span>
    </div>

    <h3 class="mb-2 text-lg font-bold text-gray-900 transition-colors dark:text-white {{ $a['hover'] }}">{{ $name }}</h3>

    <p class="mb-4 flex-grow text-sm text-gray-600 dark:text-gray-400">{{ $blurb }}</p>

    @if (! empty($tags))
        <p class="mt-auto text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">{{ implode(' · ', $tags) }}</p>
    @endif


    <div class="es-glare"></div>
</a>
