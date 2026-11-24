@props([
    // warn (something to know before going on), error (something went wrong), info, success.
    'tone' => 'warn',
    'title' => null,
])

{{-- A notice on a page of the admin portal: a tinted panel with a full 1px border and an icon,
     never coloured text on its own and never a stripe down one side (CLAUDE.md). The slot is the
     message; `action` is one link or button at the end of it.

     It carries no live-region role of its own: most notices are part of the page (the Queue's
     health panel, a plan note) and would be read out as an alert on every load. A notice that
     answers what somebody just did is x-page-flash, which says so. --}}
@php
    $tones = [
        'warn' => ['bg-amber-100/80 dark:bg-amber-900/25 border-transparent', 'text-amber-700 dark:text-amber-400', 'text-gray-900 dark:text-amber-100', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
        'error' => ['bg-red-50 dark:bg-red-900/20 border-transparent', 'text-red-600 dark:text-red-400', 'text-red-800 dark:text-red-200', 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
        'info' => ['bg-[var(--brand-blue-a20)] dark:bg-blue-900/20 border-transparent', 'text-blue-600 dark:text-blue-400', 'text-blue-800 dark:text-blue-200', 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'],
        'success' => ['bg-green-50 dark:bg-green-900/20 border-transparent', 'text-green-600 dark:text-green-400', 'text-green-800 dark:text-green-200', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    [$box, $iconInk, $ink, $path] = $tones[$tone] ?? $tones['warn'];
@endphp

{{-- 2026-11 redesign: a band in the tone's colour with its message and, at the end, its one
     action; the warning is said by the colour and the words, so the icon is left to the other
     tones. --}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 gap-y-1 border rounded-2xl px-4 sm:px-5 py-3.5 '.$box]) }}>
    @if ($tone === 'error')
    <svg class="w-5 h-5 flex-shrink-0 {{ $iconInk }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
    </svg>
    @endif
    <div class="min-w-0 flex-1 text-[0.9375rem] {{ $ink }}"><div class="page-notice-text">
        @if ($title)
        <p class="font-semibold" v-pre>{{ $title }}</p>
        @endif
        {{ $slot }}
    </div></div>
    @if (isset($action) && trim((string) $action) !== '')
    <div class="flex-shrink-0 text-sm font-semibold page-notice-action">{{ $action }}</div>
    @endif
</div>
