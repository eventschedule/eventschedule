@props([
    'label' => '',
    'color' => null,
    'size' => 'md',
    'padding' => 'p-6',
])

@php
    $colorClasses = match($color) {
        'green' => 'text-green-600 dark:text-green-400',
        'red' => 'text-red-600 dark:text-red-400',
        'amber' => 'text-amber-600 dark:text-amber-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        default => 'text-gray-900 dark:text-white',
    };
    $sizeClass = $size === 'lg' ? 'text-3xl' : 'text-2xl';
@endphp

<div {{ $attributes->merge(['class' => "ap-card rounded-xl shadow $padding"]) }}>
    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 text-start">{{ $label }}</p>
    <p class="mt-2 {{ $sizeClass }} font-bold text-center {{ $colorClasses }}">{{ $slot }}</p>
    @isset($subtitle)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-center">{{ $subtitle }}</p>
    @endisset
</div>
