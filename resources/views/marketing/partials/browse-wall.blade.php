{{-- A wall of posters: App\Utils\BrowseWall decides the tiles and App\Utils\PosterWall where each
     row ends. Every value here is somebody else's text (an event's name, a schedule's, a city),
     escaped by Blade. The page is deliberately NOT a Vue mount: if that ever changes, these need
     the user-text component, because Vue would compile a name holding a mustache as a template. --}}
@php
    $grid = $grid ?? false;
    $profiles = $grid ? [] : array_keys(\App\Utils\PosterWall::PROFILES);
    // The wall's width as a share of the window at each breakpoint, for each picture's `sizes`.
    $spans = ['x' => ['(min-width: 1920px)', 96], 'd' => ['(min-width: 1440px)', 95], 'l' => ['(min-width: 1024px)', 93], 't' => ['(min-width: 640px)', 94], 'm' => ['', 92]];
    $count = count($tiles);
    // The first row, whichever breakpoint is widest: these are on screen as the page opens, so
    // their pictures are not left to load lazily and the first is asked for ahead of the rest.
    $opening = $grid ? -1 : max(array_map(fn ($profile) => $rows[$profile]['ends'][0] ?? -1, $profiles));
    $first = true;
    // A name is one level under whatever heads it: a stretch of time on the wall, the section
    // itself in an even grid.
    $nameTag = $grid ? 'h3' : 'h4';
