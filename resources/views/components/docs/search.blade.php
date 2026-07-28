{{--
    Docs search. One component, two sizes: `hero` on the /docs landing page,
    where it is the primary control, and `rail` on every leaf page.

    Vanilla JS (docs.js picks up [data-docs-search]), not Alpine and not an
    in-DOM Vue template: the marketing bundle exposes Vue's runtime-only build,
    so an in-DOM template would render empty.

    The 364-entry index is ~97 KB raw / ~18 KB gzipped, so it is fetched from a
    cached endpoint on first focus rather than inlined into all 41 doc pages.
--}}
@props(['variant' => 'rail'])

@php
    $hero = $variant === 'hero';

    $inputClasses = $hero
        ? 'h-14 w-full rounded-2xl border border-gray-200 bg-white/90 ps-12 pe-24 text-base shadow-lg shadow-blue-500/5 backdrop-blur dark:border-white/10 dark:bg-white/[0.06]'
        : 'h-9 w-full rounded-lg border border-gray-200 bg-white ps-9 pe-9 text-sm dark:border-white/10 dark:bg-white/5';
@endphp

<div class="relative" data-docs-search="{{ route('marketing.docs.search_index') }}">
    <label for="docs-search-{{ $variant }}" class="sr-only">Search the documentation</label>

    <svg class="pointer-events-none absolute inset-y-0 start-3 my-auto {{ $hero ? 'h-5 w-5 start-4' : 'h-4 w-4' }} text-gray-400"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>

    <input id="docs-search-{{ $variant }}"
           type="search"
           data-role="input"
           role="combobox"
           aria-expanded="false"
           aria-autocomplete="list"
           aria-controls="docs-search-results-{{ $variant }}"
           autocomplete="off"
           spellcheck="false"
           placeholder="{{ $hero ? 'Search guides, settings and API endpoints...' : 'Search docs...' }}"
           class="{{ $inputClasses }} text-gray-900 placeholder:text-gray-400 focus:border-[var(--brand-blue)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]/25 dark:text-white">

    <kbd data-role="hint"
         class="pointer-events-none absolute inset-y-0 {{ $hero ? 'end-4' : 'end-2.5' }} my-auto flex {{ $hero ? 'h-6' : 'h-5' }} items-center rounded border border-gray-200 px-1.5 font-mono text-[10px] text-gray-400 dark:border-white/15">/</kbd>

    <button type="button"
            data-role="clear"
            hidden
            aria-label="Clear search"
            class="absolute inset-y-0 {{ $hero ? 'end-4' : 'end-2' }} my-auto flex h-6 w-6 items-center justify-center rounded text-lg leading-none text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>

    <div id="docs-search-results-{{ $variant }}"
         data-role="results"
         role="listbox"
         hidden
         class="absolute z-50 mt-2 max-h-[400px] w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-[#15151c] {{ $hero ? 'sm:w-[32rem] left-1/2 -translate-x-1/2' : '' }}"></div>
</div>
