{{--
    Previous / next within the page's own group.

    A slim bar rather than two chunky cards: it shares the page tail with
    #see-also and the CTA panel, and only one of the three should carry weight.

    Selfhost, SaaS and Developer pages get this for the first time - the old
    hardcoded chain in the controller covered only the User Guide.
--}}
@props(['prev' => null, 'next' => null])

@if ($prev || $next)
    <nav class="doc-pager" aria-label="Documentation pages">
        @if ($prev)
            <a href="{{ route($prev['route']) }}" class="doc-pager-item">
                <span class="doc-pager-label">
                    <svg class="h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </span>
                <span class="doc-pager-title">{{ $prev['title'] }}</span>
            </a>
        @else
            <span class="doc-pager-spacer"></span>
        @endif

        @if ($next)
            <a href="{{ route($next['route']) }}" class="doc-pager-item doc-pager-item--next">
                <span class="doc-pager-label">
                    <svg class="h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    Next
                </span>
                <span class="doc-pager-title">{{ $next['title'] }}</span>
            </a>
        @else
            <span class="doc-pager-spacer"></span>
        @endif
    </nav>
@endif
