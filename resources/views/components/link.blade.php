@props(['href', 'target', 'hideIcon' => false])

@php
    $isExternal = ($target ?? $attributes->get('target')) === '_blank';
    $showIcon = $isExternal && !$hideIcon;
    // Check if user provided a text color class
    $userClasses = $attributes->get('class', '');
    $hasTextColor = preg_match('/\btext-(white|black|gray|red|blue|green|yellow|purple|pink|indigo|orange)-\d+\b/', $userClasses) || 
                    preg_match('/\btext-(white|black|gray|red|blue|green|yellow|purple|pink|indigo|orange)\b/', $userClasses);
    $baseClasses = $hasTextColor ? 'hover:underline' : 'text-blue-600 dark:text-blue-400 hover:underline';
    // Check if user provided a display class (block, inline-block, etc.)
    $hasDisplayClass = $attributes->has('class') && preg_match('/\b(block|inline-block|flex|inline-flex)\b/', $attributes->get('class'));
    $classes = $hasDisplayClass ? $baseClasses : $baseClasses . ' inline-flex items-center';
@endphp

<a 
    href="{{ $href ?? $attributes->get('href') }}" 
    {{ $attributes->except(['href', 'target'])->merge(['class' => $classes]) }}
    @if($target ?? $attributes->get('target'))
        target="{{ $target ?? $attributes->get('target') }}"
    @endif
>
    @if($hasDisplayClass)
        {{ $slot }}
        @if($showIcon)
            <svg class="ml-1 h-3 w-3 flex-shrink-0 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        @endif
    @else
        <span class="whitespace-normal">{{ $slot }}</span>
        @if($showIcon)
            <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        @endif
    @endif
</a>

