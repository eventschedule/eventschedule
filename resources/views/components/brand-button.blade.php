@props(['disabled' => false, 'size' => null])

@php
    // size="sm" is a row's own action inside a form (add this participant, accept these parts),
    // where the page's Save is the full-size one. Same colour, focus ring and disabled state.
    // min-w-0 because a form may size its text buttons (the event form does). The heights are
    // the design-language review's (resources/css/app.css): .ap-btn 52px for the page's main
    // button, .ap-btn-sm 44px, the least for anything pressed.
    $sizing = $size === 'sm' ? 'ap-btn-sm min-w-0 px-3 py-1.5 text-sm' : 'ap-btn px-4 py-3 text-base';
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center '.$sizing.' bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-xl font-semibold text-white shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg-hover)] hover:to-[var(--brand-button-bg-hover)] hover:shadow-md focus:outline-none disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm']) }} @disabled($disabled)>
    {{ $slot }}
</button>
