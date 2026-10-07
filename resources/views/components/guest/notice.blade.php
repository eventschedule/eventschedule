{{-- A notice of the guest kit: something the visitor is told. A tinted ground and a full
     hairline, never a stripe down one side.

     tone: ok, warn, bad, or none for plain information.
     live: say it to a screen reader when it appears, for a notice that ANSWERS something the
     visitor just did (an alert when it is bad news, a status otherwise). Off by default: a notice
     that is simply part of the page is read in its place, like any other text.
     The actions slot sits at the end of the line: a Try again, a Close. --}}
@props([
    'tone' => null,
    'live' => false,
    'actions' => null,
])
@php
    $tone = in_array($tone, ['ok', 'warn', 'bad'], true) ? $tone : null;
    $paths = [
        'ok' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'warn' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'bad' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
    ];
    $path = $paths[$tone] ?? 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
@endphp
<div @if ($live) role="{{ $tone === 'bad' ? 'alert' : 'status' }}" @endif {{ $attributes->merge(['class' => 'gk-note'.($tone ? ' gk-note-'.$tone : '')]) }}>
    <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
    </svg>
    <div class="gk-note-body">{{ $slot }}</div>
    @if ($actions)
        {{ $actions }}
    @endif
</div>
