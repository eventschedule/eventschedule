{{-- A ranked list: one row a thing, its number at the end, and a wash behind the row as long as
     the number is against the largest. The same rows the Realtime tab draws, so the four tabs read
     alike. Every value is printed, so nothing waits on a hover, and a list of one is still a
     list, not a fat bar on an axis that runs to 500.

     Given, always all of them (an include inherits its parent's variables, so a name left out
     would be whatever the parent happened to hold):
       listRows  [['label', 'value' (a number, for the wash), 'display' (the printed figure),
                  'href' (opens a page or filters this one), 'title', 'thumb' (an image),
                  'flag' (a country code), 'icon' (a stroked path), 'brand' (a filled path),
                  'sub', 'mono'], ...]
       listShare  print each row's share of the whole beside its figure
       listBars   draw the washes (not across currencies, where lengths would compare euros
                  with dollars)
     and, only where it applies, listMax (a fixed scale: a rate is against 100, not against
     the largest rate) and listTotal (the whole a share is of, when the list is not all of it).

     Six rows show; the rest wait behind "Show all", in the page and not behind a request. --}}
@php
    $listVisible = 6;
    $listPeak = ($listMax ?? null) ?: max(1, ...array_map(fn ($r) => (float) $r['value'], $listRows ?: [['value' => 0]]));
    $listSum = ($listTotal ?? null) ?: (array_sum(array_map(fn ($r) => (float) $r['value'], $listRows)) ?: 1);
@endphp
<ol class="an-rows" role="list">
    @foreach ($listRows as $i => $row)
    @php $rowTag = ! empty($row['href']) ? 'a' : 'div'; @endphp
    <li class="an-row" @if ($i >= $listVisible) hidden data-an-extra @endif>
        <{{ $rowTag }} class="an-row-in" @if (! empty($row['href'])) href="{{ $row['href'] }}" @endif @if (! empty($row['title'])) title="{{ $row['title'] }}" @endif>
            {{-- The wash is the track's, and the track stops short of the figures, so a long bar
                 never runs under a number and a short one still clears the picture. --}}
            <span class="an-row-track" @if ($listBars) style="--w: {{ round(max(0, $row['value'] / $listPeak * 100), 1) }}%" @endif>
                @if (! empty($row['thumb']))
                    <img src="{{ $row['thumb'] }}" alt="" loading="lazy" class="an-row-thumb">
                @elseif (! empty($row['flag']))
                    <span class="an-row-flag"><span class="iti__flag iti__{{ strtolower($row['flag']) }}" aria-hidden="true"></span></span>
                @elseif (! empty($row['brand']))
                    <span class="an-row-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="{{ $row['brand'] }}" /></svg></span>
                @elseif (! empty($row['icon']))
                    <span class="an-row-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $row['icon'] }}" /></svg></span>
                @endif
                <span class="an-row-label{{ ! empty($row['mono']) ? ' is-mono' : '' }}" v-pre><bdi>{{ $row['label'] }}</bdi>@if (! empty($row['sub']))<span class="an-row-sub"><bdi>{{ $row['sub'] }}</bdi></span>@endif</span>
                @if (! empty($row['href']))
                    <svg class="an-row-go" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
                @endif
            </span>
            <span class="an-row-value">{{ $row['display'] ?? number_format($row['value']) }}</span>
            @if ($listShare)
                <span class="an-row-share">{{ round($row['value'] / $listSum * 100) }}%</span>
            @endif
        </{{ $rowTag }}>
    </li>
    @endforeach
</ol>
@if (count($listRows) > $listVisible)
<button type="button" class="an-more" data-an-more aria-expanded="false" data-less="{{ __('messages.realtime_show_less') }}">{{ __('messages.realtime_show_all') }} <span class="an-more-count">{{ number_format(count($listRows)) }}</span></button>
@endif
