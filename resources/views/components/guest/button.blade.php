{{-- A button of the guest kit (partials/guest-kit-styles). One primary per view: it is the
     schedule's colour. With href it is a link that looks like a button; without, a <button
     type="button"> unless told otherwise, so one dropped inside a form never submits it by
     accident. --}}
@props([
    'variant' => 'primary',
    'size' => null,
    'block' => false,
    'icon' => false,
    'href' => null,
    'type' => 'button',
])
@php
    $classes = 'gk-btn gk-btn-'.(in_array($variant, ['primary', 'secondary', 'quiet'], true) ? $variant : 'primary')
        .(in_array($size, ['lg', 'sm'], true) ? ' gk-btn-'.$size : '')
        .($block ? ' gk-btn-block' : '')
        .($icon ? ' gk-btn-icon' : '');
@endphp
@if ($href !== null)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
