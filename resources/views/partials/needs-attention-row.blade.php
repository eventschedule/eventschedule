@php
    // $item, $styles and $icons are provided by the parent needs-attention partial.
    $style = $styles[$item['color']] ?? $styles['blue'];
@endphp
<a href="{{ $item['url'] }}"
    class="group flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200">
    <span class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $style['bg'] }}">
        <svg class="w-5 h-5 {{ $style['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            {!! $icons[$item['type']] ?? '' !!}
        </svg>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['title'] }}</span>
        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate" dir="auto">{{ $item['subtitle'] }}</span>
    </span>
    <svg class="flex-shrink-0 w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors {{ is_rtl() ? 'rotate-180' : '' }}"
        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
    </svg>
</a>
