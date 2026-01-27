@props(['disabled' => false])

<x-text-input
    {{ $attributes->merge(['type' => 'password']) }}
    :disabled="$disabled"
/>
