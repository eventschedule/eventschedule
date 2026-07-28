{{--
    A collapsible group in the "On this page" rail.

    Two shapes, matching what the docs already used: with `href` the header is
    itself a link and only the chevron toggles; without one it is a plain
    toggle button (the API reference's grouping headers are not navigable).

    `expanded` opens it on load. The scroll-spy in docs.js opens whichever
    group holds the active section, scoped to this rail so it can never touch
    the left-hand page nav.
--}}
@props(['label', 'href' => null, 'expanded' => false, 'search' => null])

@php
    $chevron = '<svg class="doc-nav-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>';
@endphp

<div {{ $attributes->class(['doc-nav-group', 'expanded' => $expanded]) }}>
    @if ($href)
        <a href="{{ $href }}"
           class="doc-nav-group-header doc-nav-link doc-toc-link"
           @if ($search) data-search="{{ $search }}" @endif>
            {{ $label }}
            {!! $chevron !!}
        </a>
    @else
        <button type="button" class="doc-nav-group-header" aria-expanded="{{ $expanded ? 'true' : 'false' }}">
            {{ $label }}
            {!! $chevron !!}
        </button>
    @endif

    <div class="doc-nav-group-items">
        <div>{{ $slot }}</div>
    </div>
</div>
