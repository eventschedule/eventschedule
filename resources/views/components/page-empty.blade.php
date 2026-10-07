@props([
    'title',
    // What would be here, and what makes it appear.
    'text' => null,
    // The `d` of an outline icon (24x24, stroke), or several of them.
    'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
    // Inside a card that already has a head, the empty state is quieter: no icon, less room.
    'compact' => false,
])

{{-- Nothing here yet (partials/admin-page-styles, .page-empty): what would be here, and the one
     thing to do about it, which goes in the slot as buttons. The portal had four looks for this. --}}
<div {{ $attributes->merge(['class' => 'page-empty'.($compact ? ' is-compact' : '')]) }}>
    @unless ($compact)
    <svg class="page-empty-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        @foreach ((array) $icon as $iconPath)
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
        @endforeach
    </svg>
    @endunless
    <h3 v-pre>{{ $title }}</h3>
    @if ($text)
    <p v-pre>{{ $text }}</p>
    @endif
    @if (trim((string) $slot) !== '')
    <div class="page-actions">{{ $slot }}</div>
    @endif
</div>
