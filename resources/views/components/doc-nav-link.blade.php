{{--
    A link in the "On this page" rail.

    The 180-character Tailwind class string that used to be repeated on 280 of
    these now lives in the .doc-nav-link / .doc-toc-link rules in docs.css.

    `search` emits data-search, which the API reference uses to filter its
    endpoint list in place. `sub` indents a second-level entry.
--}}
@props(['href', 'search' => null, 'sub' => false])

<a href="{{ $href }}"
   @if ($search) data-search="{{ $search }}" @endif
   {{ $attributes->class(['doc-nav-link', 'doc-toc-link', 'doc-toc-link--sub' => $sub]) }}>
    {{ $slot }}
</a>
