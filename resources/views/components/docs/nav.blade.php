{{--
    The persistent left rail: every doc page, grouped by section, rendered from
    config/docs.php.

    Deliberately NOT an accordion. Collapsing hides exactly the cross-section
    discoverability a persistent nav exists to provide, and puts a click in
    front of every jump between groups. 41 links under sticky group headers in
    a self-scrolling rail is the standard shape (Stripe, Tailwind) and it fits.
    The accordion survives only in the right-hand "On this page" rail, where it
    maps to page structure.

    Active state is resolved server-side from the current route, so there is no
    JS flash and it still works with JavaScript off.
--}}
@props(['current' => null, 'searchFirst' => true])

<nav class="doc-nav" aria-label="Documentation">
    {{-- searchFirst is false on the reference layout, where the page TOC is
         rendered above this and the search would otherwise split it from the
         site nav. --}}
    <div class="{{ $searchFirst ? 'mb-4' : 'mb-4 mt-1' }}">
        <x-docs.search variant="rail" />
    </div>

    @foreach (\App\Utils\DocsUtils::groups() as $groupKey => $group)
        @php
            $pages = \App\Utils\DocsUtils::pagesInGroup($groupKey);
            $isCurrentGroup = $current && ($current['group'] ?? null) === $groupKey;
        @endphp

        @if (count($pages))
            <div @class(['doc-nav-group', 'is-current' => $isCurrentGroup])>
                @if (! empty($group['index_route']))
                    <a href="{{ route($group['index_route']) }}" class="doc-nav-group-header">{{ $group['title'] }}</a>
                @else
                    <span class="doc-nav-group-header">{{ $group['title'] }}</span>
                @endif

                <div>
                    @foreach ($pages as $page)
                        @php $isCurrent = $current && $page['key'] === $current['key']; @endphp

                        <a href="{{ route($page['route']) }}"
                           class="doc-nav-link"
                           @if ($isCurrent) aria-current="page" @endif>
                            {{ \App\Utils\DocsUtils::navTitle($page) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</nav>
