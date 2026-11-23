{{-- The phone's overview of a big market, drawn by the server so the page does not jump when
     AreaMap.vue takes over: the areas where they are, at their size (before the app grows small
     ones into free space), the market's picture when its owner chose to show it, and the places
     around them. Params: $schematic, $rtl, $hereLabel, $showHere, $t (the page's strings). --}}
@php
    $zones = [];
    foreach ($schematic['sections'] as $sec) {
        if ($sec['type'] === 'full') {
            $zones[] = $sec['zone'];
        } elseif ($sec['type'] === 'stack') {
            array_push($zones, ...$sec['zones']);
        } else {
            array_push($zones, ...$sec['a'], ...$sec['b']);
        }
    }
    $boxes = array_merge(array_column($zones, 'box'), array_column($schematic['places'] ?? [], 'box'));
    $plan = null;
    if ($zones && count(array_filter(array_column($zones, 'box'))) === count($zones)) {
        $x0 = min(array_column($boxes, 0)) - 16;
        $y0 = min(array_column($boxes, 1)) - 16;
        $w = max(1, max(array_column($boxes, 2)) + 16 - $x0);
        $h = max(1, max(array_column($boxes, 3)) + 16 - $y0);
        $plan = $h / $w >= 0.35 ? compact('x0', 'y0', 'w', 'h') : null;
    }
    $pct = fn ($b) => sprintf('left:%.3f%%;top:%.3f%%;width:%.3f%%;height:%.3f%%',
        ($b[0] - $plan['x0']) / $plan['w'] * 100, ($b[1] - $plan['y0']) / $plan['h'] * 100,
        ($b[2] - $b[0]) / $plan['w'] * 100, ($b[3] - $b[1]) / $plan['h'] * 100);
    $count = fn ($n) => str_replace(':count', (string) $n, app('translator')->getSelector()->choose((string) ($t['areaHere'] ?? ':count'), $n, app()->getLocale()));
@endphp
@if ($plan)
<div class="vmo vm-ssr-plan" aria-hidden="true">
    @if (! empty($t['tapArea']))
        <p class="vmo-hint" dir="{{ $rtl ? 'rtl' : 'ltr' }}">{{ $t['tapArea'] }}</p>
    @endif
    <div class="vms vmo-plan {{ ! empty($schematic['picture']) ? 'has-picture' : '' }}" dir="ltr" style="aspect-ratio: {{ $plan['w'] }} / {{ $plan['h'] }}">
        @if (! empty($schematic['picture']) && ! empty($schematic['frame']))
            <img class="vmo-picture" src="{{ $schematic['picture'] }}" alt="" decoding="async" style="{{ $pct([0, 0, $schematic['frame'][0], $schematic['frame'][1]]) }}">
        @endif
        @foreach ($schematic['places'] ?? [] as $place)
            <span class="vmo-place is-{{ $place['kind'] }}" style="{{ $pct($place['box']) }}"><bdi>{{ $place['label'] }}</bdi></span>
        @endforeach
        @foreach ($zones as $z)
            @php $here = count(array_filter($z['cells'], fn ($c) => $c && $c['state'] === 'present')); @endphp
            <span class="vmo-card vmo-tile" style="--zc: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $z['color'] ?? '') ? $z['color'] : '#64748B' }}; {{ $pct($z['box']) }}">
                <span class="vmo-card-head" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
                    @if ($z['name'])<b><bdi>{{ $z['name'] }}</bdi></b>@endif
                    @if ($z['range'])<span class="vms-range">{{ $z['range'] }}</span>@endif
                </span>
                <span class="vmo-card-foot" dir="{{ $rtl ? 'rtl' : 'ltr' }}"><span>{{ $count($here) }}</span></span>
            </span>
        @endforeach
        @if ($showHere && ! empty($schematic['hereAt']))
            <span class="vmo-here" style="left: {{ ($schematic['hereAt'][0] - $plan['x0']) / $plan['w'] * 100 }}%; top: {{ ($schematic['hereAt'][1] - $plan['y0']) / $plan['h'] * 100 }}%"></span>
        @endif
    </div>
</div>
@endif
