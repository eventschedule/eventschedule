{{-- Facebook/Instagram ad preview mockup --}}
<div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden max-w-sm mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-2 p-3">
        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">ES</div>
        <div>
            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ config('app.name') }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ __('messages.sponsored') }}</p>
        </div>
    </div>

    {{-- Text --}}
    <div class="px-3 pb-2">
        <p class="text-sm text-gray-900 dark:text-white">{{ $primaryText }}</p>
    </div>

    {{-- Image --}}
    @if ($imageUrl)
    <div class="aspect-square bg-gray-100 dark:bg-gray-700">
        <img src="{{ $imageUrl }}" alt="Ad preview" class="w-full h-full object-cover">
    </div>
    @else
    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-300 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M21,3H3C2,3 1,4 1,5V19A2,2 0 0,0 3,21H21C22,21 23,20 23,19V5C23,4 22,3 21,3M5,17L8.5,12.5L11,15.5L14.5,11L19,17H5Z"/>
        </svg>
    </div>
    @endif

    {{-- CTA bar --}}
    <div class="p-3 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase truncate">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $headline }}</p>
        </div>
        <span class="ml-3 flex-shrink-0 inline-flex items-center px-3 py-1.5 rounded text-xs font-semibold bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white">
            {{ str_replace('_', ' ', $cta) }}
        </span>
    </div>
</div>
