{{-- A chip of the guest kit: one fact about an event, read at a glance.
     tone: free (nothing to pay), few (running out), out (none left), accent, or none. --}}
@props([
    'tone' => null,
])
<span {{ $attributes->merge(['class' => 'gk-chip'.(in_array($tone, ['free', 'few', 'out', 'accent'], true) ? ' gk-chip-'.$tone : '')]) }}>{{ $slot }}</span>
