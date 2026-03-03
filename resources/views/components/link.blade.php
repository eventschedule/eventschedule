@props(['href', 'target', 'hideIcon' => false, 'nofollow' => false])

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
    @if($isExternal)
        rel="noopener noreferrer{{ $nofollow ? ' nofollow' : '' }}"
    @endif
>
    @if($hasDisplayClass)
        {{ $slot }}
        @if($showIcon)
            <svg class="ml-1 h-3 w-3 flex-shrink-0 inline" viewBox="0 0 20 20" fill="currentColor" stroke="currentColor" stroke-width="0.5" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
            </svg>
        @endif
    @else
        <span class="whitespace-normal">{{ $slot }}</span>
        @if($showIcon)
            <svg class="ml-1 h-3 w-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" stroke="currentColor" stroke-width="0.5" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
            </svg>
        @endif
    @endif
</a>

