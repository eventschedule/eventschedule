{{--
    The blog's page control: pills in the house style. The framework's default was a squared strip
    whose current page was marked by a shade of grey, with a "Showing 13 to 24 of 223" line that
    disagreed with the count beside the search box (the lead post is not in the paged list).

    Every href is the paginator's own, so rel=prev, rel=next and these links stay byte-identical.
--}}
@if ($paginator->hasPages())
    <nav class="blog-pager" role="navigation" aria-label="Pages">
        @if ($paginator->onFirstPage())
            <span class="blog-page is-off" aria-disabled="true">Newer</span>
        @else
            <a class="blog-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">Newer</a>
        @endif

        <span class="blog-pager-count">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        <span class="blog-pager-pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="blog-page is-gap" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="blog-page is-on" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="blog-page" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>

        @if ($paginator->hasMorePages())
            <a class="blog-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Older</a>
        @else
            <span class="blog-page is-off" aria-disabled="true">Older</span>
        @endif
    </nav>
@endif
