{{-- The ad as it will look in a Facebook or Instagram feed: who it is from, the text, the picture,
     and the bar with the headline and the button. A picture of somebody else's page, so it keeps
     its own proportions; its surfaces and ink are the portal's, so it sits in either theme. --}}
@php
    // The button's words in the reader's language. They were printed as the constant Meta is sent
    // ("GET TICKETS"), in English whatever the language.
    $ctaLabels = [
        'LEARN_MORE' => __('messages.cta_learn_more'),
        'GET_TICKETS' => __('messages.cta_get_tickets'),
        'SIGN_UP' => __('messages.cta_sign_up'),
        'BOOK_TRAVEL' => __('messages.cta_book_now'),
    ];
@endphp
<div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden max-w-sm mx-auto">
    {{-- Header --}}
    <div class="flex items-center gap-2 p-3">
        <div class="w-8 h-8 rounded-full bg-[var(--brand-button-bg)] flex items-center justify-center text-white text-xs font-bold" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(config('app.name'), 0, 1)) }}</div>
        <div>
            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ config('app.name') }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ __('messages.sponsored') }}</p>
        </div>
    </div>

    {{-- Text --}}
    <div class="px-3 pb-2">
        <p class="text-sm text-gray-900 dark:text-white"><bdi>{{ $primaryText }}</bdi></p>
    </div>

    {{-- Image --}}
    @if ($imageUrl)
    <div class="bg-gray-100 dark:bg-gray-700 overflow-hidden">
        <img src="{{ $imageUrl }}" alt="" class="w-full h-auto">
    </div>
    @else
    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
    </div>
    @endif

    {{-- CTA bar --}}
    <div class="p-3 bg-gray-50 dark:bg-gray-700 flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase truncate" dir="ltr">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><bdi>{{ $headline }}</bdi></p>
        </div>
        <span class="flex-shrink-0 inline-flex items-center px-3 py-1.5 rounded text-xs font-semibold bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white">
            {{ $ctaLabels[$cta] ?? \Illuminate\Support\Str::headline(strtolower((string) $cta)) }}
        </span>
    </div>
</div>