@endphp
{{-- An even grid never has more columns than it has pictures, so a short list still ends flush. --}}
<div class="{{ $grid ? 'bw-grid' : 'bw-wall' }}"@if ($grid) style="--n4:{{ in_array($count, [3, 5, 6, 9], true) ? 3 : 4 }};--n6:{{ max(4, min(6, $count)) }};--n8:{{ max(6, min(8, $count)) }}"@endif>
    @foreach ($tiles as $index => $tile)
        @php
            // An even grid shows every picture inside one square, on its own colours.
            $fit = $grid || ! ($tile['exact'] ?? false);
            $sizes = $grid
                ? '(min-width: 1280px) 15vw, (min-width: 1024px) 23vw, (min-width: 640px) 31vw, 46vw'
                : collect($spans)
                    ->map(fn ($span, $profile) => trim($span[0].' '.max(10, (int) ceil(($rows[$profile]['share'][$index] ?? 1) * $span[1])).'vw'))
                    ->implode(', ');
            $eager = $index <= $opening;
            // Alone in its row on a phone, a tall narrow poster would be taller than the screen:
            // there a flyer is fitted in a 4:5 box, like a picture of unknown shape, and a poster
            // set in type, which has no shape to keep, is set square.
            $lone = ! $grid && in_array($tile['kind'] ?? '', ['flyer', 'type'], true) && ($tile['ratio'] ?? 1) < 0.78
                && in_array($index, $rows['m']['ends'], true)
                && ($index === 0 || in_array($index - 1, $rows['m']['ends'], true));
            // The posters that are there as the page opens arrive one after another.
            $arrive = $index <= $opening + 8 && ! $grid ? 'bw-in' : '';
        @endphp

        @if ($tile['kind'] === 'label')
            @php
                // Mid-row, a label stands a little off the posters before it, which are another
                // stretch of time's.
                $mid = collect($profiles)
                    ->filter(fn ($profile) => $index > 0 && ! in_array($index - 1, $rows[$profile]['ends'], true))
                    ->map(fn ($profile) => 'bw-mid-'.$profile)
                    ->implode(' ');
            @endphp
            <div class="bw-tile bw-label {{ $mid }} {{ $arrive }}" id="when-{{ $tile['band'] }}" style="--i:{{ $index }}">
                <div class="bw-label-in">
                    <h3>{{ $tile['title'] }}</h3>
                    <p>{{ $tile['count'] }} {{ $tile['count'] === 1 ? 'event' : 'events' }}</p>
                </div>
            </div>
        @elseif ($tile['kind'] === 'blank')
            @php
                // Its shape at each breakpoint, as the row it ends up in needs.
                $shapes = collect($profiles)->map(fn ($profile) => '--r'.$profile.':'.($rows[$profile]['ratio'][$index] ?? $tile['ratio']))->implode(';');
            @endphp
            <div class="bw-tile bw-poster bw-blank {{ $arrive }}" style="--r:{{ $tile['ratio'] }};{{ $shapes }};--i:{{ $index }}">
                <a href="{{ app_url('/sign_up') }}" class="bw-frame">
                    <span class="bw-clip bw-blank-face">
                        <span class="bw-blank-k">This space is free</span>
                        <span class="bw-blank-t">Your event here</span>
                        <span class="bw-blank-go">
                            Put yours up
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </span>
                </a>
            </div>
        @else
            <article class="bw-tile bw-poster bw-{{ $tile['kind'] }} {{ $fit ? 'bw-fit' : '' }} {{ $lone ? 'bw-lone' : '' }} {{ ! empty($tile['hidden']) ? 'is-hidden' : '' }} {{ $arrive }}" style="--r:{{ $grid ? 1 : $tile['ratio'] }};--i:{{ $index }}">
                {{-- The whole poster is the link, and the first thing in the tile for a keyboard.
                     A remote one goes straight to its origin and is deliberately followable: the
                     backlink is what sharing here is for. --}}
                @if (! empty($tile['remote']))
                    <a href="{{ $tile['url'] }}" target="_blank" rel="noopener" data-federated-click="{{ $tile['hash'] }}" class="bw-link" aria-label="{{ $tile['name'] }}"></a>
                @else
                    <a href="{{ $tile['url'] }}" target="_blank" rel="noopener" class="bw-link" aria-label="{{ $tile['name'] }}"></a>
                @endif

                <div class="bw-frame">
                    @if ($tile['kind'] === 'type')
                        {{-- No flyer: the poster is set in type. Everything on it is said again,
                             to a screen reader, by the caption underneath. --}}
                        <div class="bw-clip bw-face bw-ink-{{ $tile['ink'] }}" aria-hidden="true">
                            <span class="bw-face-top">
                                @if (! empty($tile['stamp']))
                                    <span class="bw-stamp">
                                        <span class="bw-stamp-top">{{ $tile['stamp']['top'] }}</span>
                                        <span class="bw-stamp-num">{{ $tile['stamp']['num'] }}</span>
                                        <span class="bw-stamp-mon">{{ $tile['stamp']['mon'] }}@if ($tile['time']) <i>&middot;</i> {{ $tile['time'] }}@endif @if (! empty($tile['rhythm'])) <i>&middot;</i> {{ $tile['rhythm'] }}@endif</span>
                                    </span>
                                @else
                                    <span class="bw-stamp"><span class="bw-stamp-top">{{ $tile['when'] }}</span></span>
                                @endif
                                @if ($tile['image'])
                                    <img src="{{ $tile['image'] }}" alt="" width="56" height="56" loading="lazy" decoding="async">
                                @endif
                            </span>
                            <span class="bw-face-name bw-len-{{ $tile['long'] }}">{{ $tile['name'] }}</span>
                        </div>
                    @else
                        {{-- The same picture twice costs one request, as long as both ask for it the
                             same way (the same srcset and sizes): once as the poster, once out of
                             focus behind it, which is the light it throws on the wall. --}}
                        @php
                            $source = 'src="'.e($tile['image']).'"'.($tile['srcset'] ? ' srcset="'.e($tile['srcset']).'" sizes="'.e($sizes).'"' : '');
                            $loading = $eager ? 'loading="eager"'.($first ? ' fetchpriority="high"' : '') : 'loading="lazy"';
                            $first = $first && ! $eager;
                        @endphp
                        <img class="bw-glow" {!! $source !!} alt="" aria-hidden="true" {!! $eager ? 'loading="eager"' : 'loading="lazy"' !!} decoding="async">
                        <div class="bw-clip">
                            @if ($fit || $lone)
                                <img class="bw-fill" {!! $source !!} alt="" aria-hidden="true" {!! $eager ? 'loading="eager"' : 'loading="lazy"' !!} decoding="async">
                            @endif
                            <img class="bw-img" {!! $source !!} alt="{{ $tile['name'] }}" width="480" height="{{ (int) round(480 / $tile['ratio']) }}" {!! $loading !!} decoding="async">
                        </div>
                    @endif

                    @if (! empty($tile['live']))
                        <span class="bw-live"><i aria-hidden="true"></i>On now</span>
                    @endif
                    @if (! empty($tile['source']))
                        <span class="bw-source">{{ $tile['source'] }}</span>
                    @endif
                </div>

                <div class="bw-cap">
                    {{-- On a poster set in type the day and the name are already in front of the
                         reader, so here they are for a screen reader only. --}}
                    <p class="bw-when {{ $tile['kind'] === 'type' ? 'sr-only' : '' }}"><b>{{ $tile['when'] }}</b>@if ($tile['time']) <span>{{ $tile['time'] }}</span>@endif @if (! empty($tile['rhythm'])) <span>{{ $tile['rhythm'] }}</span>@endif</p>
                    <{{ $nameTag }} class="bw-name {{ $tile['kind'] === 'type' ? 'sr-only' : '' }}">{{ $tile['name'] }}</{{ $nameTag }}>
                    @if ($tile['place'] || $tile['country'])
                        <p class="bw-place">{{ $tile['place'] }}@if ($tile['place'] && $tile['country'])<span>, {{ $tile['country'] }}</span>@elseif ($tile['country']){{ $tile['country'] }}@endif</p>
                    @endif
                    @if ($tile['schedule'])
                        <p class="bw-who">
                            @if ($tile['scheduleUrl'])
                                <a href="{{ $tile['scheduleUrl'] }}" target="_blank" rel="noopener">{{ $tile['schedule'] }}</a>
                            @else
                                {{ $tile['schedule'] }}
                            @endif
                        </p>
                        @if ($tile['more'] && $tile['scheduleUrl'])
                            <p class="bw-more-of"><a href="{{ $tile['scheduleUrl'] }}" target="_blank" rel="noopener">More from them</a></p>
                        @endif
                    @endif

                    @if ($admin)
                        @if (! empty($tile['remote']))
                            <form method="POST" action="{{ route('marketing.federation.block', $tile['hash']) }}" class="bw-admin">
                                @csrf
                                <button type="submit" aria-label="Hide this listing from discovery">Hide</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('marketing.discovery.toggle', $tile['hash']) }}" class="bw-admin">
                                @csrf
                                @if ($tile['hidden'])
                                    <span>Hidden</span>
                                @endif
                                <button type="submit" class="{{ $tile['hidden'] ? 'is-restore' : '' }}" aria-label="{{ $tile['hidden'] ? 'Restore event to discovery' : 'Hide event from discovery' }}">{{ $tile['hidden'] ? 'Unhide' : 'Hide' }}</button>
                            </form>
                        @endif
                    @endif
                </div>

            </article>
        @endif

        @foreach ($profiles as $profile)
            @if ($index === $count - 1 && ($rows[$profile]['pad'] ?? 0) > 0)
                <i class="bw-pad bw-pad-{{ $profile }}" style="--p:{{ $rows[$profile]['pad'] }}" aria-hidden="true"></i>
            @endif
            @if (in_array($index, $rows[$profile]['ends'], true))
                <i class="bw-brk bw-brk-{{ $profile }}" aria-hidden="true"></i>
            @endif
        @endforeach
    @endforeach
</div>
