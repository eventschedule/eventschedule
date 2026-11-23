{{-- The vendor map on a guest page: a band in the venue map's look (partials/guest-map-styles,
     .gk-map-band) with the kind's "At the market this week", a row of faces, "+12" and Open map;
     and on the map's event page ($vmGrid) the grid of who is there that date, in the rhythm of the
     Lineup section above it.

     $vmBand is App\Services\VendorMaps\VendorMapBand::forSchedule() or ::forEvent(): already only
     what is public (approved vendors who are there, a name only without a picture or a link), read
     in a fixed number of queries. Server-rendered, no script: a schedule's page is mounted by
     other apps, so every name is printed as text and inside <bdi>. --}}
@php
    $vmGrid = $vmGrid ?? false;
    $vmFaces = $vmBand['faces'];
    $vmRest = max(0, $vmBand['count'] - count($vmFaces));
    $vmRestNarrow = max(0, $vmBand['count'] - min(3, count($vmFaces)));
    $vmCompact = $vmBand['count'] > 8;
    $vmTitleId = 'gk-vm-band-title'.($vmGrid ? '-event' : '');
@endphp
@once('guest-map-styles')
    @include('partials.guest-map-styles')
@endonce
@once('vendor-map-band-styles')
<style {!! nonce_attr() !!}>
    .gk-vm-band .gk-map-band { position: relative; }
    .gk-vm-band .gk-map-open::after { content: ""; position: absolute; inset: 0; }
    .gk-vm-band .gk-map-open:focus-visible { outline: none; }
    .gk-vm-band .gk-map-band:has(.gk-map-open:focus-visible) { outline: 2px solid var(--gk-ink); outline-offset: -2px; border-radius: inherit; }
    .gk-vm-band .gk-map-stack .gk-vm-initial { display: grid; place-items: center; background: var(--gk-well); font-size: .6875rem; font-weight: 700; color: var(--gk-ink-2); }
    /* The narrow screen's own rule (guest-map-styles), at this rule's weight: fewer faces, so the
       title and "Open map" keep their room. */
    @media (max-width: 26rem) {
        .gk-vm-band .gk-map-stack .gk-vm-initial.gk-map-wide { display: none; }
        .gk-vm-band .gk-map-stack .gk-vm-initial.gk-map-narrow { display: grid; }
    }
    /* On a phone the faces take a row of their own under the title, so "At the market this week"
       and Open map keep their width (the venue map's band has one short word for a title). */
    @media (max-width: 40rem) {
        .gk-vm-band .gk-map-band { grid-template-columns: auto minmax(0, 1fr) auto; grid-template-areas: "icon title acts" "sub sub sub" "stack stack stack"; row-gap: .375rem; }
        .gk-vm-band .gk-map-stack { padding-inline-start: 0; }
    }
    .gk-vm-people { display: flex; flex-direction: column; gap: 1rem; }
    .gk-vm-people .gk-label { margin-bottom: 0; }
    .gk-vm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr)); gap: .75rem 1rem; margin: 0; padding: 0; list-style: none; }
    .gk-vm-grid.is-compact { grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); gap: .25rem .75rem; }
    .gk-vm-person { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .gk-vm-face { flex: none; display: grid; place-items: center; width: 3rem; height: 3rem; overflow: hidden; border-radius: 999px; background: var(--gk-well); font-size: .875rem; font-weight: 700; color: var(--gk-ink-2); }
    .gk-vm-face img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .gk-vm-grid.is-compact .gk-vm-face { width: 2.25rem; height: 2.25rem; font-size: .75rem; }
    .gk-vm-text { display: flex; flex-direction: column; min-width: 0; }
    .gk-vm-name { overflow: hidden; font-size: 1rem; font-weight: 650; line-height: 1.3; color: var(--gk-ink); text-overflow: ellipsis; white-space: nowrap; }
    .gk-vm-name a { color: inherit; text-decoration: none; }
    .gk-vm-name a:hover { text-decoration: underline; text-underline-offset: 3px; }
    .gk-vm-name a:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .25rem; }
    .gk-vm-sub { overflow: hidden; font-size: .875rem; line-height: 1.35; color: var(--gk-ink-3); text-overflow: ellipsis; white-space: nowrap; }
    .gk-vm-grid.is-compact .gk-vm-name { font-size: .9375rem; }
    .gk-vm-grid.is-compact .gk-vm-sub { font-size: .8125rem; }
</style>
@endonce

<section v-pre class="gk-panel gk-panel-flush gk-map gk-vm-band" aria-labelledby="{{ $vmTitleId }}" @if ($vmBand['rtl']) dir="rtl" @endif>
    <div class="gk-map-band">
        <span class="gk-map-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 5 4h14l2 5.5M3 9.5h18M3 9.5a3 3 0 0 0 6 0m0 0a3 3 0 0 0 6 0m0 0a3 3 0 0 0 6 0M5 12.5V20h14v-7.5M10 20v-4h4v4" /></svg>
        </span>
        <h2 id="{{ $vmTitleId }}" class="gk-map-title">{{ $vmBand['title'] }}</h2>
        @if ($vmBand['dateLabel'])
            <p class="gk-map-sub">{{ $vmBand['dateLabel'] }}</p>
        @endif
        @if ($vmFaces)
            <ul class="gk-map-stack" aria-hidden="true">
                @foreach ($vmFaces as $i => $face)
                    <li class="{{ $i > 2 ? 'gk-map-wide' : '' }} {{ $face['photo'] ? '' : 'gk-vm-initial' }}">
                        @if ($face['photo'])
                            <img src="{{ $face['photo'] }}" alt="" loading="lazy" decoding="async" width="32" height="32">
                        @else
                            {{ $face['initials'] }}
                        @endif
                    </li>
                @endforeach
                @if ($vmRest > 0)
                    <li class="gk-map-more gk-map-wide" dir="ltr">+{{ $vmRest }}</li>
                @endif
                @if ($vmRestNarrow > 0)
                    <li class="gk-map-more gk-map-narrow" dir="ltr">+{{ $vmRestNarrow }}</li>
                @endif
            </ul>
        @endif
        <div class="gk-map-acts">
            <a href="{{ $vmBand['url'] }}" class="gk-btn gk-btn-secondary gk-btn-sm gk-map-toggle gk-map-open">{{ __('messages.vm_public_open_map') }}</a>
        </div>
    </div>
</section>

@if ($vmGrid && $vmBand['vendors'])
    <div v-pre class="gk-vm-people">
        <h3 class="gk-label">{{ $vmBand['people'] }}</h3>
        <ul class="gk-vm-grid {{ $vmCompact ? 'is-compact' : '' }}" role="list">
            @foreach ($vmBand['vendors'] as $v)
                <li class="gk-vm-person">
                    <span class="gk-vm-face" aria-hidden="true">
                        @if ($v['photo'])
                            <img src="{{ $v['photo'] }}" alt="" loading="lazy" decoding="async" width="48" height="48">
                        @else
                            {{ $v['initials'] }}
                        @endif
                    </span>
                    <span class="gk-vm-text">
                        <span class="gk-vm-name">
                            @if ($v['url'])
                                <a href="{{ $v['url'] }}"><bdi>{{ $v['name'] }}</bdi></a>
                            @else
                                <bdi>{{ $v['name'] }}</bdi>
                            @endif
                        </span>
                        <span class="gk-vm-sub">
                            {{ ($vmBand['spotWord'])($v['label']) }}@if ($v['categories']) &middot; {{ implode(', ', $v['categories']) }}@endif
                        </span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
