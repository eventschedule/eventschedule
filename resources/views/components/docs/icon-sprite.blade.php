{{--
    Inline SVG sprite for the icons the search results reference with
    <use href="#docs-icon-...">. Emitted once per docs page.

    Only the icons actually used by the manifest are included, so this stays
    small. <use> is same-document, so there is no CSP consideration.
--}}
<svg width="0" height="0" aria-hidden="true" focusable="false"
     style="position:absolute;width:0;height:0;overflow:hidden">
    @foreach (\App\Utils\DocsUtils::iconKeys() as $key)
        <symbol id="docs-icon-{{ $key }}" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
            @foreach (\App\Utils\DocIcons::paths($key) as $d)
                <path d="{{ $d }}" />
            @endforeach
        </symbol>
    @endforeach
</svg>
