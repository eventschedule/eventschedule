{{-- A section heading of the guest kit. An h2 unless told otherwise: the page's one h1 is the
     schedule's or the event's own name. --}}
@props([
    'level' => 2,
    'large' => false,
])
@php $tag = 'h'.max(1, min(6, (int) $level)); @endphp
<{{ $tag }} {{ $attributes->merge(['class' => 'gk-h'.($large ? ' gk-h-lg' : '')]) }}>{{ $slot }}</{{ $tag }}>
