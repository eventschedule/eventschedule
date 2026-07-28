{{--
    One check row in a /pricing plan card.

    Exists so the three cards cannot drift: the hand-rolled version had 39 copies
    of this block and the icon markup had already diverged between cards.

    The wording and order of the labels passed in are curated (CLAUDE.md:43) -
    this component styles them, it never edits them.
--}}
@props(['accent' => 'emerald'])

@php
    // Full class strings - interpolated Tailwind colour classes do not JIT-generate.
    $accents = [
        'emerald' => [
            'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
            'icon' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'blue' => [
            'chip' => 'bg-blue-100 dark:bg-blue-500/20',
            'icon' => 'text-blue-600 dark:text-blue-400',
        ],
        'amber' => [
            'chip' => 'bg-amber-100 dark:bg-amber-500/20',
            'icon' => 'text-amber-600 dark:text-amber-400',
        ],
    ];
    $a = $accents[$accent] ?? $accents['emerald'];
@endphp

<li class="flex items-start gap-3">
    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $a['chip'] }}">
        <svg aria-hidden="true" class="h-3 w-3 {{ $a['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
    </span>
    <span class="text-gray-600 dark:text-gray-300">{{ $slot }}</span>
</li>
