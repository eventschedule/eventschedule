{{-- The visitors' map of a market, fair or expo (/{schedule}/map, VendorMapPublicController).

     Complete before any script: the header, the full list of who is there (FIRST in the
     document, so a screen reader and the Tab key start at it) and the map, which a phone shows on
     top by CSS order. resources/js/vendor-map-public.js then mounts PublicMapApp.vue on
     #vendor-map-public[data-vue-root] and redraws the same layout with search, the category
     chips, zoom and the vendor cards. Everything it needs is the JSON below: no request after the
     page, because a market's shoppers share one mobile network.

     The server's markup inside the mount is REPLACED by the app (it renders its own compiled
     template, never this markup as one), and it carries v-pre all the same, so a vendor's name in
     it can only ever be text. --}}
@php
    $map = $boot['map'];
    $t = $boot['t'];
    $isRtl = is_rtl();
    $selected = $boot['selected'];
    $selectedRow = $selected !== null ? collect($map['spots'])->first(fn ($s) => (int) $s['id'] === (int) $selected && ! empty($s['vendor'])) : null;
    $areaColors = collect($map['areas'])->pluck('color', 'id')->all();
    $areaNames = collect($map['areas'])->pluck('name', 'id')->all();
    $absentText = fn ($s) => ! empty($s['vendor']['next']) ? str_replace(':date', $s['vendor']['next'], $t['absentNext']) : $t['absent'];
    $sub = function ($s) use ($map) {
        $cats = collect($s['vendor']['categories'] ?? [])->map(fn ($c) => $map['categories'][$c] ?? null)->filter()->implode(', ');

        return collect([$cats, $s['vendor']['sells'] ?? null])->filter()->implode(' · ');
    };
    $initials = fn ($name) => \App\Services\VendorMaps\VendorMapBand::initials((string) $name);
    // Bare flags, spelled out: "<" can never close the script block, and addresses read as addresses.
    $bootJson = json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<x-app-guest-layout :role="$role" :fonts="$fonts" :page-meta="$pageMeta">

<x-slot name="head">
    @include('vendor-map.schematic-styles')
    @include('vendor-map.public-styles')
</x-slot>

