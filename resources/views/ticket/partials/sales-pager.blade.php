{{-- The Sales list's pager (the 2026-11-24 design): "Showing 1 to 20 of 72 results" on the left, and
     on the right ← 1 2 3 4 → on one bordered track, the page you are on a grey key. Passed to
     $sales->links() by ticket/sales_table, so Laravel's own Tailwind pager, which other lists
     still use, is untouched. The arrows point the way the pages run, so they turn round in a
     right-to-left page. --}}
@if ($paginator->hasPages())
<nav class="sales-pager" aria-label="{{ __('messages.pager_label') }}">
    <p class="sales-pager-count">{{ __('messages.pager_showing', ['first' => number_format((int) $paginator->firstItem()), 'last' => number_format((int) $paginator->lastItem()), 'total' => number_format($paginator->total())]) }}</p>
    <div class="sales-pager-track">
        @if ($paginator->onFirstPage())
        <span class="sales-pager-key is-arrow is-off" aria-disabled="true" aria-label="{{ __('messages.pager_previous_page') }}"><span class="sales-pager-arrow" aria-hidden="true">&larr;</span></span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="sales-pager-key is-arrow" aria-label="{{ __('messages.pager_previous_page') }}"><span class="sales-pager-arrow" aria-hidden="true">&larr;</span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
            <span class="sales-pager-key is-gap" aria-hidden="true">&hellip;</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                    <span class="sales-pager-key is-on" aria-current="page">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="sales-pager-key" aria-label="{{ __('messages.pager_page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="sales-pager-key is-arrow" aria-label="{{ __('messages.pager_next_page') }}"><span class="sales-pager-arrow" aria-hidden="true">&rarr;</span></a>
        @else
        <span class="sales-pager-key is-arrow is-off" aria-disabled="true" aria-label="{{ __('messages.pager_next_page') }}"><span class="sales-pager-arrow" aria-hidden="true">&rarr;</span></span>
        @endif
    </div>
</nav>
@endif
