@props(['disabled' => false, 'size' => null])

@php
    // size="sm" is a row's own action inside a form (add this participant, accept these parts),
    // where the page's Save is the full-size one. Same gradient, focus ring and disabled state.
    // min-w-0 / min-h-0 because a form may size its text buttons (the event form does).
    $sizing = $size === 'sm' ? 'min-w-0 min-h-0 px-3 py-1.5 text-sm' : 'px-4 py-3 text-base';
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center '.$sizing.' bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-white shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm']) }} @disabled($disabled)>
    {{ $slot }}
</button>
