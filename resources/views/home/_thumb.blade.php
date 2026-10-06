{{-- A schedule's or an event's picture, or a quiet tile with its initial (or an icon) when it has
     none. $thumbSmall is the 40px one used in lists of schedules; the default is the 48px row
     thumb. Tested as exactly true: an include inherits its parent's variables, and a parent's
     `$small` (a class string in the Coming up card) once made every event thumb the small one. --}}
@php
    $box = ($thumbSmall ?? false) === true ? 'w-10 h-10 rounded-lg' : 'w-12 h-12 rounded-xl';
@endphp
@if (! empty($image))
    <img src="{{ $image }}" alt="" loading="lazy" class="{{ $box }} object-cover shrink-0">
@else
    <span class="{{ $box }} shrink-0 flex items-center justify-center text-base font-semibold text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-2)" aria-hidden="true">
        @if (! empty($icon))
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
        @else
            {{ mb_strtoupper(mb_substr(trim((string) ($name ?? '')), 0, 1)) }}
        @endif
    </span>
@endif
