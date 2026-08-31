@php
    // $item, $styles and $icons are provided by the parent needs-attention partial.
    // $dismissRoute is passed only by callers that opt in - today just the dashboard's
    // Next steps panel - so the to-do queue and the admin dashboard render no control.
    $style = $styles[$item['color']] ?? $styles['blue'];
    $dismissRoute = $dismissRoute ?? null;
    $dismissible = $dismissRoute && ! empty($item['dismiss_schedule']);
@endphp
{{-- A form cannot be nested inside an anchor - the browser re-parents it and the row breaks -
     so the row is a wrapper with the link and the dismiss form as siblings. With no form the
     anchor is flex-1 and fills the wrapper, and it keeps px-5, literally the padding class the
     row carried before, so the to-do queue and the admin dashboard lay out unchanged. Deliberately
     NOT a logical pe-5 there: nothing else in the repo uses that class, so it is absent from the
     built bundle and those two rows would lose their end padding until the next npm run build.

     One thing DID change for all three callers: the anchor now carries a focus ring. It had no
     focus styling at all before, so this is an improvement, but it is not "unchanged". --}}
<div class="group flex items-stretch hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200">
    <a href="{{ $item['url'] }}"
        class="flex min-w-0 flex-1 items-center gap-3 py-3 {{ $dismissible ? 'ps-5 pe-2' : 'px-5' }} focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]">
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

    @if ($dismissible)
        {{-- Named per row: a screen reader listing eight buttons all called "Dismiss" cannot
             tell them apart, so the label carries the schedule name.

             One shade darker than the chevron it sits beside, in both themes. The chevron is
             decorative and aria-hidden, so it is not held to a contrast floor; this is a real
             control and WCAG 1.4.11 wants 3:1. gray-400 measured 1.83:1 against the card in
             light mode - gray-500/gray-400 measures 3.51 light and 4.65 dark. --}}
        <form method="POST" action="{{ $dismissRoute }}" class="flex items-center ps-1 pe-3">
            @csrf
            <input type="hidden" name="schedule" value="{{ $item['dismiss_schedule'] }}">
            <input type="hidden" name="type" value="{{ $item['type'] }}">
            <button type="submit"
                title="{{ __('messages.next_step_dismiss', ['schedule' => $item['subtitle']]) }}"
                aria-label="{{ __('messages.next_step_dismiss', ['schedule' => $item['subtitle']]) }}"
                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-200/70 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </form>
    @endif
</div>
