{{-- The market as blocks of equal stalls, one block per area (VendorMapSchematic::build()).
     Shared by the entrance sign (vendor-map/sign) and the visitors' map before its script starts
     (vendor-map/public); PublicMapApp's SchematicMap.vue draws the same classes.

     The layout is a place, so it never mirrors: dir="ltr" on the frame keeps the hall on the left
     where it is on the ground, and each heading reads in the page's own direction.
     $schematic, $rtl, $hereLabel, $showHere; optional $selected (a spot id). --}}
@php
    $selected = $selected ?? null;
    $zoneHtml = function ($z) use ($rtl, $selected) {
        $cells = '';
        foreach ($z['cells'] as $c) {
            if ($c === null) {
                $cells .= '<span class="vms-c is-gap" aria-hidden="true"></span>';
                continue;
            }
            $classes = 'vms-c is-'.e($c['state']).((string) $selected === (string) $c['id'] ? ' is-on' : '');
            $cells .= '<span class="'.$classes.'" data-spot="'.e($c['id']).'">'.e($c['label']).'</span>';
        }

        return '<section class="vms-zone" style="--zc: '.e(preg_match('/^#[0-9a-fA-F]{6}$/', $z['color']) ? $z['color'] : '#64748B').'">'
            .'<div class="vms-zh" dir="'.($rtl ? 'rtl' : 'ltr').'">'
            .($z['name'] !== '' ? '<b><bdi>'.e($z['name']).'</bdi></b>' : '')
            .($z['range'] !== '' ? '<span class="vms-range">'.e($z['range']).'</span>' : '')
            .($z['sells'] !== '' ? '<span class="vms-sells"><bdi>'.e($z['sells']).'</bdi></span>' : '')
            .'</div>'
            .'<div class="vms-grid" style="grid-template-columns: repeat('.(int) $z['cols'].', minmax(0, 1fr))">'.$cells.'</div>'
            .'</section>';
    };
@endphp
<div class="vms" dir="ltr">
    @foreach ($schematic['sections'] as $section)
        @if ($section['type'] === 'full')
            {!! $zoneHtml($section['zone']) !!}
        @elseif ($section['type'] === 'stack')
            @foreach ($section['zones'] as $z)
                {!! $zoneHtml($z) !!}
            @endforeach
        @else
            <div class="vms-cols" style="grid-template-columns: minmax(0, {{ (int) $section['fa'] }}fr) minmax(0, {{ (int) $section['fb'] }}fr)">
                <div class="vms-col">@foreach ($section['a'] as $z){!! $zoneHtml($z) !!}@endforeach</div>
                <div class="vms-col">@foreach ($section['b'] as $z){!! $zoneHtml($z) !!}@endforeach</div>
            </div>
        @endif
    @endforeach

    @if ($schematic['amenities'] || ($showHere && $schematic['here']))
        <div class="vms-foot" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
            @if ($showHere && $schematic['here'])
                <p class="vms-here"><i aria-hidden="true"></i><b>{{ $hereLabel }}</b>@if ($schematic['entrance'])<span>· {{ $schematic['entrance'] }}</span>@endif</p>
            @endif
            @if ($schematic['amenities'])
                <ul class="vms-amen">
                    @foreach ($schematic['amenities'] as $a)
                        <li><bdi>{{ $a['label'] }}</bdi></li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