<div class="gk-stage gk-p-vmap {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<div id="vendor-map-public" data-vue-root>
<div class="vm-page" v-pre>
    <header class="vm-head">
        @unless ($boot['embed'] ?? false)
        <a href="{{ $boot['back']['url'] }}" class="vm-back">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" @if ($isRtl) style="transform: scaleX(-1)" @endif><path d="M15 5l-7 7 7 7" /></svg>
            <bdi>{{ $boot['back']['label'] }}</bdi>
        </a>
        @endunless
        @if ($map['dateLabel'] && $boot['status'] !== 'closed')
            <p class="gk-dateline vm-dateline">{{ $boot['dateText'] }}</p>
        @endif
        <h1 class="vm-title"><bdi>{{ $map['name'] }}</bdi></h1>
        @if ($boot['closed'])
            <div class="gk-note gk-note-warn vm-closed" role="status">
                <svg class="gk-note-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" /></svg>
                <div class="gk-note-body">
                    <p class="vm-closed-title">{{ $boot['closed']['title'] }}</p>
                    @if ($boot['closed']['next'])
                        <p>{{ $boot['closed']['next'] }}</p>
                    @endif
                </div>
            </div>
        @endif
    </header>

    <section class="vm-side" aria-label="{{ $t['vendors'] }}">
        @if ($groups)
            @php
                // The chips the app shows: the categories somebody here today sells, in order.
                $ssrPresent = collect($map['spots'])->filter(fn ($s) => ($s['state'] ?? '') === 'present' && ! empty($s['vendor']));
                $ssrChips = collect($map['categories'] ?? [])->filter(fn ($label, $slug) => $ssrPresent->contains(fn ($s) => in_array($slug, $s['vendor']['categories'] ?? [], true)));
            @endphp
            {{-- The app's search bar and chips, drawn ahead of it so nothing moves when it starts. --}}
            <div class="vm-sheethead">
                <div class="vm-search">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    <input type="search" class="vm-search-input" placeholder="{{ $t['search'] }}" aria-label="{{ $t['searchLabel'] }}" readonly>
                </div>
                @if ($ssrChips->count() > 1)
                    <div class="vm-chips" aria-hidden="true">
                        <span class="gk-chip vm-chip" aria-pressed="true">{{ $t['all'] }}</span>
                        @foreach ($ssrChips as $label)
                            <span class="gk-chip vm-chip"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"></svg>{{ $label }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
        @if ($selectedRow)
            @php $v = $selectedRow['vendor']; @endphp
            <article class="vm-card vm-card-slot {{ $selectedRow['state'] === 'absent' ? 'is-absent' : '' }}" aria-labelledby="vm-card-{{ $selectedRow['id'] }}">
                @if (! empty($v['url']) && ! empty($v['photo']))
                    <img class="vm-card-photo" src="{{ $v['photo'] }}" alt="" loading="lazy" decoding="async" width="400" height="200">
                @endif
                <div class="vm-card-body">
                    <p class="vm-card-spot">
                        <i class="vm-dot" @if ($areaColors[$selectedRow['area']] ?? null) style="--area: {{ $areaColors[$selectedRow['area']] }}" @endif aria-hidden="true"></i>
                        <span>{{ str_replace(':number', $selectedRow['label'], $t['spot']) }}</span>
                        @if ($areaNames[$selectedRow['area']] ?? null)
                            <span aria-hidden="true">&middot;</span><bdi>{{ $areaNames[$selectedRow['area']] }}</bdi>
                        @endif
                    </p>
                    <h2 id="vm-card-{{ $selectedRow['id'] }}" class="vm-card-name"><bdi>{{ $v['name'] }}</bdi></h2>
                    @if ($selectedRow['state'] === 'absent')
                        <p class="vm-card-absent">{{ $absentText($selectedRow) }}</p>
                    @endif
                    @if ($sub($selectedRow))
                        <p class="vm-card-sells"><bdi>{{ $sub($selectedRow) }}</bdi></p>
                    @endif
                    @if (! empty($v['url']))
                        <a href="{{ $v['url'] }}" class="gk-btn gk-btn-primary vm-card-link">{{ $t['visit'] }}</a>
                    @endif
                </div>
            </article>
        @endif

        @if ($groups)
            <h2 class="vm-listtitle">{{ $boot['listTitle'] }}</h2>
            @foreach ($groups as $g)
                <div class="vm-group">
                    @if ($map['areas'])
                        <h3 class="vm-group-name">
                            <i class="vm-dot" @if ($g['color']) style="--area: {{ $g['color'] }}" @endif aria-hidden="true"></i>
                            <bdi>{{ $g['name'] }}</bdi>
                        </h3>
                    @endif
                    <ul class="vm-list" role="list">
                        @foreach ($g['rows'] as $r)
                            <li id="vm-row-{{ $r['id'] }}">
                                <div class="vm-row {{ $r['state'] === 'absent' ? 'is-absent' : '' }} {{ $selected !== null && (int) $selected === (int) $r['id'] ? 'is-on' : '' }}">
                                    <span class="vm-row-num" @if ($areaColors[$r['area']] ?? null) style="--area: {{ $areaColors[$r['area']] }}" @endif dir="ltr" aria-hidden="true">{{ $r['label'] }}</span>
                                    @if (! empty($r['vendor']['url']) && ! empty($r['vendor']['photo']))
                                        <img class="vm-row-pic" src="{{ $r['vendor']['photo'] }}" alt="" loading="lazy" decoding="async" width="40" height="40">
                                    @else
                                        <span class="vm-row-pic vm-row-initials" aria-hidden="true">{{ $initials($r['vendor']['name']) }}</span>
                                    @endif
                                    <span class="vm-row-text">
                                        <span class="vm-row-name">
                                            <span class="vm-sr">{{ str_replace(':number', $r['label'], $t['spot']) }}, </span>
                                            @if (! empty($r['vendor']['url']))
                                                <a href="{{ $r['vendor']['url'] }}" class="vm-row-link"><bdi>{{ $r['vendor']['name'] }}</bdi></a>
                                            @else
                                                <bdi>{{ $r['vendor']['name'] }}</bdi>
                                            @endif
                                        </span>
                                        @if ($r['state'] === 'absent')
                                            <span class="vm-row-sub vm-row-absent">{{ $absentText($r) }}</span>
                                        @elseif ($sub($r))
                                            <span class="vm-row-sub"><bdi>{{ $sub($r) }}</bdi></span>
                                        @endif
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <div class="vm-empty">
                <p>{{ $boot['emptyText'] }}</p>
            </div>
        @endif
    </section>

    @php
        // As PublicMapApp.vue decides: a big market is shown area by area on a phone.
        $ssrZones = collect($boot['schematic']['sections'])->flatMap(fn ($s) => match ($s['type']) {
            'full' => [$s['zone']], 'stack' => $s['zones'], default => array_merge($s['a'], $s['b']) });
        $ssrLayered = $ssrZones->count() > 1 || $ssrZones->sum(fn ($z) => count(array_filter($z['cells']))) > 30;
    @endphp
    <section class="vm-mapwrap {{ $ssrLayered ? 'is-layered' : '' }}" aria-label="{{ $t['map'] }}">
        @if ($ssrLayered)
            @include('vendor-map.area-plan', ['schematic' => $boot['schematic'], 'rtl' => $isRtl, 'hereLabel' => $t['here'], 'showHere' => $boot['showHere'], 't' => $t])
        @endif
        <div class="vm-ssr-full">
            @include('vendor-map.schematic', ['schematic' => $boot['schematic'], 'rtl' => $isRtl, 'hereLabel' => $t['here'], 'showHere' => $boot['showHere'], 'selected' => $selected])
        </div>
    </section>
</div>
</div>
</div>

<script type="application/json" id="vendor-map-public-data" {!! nonce_attr() !!}>{!! $bootJson !!}</script>
@vite('resources/js/vendor-map-public.js')

</x-app-guest-layout>
